<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController
{
    public function addComment(Request $request, $bookId)
    {
        // Validate the request
        $req = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        // Create a new comment

        // $comment = new Comment();
        // $comment->book_id = $bookId;
        // $comment->user_id = $user->id; // Assuming user is authenticated
        // $comment->comment = $request->validate('comment');
        // $comment->save();

        Comment::create([
            'book_id' => $bookId,
            'user_id' => $user->id, // Assuming user is authenticated
            'comment' => $req['comment'],
        ]);

        return response()->json(['message' => 'Comment added successfully',
                                "username"=> $user->name], 201);
    }

    public function getComments ($bookId)
    {
        $comments = Comment::where('book_id', $bookId)
            ->with('user:id,name') // Eager load the user relationship
            ->orderBy('created_at', 'desc')
            ->get(['id', 'book_id', 'user_id', 'comment', 'created_at']);

        return response()->json($comments);
    }
}
