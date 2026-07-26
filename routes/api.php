<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rebuilds the legacy mobile-attendance API (api/rest/api.php: login,
| logout, get_attendance, save_attendance) behind Sanctum token auth —
| see App\Http\Controllers\Api\AuthController's docblock for why this
| isn't a byte-compatible port of that dispatcher.
|
*/

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('attendance', [AttendanceController::class, 'index']);
    Route::post('attendance', [AttendanceController::class, 'store']);
});
