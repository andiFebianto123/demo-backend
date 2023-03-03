<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('contoh', [IndexController::class, 'index']);
// Route::get('contoh/create', [IndexController::class, 'create']);

Route::middleware(['auth.custom'])->group(function(){
    // auth
    Route::get('login', [IndexController::class, 'login']);
    Route::get('register', [IndexController::class, 'register']);
    Route::post('login', [IndexController::class, 'customLogin']);
    Route::post('register', [IndexController::class, 'customRegistration']);
});


Route::middleware(['admin'])->group(function () {
    Route::get('logout', [IndexController::class, 'logout']);
    // product
    Route::get('product', [ProductController::class, 'index']);
    Route::get('product/create', [ProductController::class, 'create']);
    Route::post('product', [ProductController::class, 'store']);
    Route::get('product/{id}/edit', [ProductController::class, 'edit']);
    Route::post('product/{id}', [ProductController::class, 'update']);
    Route::get('product/search', [ProductController::class, 'search']);
    Route::delete('product/{id}', [ProductController::class, 'delete']);

    // transaction
    Route::get('transaction', [TransactionController::class, 'index']);
    Route::get('transaction/search', [TransactionController::class, 'search']);
});

Route::get('/', function(){
    return redirect('product');
});

