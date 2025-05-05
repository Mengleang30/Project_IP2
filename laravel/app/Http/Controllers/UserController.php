<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController
{

    public function listAllUsers()
    {
        $users = User::where('role', '!=', 'admin')->get();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found'], 404);
        }
        return response()->json($users);
    }
    public function createUser(Request $request){
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,customer',
        ]);

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

    public function updateUser(Request $request, $id){
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:8',
        ]);
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->update([
            'name' => $validated['name'],
            // 'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
        ]);
        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ], 200);
    }
    public function updatePassword(Request $request, $id){
        $validated = $request->validate([
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ]);
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if (!password_verify($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Your current password is incorrect'], 403);
        }
        $user->update([
            'password' => bcrypt($validated['new_password']),
        ]);
        return response()->json([
            'message' => 'Password updated successfully',
            'user' => $user,
        ], 200);
    }

}
