<?php

use App\Controllers\Api\AuthController;
use App\Controllers\Api\CommentController;
use App\Controllers\Api\DashboardController;
use App\Middleware\AuthMiddleware;
use App\Middleware\DashboardMiddleware;
use App\Middleware\RateLimitMiddleware;
use Core\Routing\Route;

/**
 * Make something great with this app
 * keep simple yeah.
 */

Route::middleware(RateLimitMiddleware::class)->prefix('/session')->group(function () {
    Route::post('/', [AuthController::class, 'login']);
    Route::options('/'); // Preflight request [/api/session]
});

Route::middleware([RateLimitMiddleware::class, AuthMiddleware::class])->group(function () {

    // Dashboard
    Route::middleware(DashboardMiddleware::class)->group(function () {
        Route::get('/download', [DashboardController::class, 'download']);
        Route::options('/download');

        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::options('/stats');

        Route::put('/key', [DashboardController::class, 'rotate']);
        Route::options('/key');

        Route::get('/user', [DashboardController::class, 'user']);
        Route::patch('/user', [DashboardController::class, 'update']);
        Route::options('/user');
    });

    // Comment
    Route::prefix('/comment')->group(function () {

        Route::controller(CommentController::class)->group(function () {
            Route::post('/', 'create');
        });

        Route::options('/'); // Preflight request [/api/comment]

        Route::prefix('/{id}')->group(function () {
            Route::controller(CommentController::class)->group(function () {

                Route::put('/', 'update');
                Route::delete('/', 'destroy');

                // Like or unlike comment
                Route::post('/', 'like');
                Route::patch('/', 'unlike');
            });

            Route::options('/'); // Preflight request [/api/comment/{id}]
        });
    });

    // api v2
    Route::prefix('/v2')->group(function () {

        Route::get('/config', [DashboardController::class, 'configV2']);
        Route::options('/config');

        Route::prefix('/comment')->group(function () {
            Route::get('/', [CommentController::class, 'getV2']);
            Route::options('/');
        });
    });
});
