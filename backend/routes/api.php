<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookingStatusLogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\DamageReportController;
use App\Http\Controllers\Api\AuthController;
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

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello from Laravel Backend',
    ]);
});

// Public authentication routes
Route::post('/users/register', [AuthController::class, 'register'])->name('users.register');
Route::post('/register', [AuthController::class, 'register'])->name('api/register');
Route::post('/login', [UserController::class, 'login'])->name('api/login');
Route::post('/users/login', [UserController::class, 'login'])->name('users.login');

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/logout', [UserController::class, 'logout']);
});

// Admin only routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('users', UserController::class)->except(['create', 'edit']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('cities', CityController::class);
    Route::apiResource('coupons', CouponController::class);
    Route::apiResource('loyalty-points', LoyaltyPointController::class);
});

<<<<<<< HEAD
// User routes - make update public for testing (remove auth for now)
Route::put('/users/{user}', [UserController::class, 'update']);

// Authenticated user routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/{user}/avatar', [UserController::class, 'uploadAvatar']);
    Route::delete('/users/{user}/avatar', [UserController::class, 'removeAvatar']);
});

// Shop routes (accessible by both shop_owner and admin)
=======
// Shop routes (accessible by both shop and admin)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/shops', [ShopController::class, 'index']);
    Route::get('/shops/{shop}', [ShopController::class, 'show']);
});

// Shop owner routes
>>>>>>> view_shop
Route::middleware(['auth:sanctum', 'role:shop_owner'])->group(function () {
    Route::apiResource('vehicles', VehicleController::class);
    Route::apiResource('shops', ShopController::class)->except(['index', 'show']);
    Route::apiResource('bookings', BookingController::class);
    Route::apiResource('booking-status-logs', BookingStatusLogController::class);
    Route::apiResource('damage-reports', DamageReportController::class);
    Route::apiResource('feedback', FeedbackController::class);
    Route::apiResource('histories', HistoryController::class);
    Route::apiResource('payments', PaymentController::class);
});

// Customer routes (accessible by all authenticated users)
Route::middleware('auth:sanctum')->group(function () {
    // Add customer-specific routes here if needed
});
