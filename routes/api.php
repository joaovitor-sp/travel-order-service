<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TravelOrderController;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

Route::get('test/generate-token', function () {
    $user = new \App\Domain\Models\User(['id' => 'user-123', 'name' => 'Test User', 'is_admin' => true]);
    $token = JWTAuth::claims([
        'name' => $user->name,
        'is_admin' => (bool) $user->is_admin,
    ])->fromUser($user);

    return response()->json(['token' => $token]);
});

Route::prefix('v1')->middleware(['jwt.stateless'])->group(function () {
    Route::get('/orders', [TravelOrderController::class, 'index']);
    Route::get('/orders/{id}', [TravelOrderController::class, 'show']);
    Route::post('/orders', [TravelOrderController::class, 'store']);
    Route::patch('/orders/{id}', [TravelOrderController::class, 'update']);
    Route::post('/orders/{id}/approve', [TravelOrderController::class, 'approve']);
    Route::post('/orders/{id}/cancel', [TravelOrderController::class, 'cancel']);
});
