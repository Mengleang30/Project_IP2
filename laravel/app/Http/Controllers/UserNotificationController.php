<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserNotificationController
{
    public function GetNotification(Request $request)
    {
        $user = $request->user();
        $notfications = $user->notifications()->orderBy('created_at', 'desc')->get();
        return response()->json([
            'message' => 'Notifications retrieved successfully!',
            'notifications' => $notfications,
        ]);

    }


    public function AdminGetNotification(Request $request)
    {
        $user = $request->user();
        $notfications = $user->notifications()->orderBy('created_at', 'desc')->get();
        return response()->json([
            'message' => 'Notifications retrieved successfully!',
            'notifications' => $notfications,
        ]);

    }
}
