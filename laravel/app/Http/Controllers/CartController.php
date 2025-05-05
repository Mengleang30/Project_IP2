<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Models\CartBook;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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

        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
        ]);

        // Check if the book already exists in the cart
        $existingCartBook = CartBook::where('cart_id', $cart->id)
            ->where('book_id', $book->id)
            ->first();
        if ($existingCartBook) {
            // Update quantity and total price
            $existingCartBook->quantity += $validated['quantity'];
            $existingCartBook->save();
        } else {
            // Create new cart book
            $existingCartBook = CartBook::create([
                'cart_id' => $cart->id,
                'book_id' => $book->id,
                'quantity' => $validated['quantity'],
                'price' => $book->price,
            ]);
        }
        // Update the book's quantity in stock
        $book->quantity -= $validated['quantity'];
        $book->save();

        $subTotalPrice = $existingCartBook->quantity * $book->price;


        return response()->json([
            'message' => 'Book added to cart successfully',
            'cart_book' => $existingCartBook,
            'sub_total_price' => $subTotalPrice,
        ], 201);
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

        $cartBook->delete();

        return response()->json(['message' => 'Cart book deleted successfully']);
    }
}
