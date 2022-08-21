<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineWebhookController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// LINE系
Route::prefix('line')->group(function() {
    // LINEからのwebhookイベントは全てここに入る
    Route::post('/webhook', [LineWebhookController::class, 'webhook']);
});

// フロント側からのアクションはここに入る
Route::prefix('v1')->group(function() {
    Route::post('/regist', [AuthController::class, 'regist']);
});