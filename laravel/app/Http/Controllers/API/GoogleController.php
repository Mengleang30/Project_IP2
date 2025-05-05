<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Google_Client;
use Google_Service_Oauth2; // Add this if you need additional Google services
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleController
{
  // Redirect to Google OAuth
  public function redirectToGoogle()
  {
      return Socialite::driver('google')->redirect();
  }

  // Handle Google Callback
  public function handleGoogleCallback()
  {
      try {
          // Get user info from Google using stateless mode
          $googleUser = Socialite::driver('google')->user();

          // Check if user exists
          $user = User::where('google_id', $googleUser->getId())->first();

          if (!$user) {
              // If user doesn't exist, create a new one
              $user = User::create([
                  'name' => $googleUser->getName(),
                  'email' => $googleUser->getEmail(),
                  'google_id' => $googleUser->getId(),
                  'avatar' => $googleUser->getAvatar(),
              ]);
          }

          // Generate API token for the user
          $token = $user->createToken()->accessToken;

          return response()->json(['token' => $token]);

      } catch (\Exception $e) {
          return response()->json(['error' => 'Something went wrong.'], 400);
      }
  }
}
