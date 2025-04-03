<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AirportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->except(['index', 'show']);
    }

    /**
     * Display a listing of airports.
     */
    public function index(): JsonResponse
    {
        $airports = Airport::all();
        return response()->json($airports);
    }

    /**
     * Store a newly created airport in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|string|size:3|unique:airports,id|alpha:upper',
                'name' => 'required|string|max:255',
                'country' => 'required|string|max:255',
            ]);
            
            $airport = Airport::create($validated);
            
            return response()->json([
                'message' => 'Airport created successfully',
                'data' => $airport
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating airport: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create airport',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified airport.
     */
    public function show(string $id): JsonResponse
    {
        $airport = Airport::findOrFail($id);
        return response()->json($airport);
    }

    /**
     * Update the specified airport in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $airport = Airport::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'country' => 'sometimes|required|string|max:255',
            ]);
            
            $airport->update($validated);
            
            return response()->json([
                'message' => 'Airport updated successfully',
                'data' => $airport
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating airport: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update airport',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified airport from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $airport = Airport::findOrFail($id);
            
            // Check if airport is being used in flights
            if ($airport->departingFlights()->exists() || $airport->arrivingFlights()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete airport as it has associated flights'
                ], 409);
            }
            
            $airport->delete();
            
            return response()->json([
                'message' => 'Airport deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting airport: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete airport',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
