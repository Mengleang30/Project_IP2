<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Models\CartBook;

use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{

    public function addToCart(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $book = Book::findOrFail($validated['book_id']);
        if ($book->quantity < $validated['quantity']) {
            return response()->json(['message' => 'Not enough stock available'], 400);
        }

        $cart = Cart::firstOrCreate(
             ['user_id' => $user->id],         // Lookup condition
             ['grand_total' => 0]
        );

        $finalPrice = $book->price * (1 - ($book->discount / 100));

        // Check if the book already exists in the cart
        $existingCartBook = CartBook::where('cart_id', $cart->id)
            ->where('book_id', $book->id)
            ->first();

        if ($existingCartBook) {
            // Update quantity and total price
            $existingCartBook->quantity += $validated['quantity'];
            $existingCartBook->sub_total = $existingCartBook->quantity * $finalPrice;
            $existingCartBook->price = $finalPrice;
            $existingCartBook->save();
        } else {
            // Create new cart book
            $existingCartBook = CartBook::create([
                'cart_id' => $cart->id,
                'book_id' => $book->id,
                'quantity' => $validated['quantity'],
                'price' => $finalPrice,
                'sub_total' => $validated['quantity'] * $finalPrice,
            ]);
        }
        // Update the book's quantity in stock
        $book->quantity -= $validated['quantity'];
        $book->save();

        $lowStockThreshold = 4;

        if($book->quantity <= $lowStockThreshold) {
           $admin = User::where('role', 'admin')->first();
           if ($admin) {
               $admin->notify(new LowStockAlert($book));
           }
        }

        $cart->grand_total = CartBook::where('cart_id', $cart->id)->sum('sub_total');
        $cart->save();
        return response()->json([
            'message' => 'Book added to cart successfully',
            'cart_book' => $existingCartBook,
        ], 201);
    }

    public function updateQuantity(Request $request, $cartBookId){
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartBook = CartBook::find($cartBookId);
        if (!$cartBook) {
            return response()->json(['message' => 'Cart book not found'], 404);
        }

        $book = Book::find($cartBook->book_id);
        if ($book->quantity < $validated['quantity']) {
            return response()->json(['message' => 'Not enough stock available'], 400);
        }

        // Update the book's quantity in stock
        $book->quantity -= ($validated['quantity'] - $cartBook->quantity);
        $book->save();

        $cart = Cart::find($cartBook->cart_id);
        if ($cart) {
            $cart->grand_total = CartBook::where('cart_id', $cart->id)->sum('sub_total');
            $cart->save();
        }
        // Update cart book quantity
        $cartBook->quantity = $validated['quantity'];
        $cartBook->sub_total= ($validated['quantity'] * $cartBook->price);
        $cartBook->save();

        return response()->json(['message' => 'Cart book quantity updated successfully']);

    }

    public function getCart(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cart = Cart::with('cartBooks.book')->where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }
        $cart->grand_total = $cart->cartBooks->sum('sub_total');
        $cart->save();

        return response()->json($cart);
    }

    public function deleteCartBook(Request $request, $cartBookId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cartBook = CartBook::find($cartBookId);
        if (!$cartBook) {
            return response()->json(['message' => 'Cart book not found'], 404);
        }

        // Update the book's quantity in stock
        $book = Book::find($cartBook->book_id);
        if ($book) {
            $book->quantity += $cartBook->quantity;
            $book->save();
        }

        $cart = Cart::find($cartBookId);
        if ($cart) {
            $cart->grand_total = CartBook::where('cart_id', $cart->id)->sum('sub_total');
            $cart->save();
        }
        $cartBook->delete();

        return response()->json(['message' => 'Cart book deleted successfully']);
    }



    public function clearCart(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }
        // Get all CartBooks of this cart
        $cartBooks = CartBook::where('cart_id', $cart->id)->get();

        foreach ($cartBooks as $cartBook) {
            $book = Book::find($cartBook->book_id);
            if ($book) {
                // Return quantity back to book stock
                $book->quantity += $cartBook->quantity;
                $book->save();
            }
        }


        // Delete all cart books
        CartBook::where('cart_id', $cart->id)->delete();

        $cart->grand_total = 0;
        $cart->save();
        return response()->json(['message' => 'Cart cleared successfully']);
    }


}
