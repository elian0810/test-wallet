<?php
// routes/web.php

use App\Http\Controllers\SoapControllers\SoapCreditLineController;
//----------------------------- LÍNEAS DE CRÉDITOS -----------------------------//
Route::group(['prefix' => 'credit-lines'], function () {
    Route::get('/soap/index', [SoapCreditLineController::class, 'soapIndexCreditLine']);
    Route::post('/soap/send-blance', [SoapCreditLineController::class, 'soapSendBalane']);
    Route::post('/soap/generate-token-total-debt', [SoapCreditLineController::class, 'soapGenerateTokenTotalDebt']);
    Route::post('/soap/debt-credit-line', [SoapCreditLineController::class, 'soapDebtCreditLine']);
});
