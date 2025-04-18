<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// group of /api/books
Route::group(['prefix' => 'books'], function () {
    Route::get('/', 'App\Http\Controllers\BookController@listAllBooks');
    Route::get('/{id}', 'App\Http\Controllers\BookController@listBookById');
    Route::post('/', 'App\Http\Controllers\BookController@createBooks');
    Route::patch('/{id}', 'App\Http\Controllers\BookController@updateBooks');
    Route::delete('/{id}', 'App\Http\Controllers\BookController@deleteBooks');

});


Route::group(['prefix' => 'categories'], function () {
    Route::get('/', CategoryController::class . '@ListAllCategories');
    Route::get('/{id}', CategoryController::class . '@ListCategoryById');
    Route::post('/', CategoryController::class . '@createCategory');
    Route::patch('/{id}', CategoryController::class . '@updateCategory');
    Route::delete('/{id}', CategoryController::class . '@deleteCategory');
});


