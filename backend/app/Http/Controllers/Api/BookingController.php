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
