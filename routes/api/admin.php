<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ValidatorController;

Route::post('login/compte/validator', [ValidatorController::class, 'loginCompteValdiator'])->name('loginCompteClient');
Route::get('get/pending/customers', [ValidatorController::class, 'pendingCustomerAccount'])->name('pendingCustomerAccount');
Route::get('get/motif/list', [ValidatorController::class, 'getMotifList'])->name('getMotifList');
Route::post('validate/pending/customers', [ValidatorController::class, 'validatePendingCustomerAccount'])->name('validatePendingCustomerAccount');
Route::post('reject/pending/customers', [ValidatorController::class, 'rejectPendingCustomerAccount'])->name('rejectPendingCustomerAccount');
