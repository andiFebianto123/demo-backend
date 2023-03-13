<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MitraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('transaction/add-transaction', [TransactionController::class, 'store']);
Route::get('customer/mitra-search', [CustomerController::class, 'searchMitra']);
Route::post('customer/order', [CustomerController::class, 'sendOrder']);
Route::get('customer/order', [CustomerController::class, 'listOrder']);
Route::get('customer/detail-order', [CustomerController::class, 'detailOrder']);

// mitra
Route::get('mitra/order', [MitraController::class, 'listOrder']);
Route::get('mitra/order-detail', [MitraController::class, 'detailOrder']);
Route::get('mitra/all-service', [MitraController::class, 'allService']);
Route::post('mitra/request-order', [MitraController::class, 'requestOrder']);
