<?php

// routes/api.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;

// login
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->withoutMiddleware('jwt.auth');
});

// register user
Route::prefix('users')->group(function () {
    Route::post('/', [UserController::class, 'register'])->withoutMiddleware('jwt.auth');
});

// auth routes
Route::middleware('jwt.auth')->group(function () {
    // admin routes
    Route::middleware('admin')->group(function () {
        // users
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
        });


        Route::prefix('tasks')->group(function () {
            Route::delete('/{task}', [TaskController::class, 'destroy']);
            Route::get('/deleted', [TaskController::class, 'indexDeleted']);
        });
    });

    // logout
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // users
    Route::prefix('users')->group(function () {
        Route::put('/password', [UserController::class, 'changePassword']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
    });

    // tasks
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{task}', [TaskController::class, 'show']);
        Route::put('/{task}', [TaskController::class, 'update']);
    });
});
