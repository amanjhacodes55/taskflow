<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

/*
| Version 1 of the TaskFlow API, served under /api/v1. Clients authenticate with a Sanctum
| token sent as "Authorization: Bearer <token>". Every route is rate limited (see AppServiceProvider).
*/
Route::prefix('v1')->middleware('throttle:api')->name('api.')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register'])->name('register');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me'])->name('me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('logout');

        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('projects.tasks', TaskController::class)->shallow();
        Route::apiResource('tasks.comments', CommentController::class)->only(['index', 'store']);
    });
});
