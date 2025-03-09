<?php

use App\Http\Controllers\API\V1\AiVideoGeneratorController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('v1')
    ->middleware(\App\Http\Middleware\ApiLoggerMiddleware::class)
    ->group(function () {
// Public routes
        Route::get('ping', function () {
            return response()->json(['message' => 'pong', 'timestamp' => now()]);
        });

// Protected routes
        Route::middleware('api.token')->group(function () {
            // User endpoints
            Route::get('user', function () {
                return auth()->user();
            });

            //generate video
            Route::post('/generate-video', [    AiVideoGeneratorController::class, 'generateVideo']);
            Route::get('/video-job/{workflowId}', [AiVideoGeneratorController::class, 'getJobStatus']);
        });
    });