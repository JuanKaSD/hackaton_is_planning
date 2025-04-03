<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Models\Airline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class FlightController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->except(['index', 'show']);
    }

    /**
     * Display a listing of flights.
     */
    public function index(): JsonResponse
    {
        $flights = Flight::with(['airline:id,name', 'originAirport', 'destinationAirport', 'airplane', 'duration', 'flight_date'])
            ->get();

        // Determine state for each flight
        $flights->each(function ($flight) {
            $flight->state = $this->determineFlightState($flight);
        });

        return response()->json($flights);
    }

    /**
     * Store a newly created flight in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Verify user is associated with the airline
            $airlineId = $request->input('airline_id');
            $airline = Airline::findOrFail($airlineId);

            // Check if user is the enterprise that owns this airline
            if (Auth::id() !== $airline->enterprise_id) {
                return response()->json([
                    'message' => 'Unauthorized to create flights for this airline'
                ], 403);
            }

            $validated = $request->validate([
                'airline_id' => 'required|exists:airlines,id',
                'origin' => 'required|exists:airports,id',
                'destination' => 'required|exists:airports,id|different:origin',
                'airplane_plate' => 'required|exists:airplanes,plate',
                'duration' => 'required|integer|min:1',
                'flight_date' => 'required|date|after:now',
            ]);

            $flight = Flight::create($validated);
            $flight->load(['airline:id,name', 'originAirport', 'destinationAirport', 'airplane', 'duration', 'flight_date']);

            return response()->json([
                'message' => 'Flight created successfully',
                'data' => $flight
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating flight: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create flight',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified flight.
     */
    public function show(Flight $flight): JsonResponse
    {
        $flight->load(['airline:id,name', 'originAirport', 'destinationAirport', 'airplane', 'duration', 'flight_date']);
        
        // Determine flight state
        $flight->state = $this->determineFlightState($flight);
        
        return response()->json($flight);
    }

    /**
     * Update the specified flight in storage.
     */
    public function update(Request $request, Flight $flight): JsonResponse
    {
        try {
            // Verify user is associated with the airline
            $airline = Airline::findOrFail($flight->airline_id);

            // Check if user is the enterprise that owns this airline
            if (Auth::id() !== $airline->enterprise_id) {
                return response()->json([
                    'message' => 'Unauthorized to update flights for this airline'
                ], 403);
            }

            $validated = $request->validate([
                'origin' => 'sometimes|required|exists:airports,id',
                'destination' => 'sometimes|required|exists:airports,id|different:origin',
                'airplane_plate' => 'sometimes|required|exists:airplanes,plate',
                'duration' => 'sometimes|required|integer|min:1',
                'flight_date' => 'sometimes|required|date|after:now',
            ]);

            $flight->update($validated);
            $flight->load(['airline:id,name', 'originAirport', 'destinationAirport', 'airplane', 'duration', 'flight_date']);

            return response()->json([
                'message' => 'Flight updated successfully',
                'data' => $flight
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating flight: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update flight',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified flight from storage.
     */
    public function destroy(Flight $flight): JsonResponse
    {
        try {
            // Verify user is associated with the airline
            $airline = Airline::findOrFail($flight->airline_id);

            // Check if user is the enterprise that owns this airline
            if (Auth::id() !== $airline->enterprise_id) {
                return response()->json([
                    'message' => 'Unauthorized to delete flights for this airline'
                ], 403);
            }

            $flight->delete();

            return response()->json([
                'message' => 'Flight deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting flight: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete flight',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine the flight state based on current time, flight date and duration.
     */
    private function determineFlightState(Flight $flight): string
    {
        $now = Carbon::now();
        $flightDate = Carbon::parse($flight->flight_date);
        $flightEndDate = (clone $flightDate)->addMinutes($flight->duration);

        if ($now->lt($flightDate)) {
            return "preparing the aircraft";
        } elseif ($now->gte($flightDate) && $now->lte($flightEndDate)) {
            return "flying";
        } else {
            return "outdated";
        }
    }
}
