<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{

    public function login(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if the user exists and if the password is correct
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate a personal access token for the user
        $token = $user->createToken('LaravelApp')->plainTextToken;

        // Return response with the token
        return response()->json([
            'token' => $token,
            'role' => $user->role
        ]);
    }
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ], 200);
    }

    public function register(Request $request){
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|default:NULL',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,customer',
        ]);

        if (strlen($validated['password']) < 8) {
            return response()->json([
                'message' => 'Password must be at least 8 characters long',
            ], 422);
        }
        
        // check if the email already exists
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser) {
            return response()->json([
                'message' => 'Email already exists',
            ], 422);
        }

        $validated = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
        ]);
        return response()->json([
            'message' => 'User created successfully',
            'user' => $validated,
        ], 201);

    }





}
