<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    /**
     * Display a listing of the user's bookings.
     */
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        $bookings = $user->bookings()->with('flight.airline')->get();
        return response()->json($bookings);
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Ensure the current user is a client
            /** @var User $user */
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            
            if (!$user->isClient()) {
                return response()->json([
                    'message' => 'Only client users can book flights'
                ], 403);
            }

            $validated = $request->validate([
                'flight_id' => 'required|exists:flights,id',
            ]);

            // Find the flight
            $flight = Flight::findOrFail($validated['flight_id']);

            // Check if booking is being made less than 24 hours before departure
            $flightDate = Carbon::parse($flight->flight_date);
            $now = Carbon::now();
            $bookingDeadline = (clone $flightDate)->subDay(); // 24 hours before flight

            if ($now->greaterThanOrEqualTo($bookingDeadline)) {
                return response()->json([
                    'message' => 'Bookings must be made at least 24 hours before departure'
                ], 400);
            }

            // Check if the client has overlapping flights
            $overlappingFlight = $this->hasOverlappingFlights($user->id, $flight);
            if ($overlappingFlight) {
                return response()->json([
                    'message' => 'You already have a booking for a flight that overlaps with this one',
                    'overlapping_flight' => $overlappingFlight
                ], 400);
            }

            // Check if flight has available seats
            if (!$flight->hasAvailableSeats()) {
                return response()->json([
                    'message' => 'No available seats on this flight'
                ], 400);
            }

            // Check if user already has a booking for this flight
            $existingBooking = Booking::where('user_id', $user->id)
                ->where('flight_id', $flight->id)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($existingBooking) {
                return response()->json([
                    'message' => 'You already have a booking for this flight'
                ], 400);
            }

            // Create booking
            $booking = new Booking([
                'user_id' => $user->id,
                'flight_id' => $flight->id,
                'status' => 'confirmed',
                'booking_reference' => Booking::generateBookingReference(),
            ]);

            $booking->save();

            // Load relationship data
            $booking->load('flight.airline');

            return response()->json([
                'message' => 'Flight booked successfully',
                'data' => $booking
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error booking flight: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to book flight',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if a user has any flights that overlap with the new flight.
     *
     * @param int $userId The user ID
     * @param Flight $newFlight The new flight to check against
     * @return mixed The overlapping flight or false if no overlap
     */
    private function hasOverlappingFlights(int $userId, Flight $newFlight)
    {
        // Get all user's confirmed/pending bookings
        $userBookings = Booking::where('user_id', $userId)
            ->whereIn('status', ['confirmed', 'pending'])
            ->with('flight')
            ->get();
        
        if ($userBookings->isEmpty()) {
            return false;
        }
        
        // New flight start and end times
        $newFlightStart = Carbon::parse($newFlight->flight_date);
        $newFlightEnd = (clone $newFlightStart)->addMinutes($newFlight->duration);
        
        foreach ($userBookings as $booking) {
            $existingFlight = $booking->flight;
            $existingFlightStart = Carbon::parse($existingFlight->flight_date);
            $existingFlightEnd = (clone $existingFlightStart)->addMinutes($existingFlight->duration);
            
            // Check if flights overlap using time interval logic
            if (
                // New flight starts during existing flight
                ($newFlightStart->betweenIncluded($existingFlightStart, $existingFlightEnd)) ||
                // New flight ends during existing flight
                ($newFlightEnd->betweenIncluded($existingFlightStart, $existingFlightEnd)) ||
                // New flight completely contains existing flight
                ($newFlightStart->lessThanOrEqualTo($existingFlightStart) && 
                 $newFlightEnd->greaterThanOrEqualTo($existingFlightEnd))
            ) {
                return $existingFlight;
            }
        }
        
        return false;
    }

    /**
     * Display the specified booking.
     */
    public function show(int $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            
            $booking = Booking::with('flight.airline')->findOrFail($id);

            // Ensure user owns the booking or is an admin
            if ($booking->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to view this booking'
                ], 403);
            }

            return response()->json($booking);
        } catch (\Exception $e) {
            Log::error('Error fetching booking: ' . $e->getMessage());
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            
            $booking = Booking::findOrFail($id);

            // Ensure user owns the booking
            if ($booking->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to cancel this booking'
                ], 403);
            }

            // Check if booking is already cancelled
            if ($booking->status === 'cancelled') {
                return response()->json([
                    'message' => 'Booking is already cancelled'
                ], 400);
            }

            // Update booking status
            $booking->status = 'cancelled';
            $booking->save();

            return response()->json([
                'message' => 'Booking cancelled successfully',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling booking: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
