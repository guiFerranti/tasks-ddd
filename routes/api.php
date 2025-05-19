<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->withoutMiddleware(['jwt.auth']);
});

Route::prefix('users')->group(function () {
    Route::post('/register', [UserController::class, 'register'])
        ->withoutMiddleware(['jwt.auth']);

    Route::put('/password', [UserController::class, 'changePassword'])
        ->middleware('jwt.auth');
});

Route::middleware('jwt.auth')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('tasks', TaskController::class)->except(['index', 'show']);
});

Route::middleware(['jwt.auth', 'admin'])->group(function () {
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
});

Route::middleware('jwt.auth')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
});

Route::middleware(['jwt.auth', 'admin'])->group(function () {
    Route::get('/tasks/deleted', [TaskController::class, 'indexDeleted']);
});
