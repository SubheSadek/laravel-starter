<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->as('auth.')
    ->controller(AuthController::class)
    ->group(function ($route) {
        $route->post('register', 'register')->name('register')->middleware(['guest', 'throttle:8,1']);
        $route->post('verify-user', 'verifyUser')->name('verify-user')->middleware(['guest', 'throttle:8,1']);
        $route->post('login', 'login')->name('login')->middleware(['guest', 'throttle:8,1']);
        $route->get('auth-user', 'authUser')->name('auth-user')->middleware('auth:sanctum');
        $route->post('logout', 'logout')->name('logout')->middleware('auth:sanctum');
    });
