<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Logout route
    Route::post('/logout', [AuthController::class, 'logout']);

    // Change password routes
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/change-password/{id}', [AuthController::class, 'changeUserPassword']);

    // Ping route
    Route::get('/ping', [UserController::class, 'ping'])->name('user.ping');

    // User routes
    Route::get('/users', [UserController::class, 'GetAllUsers'])->name('user.index');
    Route::get('/users/{id}', [UserController::class, 'UserDetails'])->name('user.show');
    Route::put('/users/{id}', [UserController::class, 'UpdateUser'])->name('user.update');
    Route::delete('/users/{id}', [UserController::class, 'DeleteUser'])->name('user.destroy');

    // Account routes
    Route::get('/accounts', [AccountController::class, 'getAccounts'])->name('account.index');
    Route::post('/accounts', [AccountController::class, 'addAccount'])->name('account.store');
    Route::get('/accounts/{id}', [AccountController::class, 'getAccountDetails'])->name('account.show');
    Route::put('/accounts/{id}', [AccountController::class, 'updateAccount'])->name('account.update');
    Route::put('/account/reset-password/{id}', [AccountController::class, 'resetPassword'])->name('account.reset_password');
    Route::delete('/accounts/{id}', [AccountController::class, 'deleteAccount'])->name('account.destroy');
});