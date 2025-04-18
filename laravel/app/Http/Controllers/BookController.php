<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController
{
    public function listAllBooks()
    {
        return Book::all();
    }

    public function listBookById( $id)
    {
        // Logic to get a single book by ID
        $book =  Book::find($id);

        if ($book){
            return response()->json($book);
        } else {
            return response()->json(['message' => 'Book not found'], 404);
        }
    }

    public function createBooks(Request $request)
    {
        // Validate the request data
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string',
            'published_date' => 'nullable|date',
            'discount' => 'nullable|numeric|min:0|max:100',
            'quantity' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'url_image' => 'nullable|url',
            'path_image' => 'nullable|string',
            'languages' => 'nullable|array',
            'category_id' => 'required|exists:categories,id',

        ]);



        $book = Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'description' => $request->description,
            'published_date' => $request->published_date,
            'discount' => $request->discount,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'url_image' => $request->url_image,
            'path_image' => $request->path_image,
            'languages' => json_encode($request->languages),
            'category_id' => $request->category_id,
        ]);
        return response()->json($book, 201);


    }

    public function updateBooks(Request $request, $id)
    {
        // Logic to update an existing book
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        // Validate the request data
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string',
            'published_date' => 'nullable|date',
            'discount' => 'nullable|numeric|min:0|max:100',
            'quantity' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'url_image' => 'nullable|url',
            'path_image' => 'nullable|string',
            'languages' => 'nullable|array',
            'category_id' => 'required|exists:categories,id',

        ]);
        $book->update([
            'title' => $request->title,
            'author' => $request->author,
            'description' => $request->description,
            'published_date' => $request->published_date,
            'discount' => $request->discount,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'url_image' => $request->url_image,
            'path_image' => $request->path_image,
            'languages' => json_encode($request->languages),
            'category_id' => $request->category_id,
        ]);
        return response()->json($book, 200);
    }

    public function deleteBooks($id)
    {
        // Logic to delete a book
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        $book->delete();
        return response()->json(['message' => 'Book deleted successfully'], 200);
    }
}
