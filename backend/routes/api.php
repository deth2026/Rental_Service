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

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/users/register', [AuthController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);

// Auth routes with /auth prefix
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/auth/me', [AuthController::class, 'me']);

Route::middleware('auth:sanctum')->get('/auth-user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello from Laravel Backend',
    ]);
});

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/logout', [UserController::class, 'logout']);
});

// Admin only routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('users', UserController::class)->except(['create', 'edit']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('cities', CityController::class)->except(['index']);
});

// Public cities endpoint - needed for dropdowns in shop creation forms
Route::get('/cities', [CityController::class, 'index']);

// Admin + Shop Owner shared routes (coupons & loyalty points management)
Route::middleware(['auth:sanctum', 'role:admin,shop_owner'])->group(function () {
    Route::apiResource('coupons', CouponController::class);
    Route::apiResource('loyalty-points', LoyaltyPointController::class);
});

// User routes - make update public for testing (remove auth for now)
Route::put('/users/{user}', [UserController::class, 'update']);

// Authenticated user routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/{user}/avatar', [UserController::class, 'uploadAvatar']);
    Route::delete('/users/{user}/avatar', [UserController::class, 'removeAvatar']);
});

// Profile settings routes (from feature/setting.user)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('users/{id}/update-profile', [UserController::class, 'updateProfile']);
    Route::post('users/{id}/change-password', [UserController::class, 'changePassword']);
});

// Public shop routes (for customer/user shop listing)
Route::get('/shops', [ShopController::class, 'index']);
Route::get('/shops/{shop}', [ShopController::class, 'show']);

// Public vehicle routes (for customers to view vehicles)
Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);

// Protected vehicle routes - require authentication for listing vehicles
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/vehicles', [VehicleController::class, 'index']);
    
    // Shop owner can create, update, delete their own vehicles
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);
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
