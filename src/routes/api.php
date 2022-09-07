<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineWebhookController;
use App\Http\Controllers\UserController;

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



// LINE系
Route::prefix('line')->group(function() {
    // LINEからのwebhookイベントは全てここに入る
    Route::post('/webhook', [LineWebhookController::class, 'webhook']);
});

// フロント側からのアクションはここに入る
Route::prefix('v1')->group(function() {
    Route::post('/regist', [AuthController::class, 'regist']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class , 'logout']);
    // ログイン後 アクセスできるエンドポイント
    Route::middleware('auth:sanctum')->group(function() {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/line/friends', [LineController::class, 'friends']);
        Route::post('/line/get-followers', [LineController::class, 'getFollowers']);
        Route::post('/user/settings', [UserController::class, 'settings']);
        Route::post('/user/create-line-bot', [UserController::class, 'createLineBot']);
    });
});