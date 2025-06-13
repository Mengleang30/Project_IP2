<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\Feedback;
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



       $admin = User::where('role', 'admin')->first();
           if ($admin) {
               $admin->notify(new Feedback(
                $bookId,
                $user->id,
                $req['comment']));
           }
        return response()->json(['message' => 'Comment added successfully',
                                "username"=> $user->name], 201);
    }

    public function getComments ($bookId)
    {
        $comments = Comment::where('book_id', $bookId)
            ->with('user:id,name') // Eager load the user relationship
            ->orderBy('created_at', 'desc')
            ->get(['id', 'book_id', 'user_id', 'comment', 'created_at']);

        // Map comments to include username
        $commentsWithUser = $comments->map(function ($comment) {
            return [
            'id' => $comment->id,
            'book_id' => $comment->book_id,
            'user_id' => $comment->user_id,
            'comment' => $comment->comment,
            'created_at' => $comment->created_at,
            'username' => $comment->user ? $comment->user->name : null,
            ];
        });

        return response()->json($commentsWithUser);
    }
}
