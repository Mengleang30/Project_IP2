<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    //
    public function customerUpdateInformation(Request $request )
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
            'phone' => 'nullable|string|max:15',
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ]);

        $currentUser = $request->user();

        if (!Hash::check($validated['current_password'], $currentUser->password)) {
            return response()->json(['message' => 'Your current password is incorrect'], 403);
        }

        $currentUser->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $currentUser,
        ], 200);
    }


}
