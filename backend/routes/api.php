<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookingStatusLogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\DamageReportController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\LoyaltyPointController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/auth-user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello from Laravel Backend',
    ]);
});

Route::apiResource('users', UserController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('vehicles', VehicleController::class);
Route::apiResource('shops', ShopController::class);
Route::apiResource('bookings', BookingController::class);
Route::apiResource('booking-status-logs', BookingStatusLogController::class);
Route::apiResource('cities', CityController::class);
Route::apiResource('coupons', CouponController::class);
Route::apiResource('damage-reports', DamageReportController::class);
Route::apiResource('feedback', FeedbackController::class);
Route::apiResource('histories', HistoryController::class);
Route::apiResource('loyalty-points', LoyaltyPointController::class);
Route::apiResource('payments', PaymentController::class);


use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
