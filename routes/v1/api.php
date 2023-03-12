<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);

Route::get('users', [RequestController::class, 'users']);

Route::middleware('auth:sanctum')->group(function () {
    Route::group(['prefix' => 'requests'], function ($router) {
        Route::get('/', [RequestController::class, 'index']);
        Route::get('/my', [RequestController::class, 'myRequests']);
        Route::post('/', [RequestController::class, 'create']);

        Route::post('/{record}/approve', [RequestController::class, 'Approve']);
        Route::post('/{record}/decline', [RequestController::class, 'Decline']);


    });
});
