<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KYCController;
use App\Http\Controllers\ProfileController;
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