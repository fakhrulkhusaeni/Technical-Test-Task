<?php

use App\Http\Controllers\GameTopupController;
use Illuminate\Support\Facades\Route;

Route::post('/game/topup/order', [GameTopupController::class, 'createOrder']);
Route::post('/game/topup/callback', [GameTopupController::class, 'callback']);
Route::post('/game/topup/callback/trigger', [GameTopupController::class, 'triggerCallback']);