<?php

use App\Http\Controllers\LineController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// LINE系
Route::prefix('line')->controller(LineController::class)->group(function() {
    Route::get('/create-rich-menu', 'createRichMenu');
    Route::get('/unset-rich-menu', 'unsetRichMenu');
});