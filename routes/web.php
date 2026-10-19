<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\TaskController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/projects');

Route::middleware('guest')->group(function () {
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('projects', ProjectController::class)->only(['index', 'store', 'show', 'destroy']);

    // JSON endpoints for the project page's AJAX calls.
    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});
