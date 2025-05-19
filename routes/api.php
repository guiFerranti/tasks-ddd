<?php

// routes/api.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;

// login
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// create user
Route::prefix('users')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
});

Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware(['jwt.auth', 'admin']);

Route::put('/users/{id}', [UserController::class, 'update'])->middleware('jwt.auth');

Route::middleware('jwt.auth')->group(function () {
    // logout
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // users
    Route::prefix('users')->group(function () {
        Route::put('/password', [UserController::class, 'changePassword']);
        Route::get('/{id}', [UserController::class, 'show']);
    });

    // tasks
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{task}', [TaskController::class, 'show']);
        Route::put('/{task}', [TaskController::class, 'update']);
    });

    // admins routes
    Route::middleware('admin')->group(function () {
        // users
        Route::get('/users', [UserController::class, 'index']);

        // tasks
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
        Route::get('/tasks/deleted', [TaskController::class, 'indexDeleted']);
    });
});
