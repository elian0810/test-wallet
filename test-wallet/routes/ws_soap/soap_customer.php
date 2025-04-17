<?php
// routes/web.php

use App\Http\Controllers\SoapControllers\SoapCustomerController;

//----------------------------- CLIENTES -----------------------------//
Route::group(['prefix' => 'customers'], function () {
    Route::post('/soap/create', [SoapCustomerController::class, 'soapCustomer']);
    Route::get('/soap/index', [SoapCustomerController::class, 'soapIndexCustomer']);
});

