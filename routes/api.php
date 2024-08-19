<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KYCController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderEnController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfitShareController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\ResellProductController;
use App\Http\Controllers\BankDetailsController;
use App\Http\Controllers\CityListController;
use App\Http\Controllers\ExcelExportController;
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
Route::middleware('authToken')->post('update-reseller-password', [AuthController::class, 'UpdateResellerPassword']);


Route::middleware('authToken')->post('user-data', [DashboardController::class, 'Getuserdata']);
Route::middleware('authToken')->post('add-kyc-info', [KYCController::class, 'addKYCInformation']);
Route::middleware('authToken')->post('get-profile-data', [ProfileController::class, 'getSellerProfileInfo']);
Route::middleware('authToken')->post('update-Profile-dtails', [ProfileController::class, 'updateProfileData']);

Route::middleware('authToken')->post('update-bank-dtails', [BankDetailsController::class, 'UpdateBankInfo']);
Route::middleware('authToken')->post('update-edit-bank-dtails', [BankDetailsController::class, 'UpdateEditBankInfo']);

Route::middleware('authToken')->post('get-all-products', [ProductController::class, 'getAllProductList']);
Route::middleware('authToken')->post('get-CId-products', [ProductController::class, 'getCIDProductList']);
Route::middleware('authToken')->post('get-product-info', [ProductController::class, 'getProductInfoByProductId']);
Route::middleware('authToken')->post('resell-product', [ResellProductController::class, 'addNewResellProduct']);
Route::middleware('authToken')->post('get-resell-product-list', [ResellProductController::class, 'getAllResellProducts']);

Route::middleware('authToken')->post('get-productDelivery-info', [ProductController::class, 'getAllResellProductsDeliverycharg']);
Route::middleware('authToken')->post('get-productDelivery-infoPID', [ProductController::class, 'getAllResellProductsDeliverychargProId']);

Route::middleware('authToken')->post('place-order', [OrderController::class, 'placeNewOrderRequest']);
Route::middleware('authToken')->post('get-order-list', [OrderController::class, 'getOrderList']);
Route::middleware('authToken')->post('get-order-info', [OrderController::class, 'getOrderInfoListByOrderNumberNew']);
Route::middleware('authToken')->post('cancle-order', [OrderController::class, 'cancleOrder']);

Route::middleware('authToken')->post('get-profit-log', [ProfitShareController::class, 'getProfitShareLogBySeller']);
Route::middleware('authToken')->post('dashboard-data', [DashboardController::class, 'getDashboardData']);
Route::middleware('authToken')->post('get-team', [ResellerController::class, 'getTeam']);

Route::middleware('authToken')->post('add-to-cart', [CartController::class, 'addCartProduct']);
Route::middleware('authToken')->post('get-cart-items-count', [CartController::class, 'getCartItemsCount']);
Route::middleware('authToken')->post('get-cart-items-list', [CartController::class, 'getCartItems']);
Route::middleware('authToken')->post('get-cart-items-list-remove', [CartController::class, 'removeCartItemById']);

Route::middleware('authToken')->post('place-order-by-cart', [OrderEnController::class, 'placeNewOrder']);
Route::middleware('authToken')->post('remove-product-from-list', [ResellProductController::class, 'removeResellProduct']);
Route::middleware('authToken')->post('update-product-from-price', [ResellProductController::class, 'updatePriceResellProduct']);
Route::middleware('authToken')->post('get-cart-total', [CartController::class, 'getCartTotalAmount']);

Route::middleware('authToken')->post('get-city-list', [CityListController::class, 'getAllCityList']);
Route::middleware('authToken')->post('DownloadExcel', [ExcelExportController::class, 'DownloadExcel']);