<?php
// routes/web.php

use App\Http\Controllers\SoapControllers\SoapCustomerController;

Route::post('/soap/customer', [SoapCustomerController::class, 'soapCustomer']);


