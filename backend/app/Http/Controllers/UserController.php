<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function login(Request $request)
    {
        // Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Verificar si el usuario ya existe en la base de datos
        $user = User::where('email', $request->email)->first();

        // Si el usuario no existe, crearlo
        if (!$user) {
            $user = User::create([
                'name' => $request->email, // Usamos el email como nombre por defecto (o puedes pedir un nombre)
                'email' => $request->email,
                'phone' => $request->phone ?? 'No phone', // Si no se pasa el teléfono, asignamos un valor por defecto
                'password' => Hash::make($request->password),
            ]);
        }

        // Verificar si la contraseña proporcionada coincide con la del usuario
        if (Hash::check($request->password, $user->password)) {
            // Crear el token para el usuario
            $token = $user->createToken('YourAppName')->plainTextToken;

            // Devolver la respuesta con el token
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ], 200);
        }

        // Si las credenciales no son correctas, devolver error
        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }



    public function store(Request $request)
    {
        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Crear el usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Crear el token para el usuario
        $token = $user->createToken('YourAppName')->plainTextToken;

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function show(User $user)
    {
        return $user;
    }

    public function update(Request $request, User $user)
    {
        $user->update($request->all());
        return response()->json($user, 200);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
