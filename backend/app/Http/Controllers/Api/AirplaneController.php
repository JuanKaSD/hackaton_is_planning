<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Airplane;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AirplaneController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->except(['index', 'show']);
    }

    /**
     * Display a listing of airplanes.
     */
    public function index(): JsonResponse
    {
        $airplanes = Airplane::all();
        return response()->json($airplanes);
    }

    /**
     * Store a newly created airplane in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'plate' => 'required|string|max:10|unique:airplanes,plate',
                'model' => 'required|string|max:255',
                'capacity' => 'required|integer|min:1',
            ]);
            
            $airplane = Airplane::create($validated);
            
            return response()->json([
                'message' => 'Airplane created successfully',
                'data' => $airplane
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating airplane: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create airplane',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified airplane.
     */
    public function show(string $plate): JsonResponse
    {
        $airplane = Airplane::with('flight.airline')->findOrFail($plate);
        return response()->json($airplane);
    }

    /**
     * Update the specified airplane in storage.
     */
    public function update(Request $request, string $plate): JsonResponse
    {
        try {
            $airplane = Airplane::findOrFail($plate);
            
            $validated = $request->validate([
                'model' => 'sometimes|required|string|max:255',
                'capacity' => 'sometimes|required|integer|min:1',
            ]);
            
            $airplane->update($validated);
            
            return response()->json([
                'message' => 'Airplane updated successfully',
                'data' => $airplane
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating airplane: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update airplane',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified airplane from storage.
     */
    public function destroy(string $plate): JsonResponse
    {
        try {
            $airplane = Airplane::findOrFail($plate);
            
            // Check if airplane has associated flights
            if ($airplane->flight()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete airplane as it has associated flights'
                ], 409);
            }
            
            $airplane->delete();
            
            return response()->json([
                'message' => 'Airplane deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting airplane: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete airplane',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
