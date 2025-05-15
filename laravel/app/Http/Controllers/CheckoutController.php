<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartBook;
use App\Models\Order;
use App\Models\OrderBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController
{
    public function checkout(Request $request)
    {
        $user = $request->user();
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $cart = Cart::with('cartBooks.book')->where('user_id', $user->id)->first();
    if (!$cart || $cart->cartBooks->isEmpty()) {
        return response()->json(['message' => 'Cart is empty'], 400);
    }

    // Start a transaction
    DB::beginTransaction();

    $existingOrder = Order::where('user_id', $user->id)
        ->where('status', 'pending')
        ->first();

    if ($existingOrder) {
        return response()->json(['message' => 'You already have a pending order'], 400);
    }

    try {
        $total = 0;
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 0,
            'status' => 'pending',
        ]);

        foreach ($cart->cartBooks as $cartBook) {
            $book = $cartBook->book;
            $price = $book->price;
            $quantity = $cartBook->quantity;
            $total += $price * $quantity;

            OrderBook::create([
                'order_id' => $order->id,
                'book_id' => $book->id,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        $order->total_price = $total;
        $order->save();

        // Clear the cart
        CartBook::where('cart_id', $cart->id)->delete();

        // save is all change is  fine
        DB::commit();

        return response()->json([
            'message' => 'Checkout successful',
            'order' => $order->load('orderBooks.book')
        ]);
    } catch (\Exception $e) {
        // Undo everything if there’s an error
        DB::rollBack();
        return response()->json(['message' => 'Checkout failed', 'error' => $e->getMessage()], 500);
    }
    }
}
