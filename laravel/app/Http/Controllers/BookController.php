<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BookController extends Controller
{
    public function listAllBooks()
    {
        return Book::all();
    }

    public function listBookById($id)
    {
        // Logic to get a single book by ID
        $book =  Book::find($id);

        if ($book) {
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

    public function listBookByCategory($categoryId)
    {
        $books = Book::where('category_id', $categoryId)->with('categories')->limit(1)->get();
        if ($books->isEmpty()) {
            return response()->json(['message' => 'No books found in this category'], 404);
        }
        return response()->json($books);
    }

    public function groupBooksByCategory()
    {
        $categories = Category::with('books')->get();

        $filter = $categories->filter(function ($category) {
            return $category->books->count() >= 2;
        })->take(8);

        $result = $filter->map(function ($category) {
            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'books' => $category->books,
            ];
        });

        return response()->json($result);
    }

    public function filterByDiscount()
    {
        $books = Book::where('discount', '>', 20)->get();

        return response()->json($books);
    }

    public function searchBooks(Request $request)
    {

        $query = $request->input('query');

        if(empty($query)) {
            return response()->json(['message' => 'Please search somethings'], 400);
        }

        $books = Book::where('title', 'like','%' . $query . '%')
            ->orWhere('author', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%')
            ->get();

        // Check if any books were found

        if ($books->isEmpty()) {
            return response()->json([
                'query' => $query,
                'message' => 'No books found'
                ], 404);
        }
        return response()->json($books);
    }

    public function ShowBooks() {

        $books = Book::inRandomOrder()->limit(30)->get();
        return response()->json($books);

    }


    public function filterBooksByCategory(Request $request)
    {
        $categoryId = $request->input('category_id');

        if (empty($categoryId)) {
            return response()->json(['message' => 'Please provide a category ID'], 400);
        }

        $books = Book::where('category_id', $categoryId)->get();

        if ($books->isEmpty()) {
            return response()->json(['message' => 'No books found in this category'], 404);
        }

        return response()->json($books);
    }

    public function reStock(Request $request, $bookId){

        $validated =  $request ->validate([
            'quantity' => 'nullable|integer|min:0',
        ]);

        $book = Book::find($bookId);

        if(!$book) {
            return response()->json([
                "message"=> "Book not found !"
            ]);
        }

        $book->quantity += $validated['quantity'];
        $book->save();

        return response()->json([
            "message"=> "Re-Stock successfully !",
            "book_id" => $bookId,
            "quantity"=> $book->quantity
        ]);

    }
}
