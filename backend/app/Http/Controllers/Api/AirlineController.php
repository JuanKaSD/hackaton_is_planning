<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Route;

class AirlineController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->except(['index', 'show']);
    }

    /**
     * Display a listing of airlines.
     */
    public function index(): JsonResponse
    {
        // Check if this is the enterprise route
        $routeName = Route::currentRouteName();

        $airlines = Airline::where('enterprise_id', Auth::id())
            ->with('enterprise:id,name')
            ->get();

        return response()->json($airlines);
    }

    /**
     * Store a newly created airline in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Check authorization here
            $this->authorize('create', Airline::class);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Automatically set the enterprise_id to the current user's id
            $validated['enterprise_id'] = Auth::id();

            $airline = Airline::create($validated);

            return response()->json([
                'message' => 'Airline created successfully',
                'data' => $airline
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating airline: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create airline',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified airline.
     */
    public function show(Airline $airline): JsonResponse
    {
        $airline->load('enterprise:id,name');
        return response()->json($airline);
    }

    /**
     * Update the specified airline in storage.
     */
    public function update(Request $request, Airline $airline): JsonResponse
    {
        try {
            // Check authorization here
            $this->authorize('update', $airline);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
            ]);

            $airline->update($validated);

            return response()->json([
                'message' => 'Airline updated successfully',
                'data' => $airline
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating airline: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update airline',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified airline from storage.
     */
    public function destroy(Airline $airline): JsonResponse
    {
        try {
            // Check authorization here
            $this->authorize('delete', $airline);

            $airline->delete();

            return response()->json([
                'message' => 'Airline deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting airline: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete airline',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
