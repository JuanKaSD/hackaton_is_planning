use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

namespace App\Http\Controllers;


class Kernel extends Controller
{
    public function handle(Request $request)
    {
        // Aquí puedes manejar la lógica de tu aplicación
        return response()->json([
            'message' => 'Hola desde el Kernel'
        ]);
    }
}