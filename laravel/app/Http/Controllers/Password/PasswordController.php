<?php

namespace App\Http\Controllers\Password;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class PasswordController
{
    //
   public function sendResetCode(Request $request){
        $request->validate(['email'=>'required|email']);

        $user = User::where('email', $request->email)->first();

        if(!$user){
            return response()->json(['message'=>"User not found"],404);
        };

        $code = random_int(10000000, 99999999); // Ensure this generates an 8-digit code



        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $code,
                'created_at' => SupportCarbon::now(),
            ]
        );
        Mail::raw(
            "Hello {$user->name},\n\n" .
            "We have received a request to reset your password. To reset your password, please use the following code:\n\n" .
            "Reset Code: $code\n\n" .
            "This code will expire in 5 minutes, so please use it as soon as possible.\n\n" .
            "If you did not request a password reset, please ignore this message.\n\n" .
            "Thank you,\n" .
            "The Support Team",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Password Reset Code');
            }
        );


        return response()->json(['message' => 'Reset code sent to email']);

   }

   public function resetPassword(Request $request){

    $request->validate([
        'email' => 'required|email',
        'code' => 'required|digits:8',
        'new_password' => 'required|min:8|confirmed',
    ]);

    $reset = DB::table('password_resets')
        ->where('email', $request->email)
        ->where('token', $request->code)
        ->first();

    if (!$reset || SupportCarbon::parse($reset->created_at)->addMinutes(5)->isPast()) {
        return response()->json(['message' => 'Invalid or expired code'], 400);
    }

    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }
    $user->password = Hash::make($request->new_password);
    $user->save();


    DB::table('password_resets')->where('email', $request->email)->delete();

    return response()->json(['message' => 'Password has been reset successfully']);
   }

}
