<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/books/create', function () {
    return view('upload', [
        'categories' => \App\Models\Category::all()
    ]);
});

Route::post('/books', [BookController::class, 'createBooks'])->name('books.store');
