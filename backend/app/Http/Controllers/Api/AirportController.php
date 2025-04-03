<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use Illuminate\Http\JsonResponse;

class AirportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->except(['index']);
    }

    /**
     * Display a listing of airports.
     */
    public function index(): JsonResponse
    {
        $airports = Airport::all();
        return response()->json($airports);
    }
}
