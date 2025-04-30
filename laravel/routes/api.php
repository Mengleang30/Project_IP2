<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WishListController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// group of /api/books
Route::group(['prefix' => 'books'], function () {
    Route::get('/', 'App\Http\Controllers\BookController@listAllBooks');
    Route::get('/id/{id}', 'App\Http\Controllers\BookController@listBookById');
    Route::post('/', 'App\Http\Controllers\BookController@createBooks');
    Route::patch('/{id}', 'App\Http\Controllers\BookController@updateBooks');
    Route::delete('/{id}', 'App\Http\Controllers\BookController@deleteBooks');
    Route::get('by_category/{category_id}', [BookController::class, 'listBookByCategory']);
    Route::get('/group_category', [BookController::class, 'groupBooksByCategory']);
    Route::get('/books_discount', [BookController::class, 'filterByDiscount']);
    Route::get('/search', [BookController::class, 'searchBooks']);
    Route::get('/show_books', [BookController::class, 'showBooks']);
    Route::get('/filter_by_category', [BookController::class, 'filterBooksByCategory']);
});

// group of /api/categories
Route::group(['prefix' => 'categories'], function () {
    Route::get('/', CategoryController::class . '@ListAllCategories');
    Route::get('/{id}', CategoryController::class . '@ListCategoryById');
    // Route::post('/', CategoryController::class . '@createCategory');
    // Route::patch('/{id}', CategoryController::class . '@updateCategory');
    // Route::delete('/{id}', CategoryController::class . '@deleteCategory');
});

Route::group(['prefix' => 'categories/admin'], function () {
    // Route::get('/', CategoryController::class . '@ListAllCategories');
    Route::get('/{id}', CategoryController::class . '@ListCategoryById');
    Route::post('/', CategoryController::class . '@createCategory');
    Route::patch('/{id}', CategoryController::class . '@updateCategory');
    Route::delete('/{id}', CategoryController::class . '@deleteCategory');
})->middleware('auth:sanctum');

Route::group(['prefix' => 'users'], function () {
   Route::get('/',[UserController::class, 'listAllUsers']);
   Route::post('/',[UserController::class, 'createUser']);
   Route::patch('/{id}',[UserController::class, 'updateUser']);
   Route::patch('/update_password/{id}',[UserController::class, 'updatePassword']);
});



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin-dashboard', function () {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json(['message' => 'Welcome to the Admin Dashboard']);
    });

    Route::get('/user-dashboard', function () {
        if (Auth::user()->role !== 'customer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json(['message' => 'Welcome to the User Dashboard']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/wishlist/add', [WishListController::class, 'addToWishlist']);
    Route::get('/wishlist', [WishListController::class, 'getWishlist']);
    // Route::get('/wishlist/{id}', [WishListController::class, 'getWishlistById']);
    Route::delete('/wishlist/remove/{id}', [WishListController::class, 'removeWishlistById']);
    Route::delete(('/wishlist/clear'), [WishListController::class, 'clearWishlist']);
});
