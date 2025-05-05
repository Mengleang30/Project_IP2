<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GoogleController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Password\PasswordController;
use App\Http\Controllers\User\AdminController;
use App\Http\Controllers\User\CustomerController;
use App\Http\Controllers\WishListController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Public routes
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::get('login/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('login/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// check if the user is logged in
Route::get('/logged_user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/password/forgot', [PasswordController::class, 'sendResetCode']);
Route::post('/password/reset', [PasswordController::class, 'resetPassword']);
// group of /api/books
Route::group(['prefix' => 'books'], function () {
    Route::get('/', 'App\Http\Controllers\BookController@listAllBooks');
    Route::get('/id/{id}', 'App\Http\Controllers\BookController@listBookById');
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

});

Route::group(['prefix' => '/customer/cart', 'middleware' => ['auth:sanctum', 'isCustomer']], function () {
    Route::post('/add', [CartController::class, 'addToCart']);
    Route::get('/', [CartController::class, 'getCart']);
    Route::delete('/delete/{id}', [CartController::class, 'deleteCartBook']);
});



// Admin-only book routes
Route::group(['prefix' => '/admin/books', 'middleware' => ['auth:sanctum', 'isAdmin']], function () {
    Route::post('/', [BookController::class, 'createBooks']);
    Route::patch('/{id}', [BookController::class, 'updateBooks']);
    Route::delete('/{id}', [BookController::class, 'deleteBooks']);
});
// Admin-only category routes
Route::group(['prefix' => 'admin/categories', 'middleware' => ['auth:sanctum', 'isAdmin']], function () {
    Route::post('/', CategoryController::class . '@createCategory');
    Route::patch('/{id}', CategoryController::class . '@updateCategory');
    Route::delete('/{id}', CategoryController::class . '@deleteCategory');
});



// Route::group(['prefix' => 'users'], function () {
//    Route::get('/',[UserController::class, 'listAllUsers']);
//    Route::post('/',[UserController::class, 'createUser']);
//    Route::patch('/{id}',[UserController::class, 'updateUser']);
//    Route::patch('/update_password/{id}',[UserController::class, 'updatePassword']);
// })->middleware('auth:sanctum');
Route::group(['prefix' => '/admin/users_management/'], function () {
    Route::get('/',[AdminController::class, 'listAllCustomers']);
    Route::get('/{id}',[AdminController::class, 'findCustomerById']);
    Route::delete('/{id}',[AdminController::class, 'deleteCustomer']);
    Route::patch('/update_password/{id}',[AdminController::class, 'updatePassword']);
    Route::patch('/update_user',[AdminController::class, 'updateUser']);
})->middleware('auth:sanctum', 'isAdmin');




// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::get('/admin-dashboard', function () {
//         if (Auth::user()->role !== 'admin') {
//             return response()->json(['message' => 'Unauthorized'], 403);
//         }
//         return response()->json(['message' => 'Welcome to the Admin Dashboard']);
//     });

//     Route::get('/user-dashboard', function () {
//         if (Auth::user()->role !== 'customer') {
//             return response()->json(['message' => 'Unauthorized'], 403);
//         }
//         return response()->json(['message' => 'Welcome to the User Dashboard']);
//     });
// });



Route::group(['prefix' => '/customer/wishlist', 'middleware' => ['auth:sanctum', 'isCustomer']], function () {
    Route::post('/', [WishListController::class, 'addToWishlist']);
    Route::get('/', [WishListController::class, 'getWishlist']);
    Route::delete('/remove/{id}', [WishListController::class, 'removeWishlistById']);
    Route::delete(('/clear'), [WishListController::class, 'clearWishlist']);

});


Route::group(['prefix' => 'customer', 'middleware' => ['auth:sanctum', 'isCustomer']], function () {
    Route::patch('customer/update_information', [CustomerController::class, 'customerUpdateInformation']);
});



Route::middleware(['auth:sanctum', 'isAdmin'])->get('/admin', function () {
    return 'Hello Admin';
});
Route::middleware(['auth:sanctum', 'isCustomer'])->get('/customer', function () {
    return 'Hello Customer';
});
