<?php

namespace App\Http\Controllers\API;

use App\Models\User;

use Laravel\Socialite\Facades\Socialite;
use Str;

class GoogleController
{
  // Redirect to Google OAuth
  public function redirectToGoogle()
  {
      return Socialite::driver('google')->stateless()->redirect();
  }

  // Handle Google Callback
  public function handleGoogleCallback()
  {
      try {
          // Get user info from Google using stateless mode


      $googleUser = Socialite::driver('google')->stateless()->user();
          // Check if user exists
      $user = User::where('google_id', $googleUser->getId())->first();

          if (!$user) {
            // If not found by google_id, try to find by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // User found by email, update google_id if missing
                if (!$user->google_id) {
                    $user->google_id = $googleUser->getId();
                    $user->name->$googleUser->getName();
                    $user->save();
                }
            } else {
                // No user with this email, create new one
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)),
                    'picture' => $googleUser->getAvatar(),
                ]);
            }
        }



          // Generate API token for the user
          $token = $user->createToken('GoogleLogin')->plainTextToken;

          return redirect()->to("http://localhost:5173/auth/google/callback?token=$token");

          return response()->json(['token' => $token]);

      } catch (\Exception $e) {
        \Log::error('Google login error: ' . $e->getMessage());
        return response()->json([
            'error' => 'Something went wrong.',
            'message' => $e->getMessage()
        ], 400);
  }}

}
