<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [\App\Http\Controllers\V1\AuthController::class, 'register']);
    Route::post('auth/sign-in', [\App\Http\Controllers\V1\AuthController::class, 'sign_in']);
    Route::middleware(['auth:sanctum', 'checkRole:ADMIN'])->prefix('admin')->group(function () {
        Route::resource('category', \App\Http\Controllers\V1\Admin\CategoryController::class);
        Route::resource('ticket', \App\Http\Controllers\V1\Admin\TicketController::class);
        Route::post('ticket/{ticket_id}/replay', [\App\Http\Controllers\V1\Admin\ReplayTicketController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'checkRole:USER'])->prefix('user')->group(function () {
        Route::resource('ticket', \App\Http\Controllers\V1\User\TicketController::class);
        Route::post('ticket/{ticket_id}/replay', [\App\Http\Controllers\V1\User\ReplayTicketController::class, 'store']);
        Route::delete('ticket/replay/{id}', [\App\Http\Controllers\V1\User\ReplayTicketController::class, 'destroy']);

        Route::resource('category', \App\Http\Controllers\V1\User\CategoryController::class);
    });
});
