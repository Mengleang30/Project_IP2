<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

class OrderController
{
   public function listOrders(Request $request){

    $user = $request->user();

    $pendingOrders = Order::with('orderBooks.book')
        ->where('user_id', $user->id)
        ->where('status', 'pending')
        ->get();

    if ($pendingOrders->isEmpty()) {
        return response()->json(['message' => 'No pending orders found'], 404);
    }

    return response()->json([
        'message' => 'List pending order successfully',
        'orders' => $pendingOrders,
    ], 200);

}
    public function cancelOrder(Request $request, $orderId)
    {
         $user = $request->user();

         $order = Order::where('id', $orderId)
              ->where('user_id', $user->id)
              ->where('status', 'pending')
              ->first();

         if (!$order) {
              return response()->json(['message' => 'Order not found or cannot be canceled'], 404);
         }

         $order->status = 'canceled';
         $order->save();

         return response()->json(['message' => 'Order canceled successfully'], 200);
    }


    public function listAllOrders(Request $request){

        $user = $request->user();

        $order = Order::where('user_id', $user->id)->get();

        if($order){
            return response()->json([
                'message'=> "You was not order yet"
            ], 404);
        }

        return response()->json([
          "All_orders" => $order
        ],200);


    }

}
