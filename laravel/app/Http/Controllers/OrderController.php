<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderBook;
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

            // Restore the quantity of books in stock
        $orderBooks = OrderBook::where('order_id', $order->id)->get();
        foreach ($orderBooks as $orderBook) {
            $book = Book::find($orderBook->book_id);
            if ($book) {
                // Return quantity back to book stock
                $book->quantity += $orderBook->quantity;
                $book->save();
            }
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


    public function listAllOrderByAdmin(Request $request){
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $orders = Order::with('orderBooks.book')->get();
        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found'], 404);
        }
        return response()->json([
            'message' => 'List all orders successfully',
            'orders' => $orders,
        ], 200);

    }


}
