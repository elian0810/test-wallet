<?php

use App\Http\Controllers\CreditLineController;
use Illuminate\Support\Facades\Route;

//----------------------------- LÍNEAS DE CRÉDITOS -----------------------------//
Route::group(['prefix' => 'credit-lines'], function () {
    Route::get('/', [CreditLineController::class, 'index']);
    Route::post('/open', [CreditLineController::class, 'openCreditLine']);
    Route::post('/send-balane', [CreditLineController::class, 'sendBalane']);
    Route::post('/generate-token-total-debt', [CreditLineController::class, 'generateTokenTotalDebt']);
});
