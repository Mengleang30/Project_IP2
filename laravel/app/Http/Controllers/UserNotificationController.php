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



    public function markNotificationAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }


    public function markAllNotificationsAsRead(Request $request)
    {
        $user = $request->user();

        // Mark all notifications as read
        $user->unreadNotifications->markAsRead();


        //desc = newest first
        //asc = oldest first
       $notifications = $user->notifications()->orderBy('created_at', 'desc')->get();

        return response()->json(['message' => 'All notifications marked as read',
            'notifications' => $notifications,
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
