<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WishListController extends Controller
{
    public function addToWishlist(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id'
        ]);

        $user = $request->user();

        $exists = Wishlist::where('user_id', $user->id)
            ->where('book_id', $request->book_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Book already in wishlist'
            ], 409);
        }

        Wishlist::create([
            'user_id' => $user->id,
            'book_id' => $request->book_id
        ]);

        return response()->json([
            'message' => 'Book added to wishlist'
        ], 201);
    }

    public function getWishlist(Request $request)
    {
        $user = $request->user();

        $wishlist = $user->wishlist()
            ->with('book')
            ->get();

        return response()->json($wishlist);
    }

    // public function removeWishlist(Request $request)
    // {
    //     $request->validate([
    //         'book_id' => 'required|exists:books,id'
    //     ]);

    //     $user = $request->user();

    //     Wishlist::where('user_id', $user->id)
    //         ->where('book_id', $request->book_id)
    //         ->delete();

    //     return response()->json([
    //         'message' => 'Book removed from wishlist'
    //     ]);
    // }
    public function removeWishlistById($id, Request $request)
    {
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'User not authenticated'
        ], 401);
    }

    $wishlist = Wishlist::where('id' ,$id)
        ->where('user_id', $user->id)
        // ->with('book')
        ->first();

    if (!$wishlist) {
        return response()->json([
            'message' => 'Wishlist item not found'
        ], 404);
    }

    $wishlist->delete();

    return response()->json([
        'message' => 'Book removed from wishlist'
    ]);
    }

    // public function findWishlistById($id, Request $request)
    // {
    //     $user = $request->user();

    //     if (!$user) {
    //         return response()->json([
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     $wishlist = Wishlist::where('id', $id)
    //         ->where('user_id', $user->id)->first();
    //         // ->with('book')


    //     if (!$wishlist) {
    //         return response()->json([
    //             'message' => 'Wishlist item not found'
    //         ], 404);
    //     }

    //    $wishlist->load('book');
    //     return response()->json($wishlist);
    // }

    public function clearWishlist(Request $request)
    {
        $user = $request->user();

        Wishlist::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Wishlist cleared'
        ]);
    }
}
