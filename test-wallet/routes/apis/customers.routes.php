<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

//----------------------------- CLIENTES -----------------------------//
Route::group(['prefix' => 'customers'], function () {
    Route::get('/', [CustomerController::class, 'index']);
    Route::post('/create', [CustomerController::class, 'create']);
});


