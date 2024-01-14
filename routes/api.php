<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KYCController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\ResellProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [AuthController::class, 'registerNewReseller']);
Route::post('login', [AuthController::class, 'authenticateUser']);

Route::middleware('authToken')->post('add-kyc-info', [KYCController::class, 'addKYCInformation']);
Route::middleware('authToken')->post('get-profile-data', [ProfileController::class, 'getSellerProfileInfo']);

Route::middleware('authToken')->post('get-all-products', [ProductController::class, 'getAllProductList']);
Route::middleware('authToken')->post('get-product-info', [ProductController::class, 'getProductInfoByProductId']);
Route::middleware('authToken')->post('resell-product', [ResellProductController::class, 'addNewResellProduct']);
Route::middleware('authToken')->post('get-resell-product-list', [ResellProductController::class, 'getAllResellProducts']);

Route::middleware('authToken')->post('place-order', [OrderController::class, 'placeNewOrderRequest']);
Route::middleware('authToken')->post('get-order-list', [OrderController::class, 'getOrderList']);
Route::middleware('authToken')->post('get-order-info', [OrderController::class, 'getOrderInfoByOrderNumber']);