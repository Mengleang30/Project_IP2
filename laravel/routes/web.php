<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\BookController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/books/create', function () {
    return view('upload', [
        'categories' => \App\Models\Category::all()
    ]);
});

Route::post('/books', [BookController::class, 'createBooks'])->name('books.store');

// Route::get('auth/google/redirect', function() {
//     return Socialite::driver('google')->redirect();
// });

// Route::get('auth/google/callback', function() {

//     // handle login or register user here
// });

