<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

        // Check if the current password is correct
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

    public function customerDeleteAccount(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $password = $user->password;

        if (!Hash::check($request->password, $password)) {
            return response()->json(['message' => 'Your current password is incorrect'], 403);
        }

        // Delete the user account
        $user->delete();

        return response()->json(['message' => 'User account deleted successfully'], 200);
    }

    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        // Store the uploaded picture
        $path = $request->file('picture')->store('profile_pictures', 'public');

        if($user->picture){
            // Delete the old picture if it exists
            Storage::disk('public')->delete($user->picture);
        }
        // Update the user's profile picture path
        $user->update(['picture' => $path]);

        return response()->json(['message' => 'Profile picture uploaded successfully', 'path' => $path], 200);
    }


}
