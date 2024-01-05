<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Client\TransfertController;
use App\Http\Controllers\Api\Client\DepotController;
use App\Http\Controllers\Api\Client\BeneficiaryController;
use App\Http\Controllers\Api\Client\CardController;
use App\Http\Controllers\Api\Client\RetraitController;

Route::post('buy/card', [CardController::class, 'buyCard'])->name('buyCard');
Route::post('complete/buy/card/client', [CardController::class, 'completeBuyCard'])->name('completeBuyCard');
Route::post('set/default/card', [ClientController::class, 'setDefaultCard'])->name('setDefaultCard');
Route::post('liaison/carte', [CardController::class, 'liaisonCarte'])->name('liaisonCarte');
Route::get('get/user/cards/{id}', [CardController::class, 'getUserCards'])->name('getUserCards');
Route::get('cards/infos', [CardController::class, 'getCardsInfos'])->name('getCardsInfos');
Route::get('card-info/{id}', [CardController::class, 'getCardInfo'])->name('getCardInfo');
Route::get('card-info/account-info/{id}', [ClientController::class, 'getAccountInfo'])->name('getAccountInfo');
Route::get('card-info/balance/{id}', [CardController::class, 'getBalance'])->name('getBalance');
Route::get('card-info/client-info/{id}', [CardController::class, 'getClientInfo'])->name('getClientInfo');
Route::get('change/card/status', [CardController::class, 'changeCardStatus'])->name('changeCardStatus');

Route::get('get/beneficiaries/{user_id}', [BeneficiaryController::class, 'getBeneficiaries'])->name('getBeneficiaries');
Route::post('add/beneficiary/{user_id}', [BeneficiaryController::class, 'addBeneficiary'])->name('addBeneficiary');
Route::get('delete/beneficiary/{id}', [BeneficiaryController::class, 'deleteBeneficiary'])->name('deleteBeneficiary');
Route::post('edit/beneficiary/{id}', [BeneficiaryController::class, 'editBeneficiary'])->name('editBeneficiary');
Route::post('add/contact/{beneficiary_id}', [BeneficiaryController::class, 'addContact'])->name('addContact');
Route::post('edit/contact/{type}/{id}', [BeneficiaryController::class, 'editContact'])->name('editContact');
Route::get('delete/contact/{type}/{id}', [BeneficiaryController::class, 'deleteContact'])->name('deleteContact');

Route::post('add/transfert/client', [TransfertController::class, 'addNewTransfertClient'])->name('addTransfertClient');
Route::post('complete/transfert/client', [TransfertController::class, 'completeTransfertClient'])->name('completeTransfertClient');

Route::post('add/depot/client', [DepotController::class, 'addNewDepotClient'])->name('addDepotClient');
Route::post('complete/depot/client', [DepotController::class, 'completeDepotClient'])->name('completeDepotClient');

Route::get('get/client/pending/withdraws/{id}', [RetraitController::class, 'getClientPendingWithdraws'])->name('getClientPendingWithdraws');
Route::post('validation/retrait/client', [RetraitController::class, 'validationRetraitAttenteClient'])->name('validationRetraitAttenteClient');
Route::post('annulation/retrait/client', [RetraitController::class, 'annulationRetraitAttenteClient'])->name('annulationRetraitAttenteClient');


Route::post('initiation/bmo', [ClientController::class, 'initiationBmo'])->name('initiationBmo');
Route::post('confirmation/bmo', [ClientController::class, 'confirmationBmo'])->name('confirmationBmo');

Route::post('create/compte/client', [ClientController::class, 'createCompteClient'])->name('createCompteClient');
Route::post('login/compte/client', [ClientController::class, 'loginCompteClient'])->name('loginCompteClient');
Route::post('reset/password', [ClientController::class, 'resetPassword'])->name('resetPassword');
Route::post('config/pin', [ClientController::class, 'configPin'])->name('configPin');

Route::get('send/code/{id}', [ClientController::class, 'sendCode'])->name('sendCode');
Route::get('send/code/telephone/{telephone}', [ClientController::class, 'sendCodeTelephone'])->name('sendCodeTelephone');
Route::get('send/code/telephone/registration/{telephone}', [ClientController::class, 'sendCodeTelephoneRegistration'])->name('sendCodeTelephoneRegistration');
Route::post('check/code/otp', [ClientController::class, 'checkCodeOtp'])->name('checkCodeOtp');
Route::get('verification/phone/{user_id}', [ClientController::class, 'verificationPhone'])->name('verificationPhone');
Route::post('verification/info/perso', [ClientController::class, 'verificationInfoPerso'])->name('verificationInfoPerso');
Route::post('verification/info/piece', [ClientController::class, 'verificationInfoPiece'])->name('verificationInfoPiece');
Route::post('save/file', [ClientController::class, 'saveFile'])->name('saveFile');

Route::get('/get/bcv/client/info/{username}', [ClientController::class, 'getCompteClientInfo'])->name('getCompteClientInfo');
Route::get('get/fees', [ClientController::class, 'getFees'])->name('getFees');
Route::get('get/compte/client', [ClientController::class, 'getCompteClient'])->name('getCompteClient');
Route::get('get/client/transactions/{id}', [ClientController::class, 'getClientTransaction'])->name('getClientTransaction');
Route::get('get/client/pending/transactions/{id}', [ClientController::class, 'getClientPendingTransaction'])->name('getPendingClientTransaction');
Route::get('get/client/transactions/all/{id}', [ClientController::class, 'getClientAllTransaction'])->name('getClientAllTransaction');

Route::get('check/client/{id}', [ClientController::class, 'checkClient'])->name('checkClient');
Route::get('check/client/with/username/{username}', [ClientController::class, 'checkClientUsername'])->name('checkClient');
Route::post('login/otp/compte/client', [ClientController::class, 'loginOtpCompteClient'])->name('loginOtpCompteClient');
Route::get('get/compte/client/infos', [ClientController::class, 'getCompteClientInfo'])->name('getCompteClientInfo');
Route::post('token/valide', [ClientController::class, 'tokenValide'])->name('tokenValide');
Route::post('change/info/user', [ClientController::class, 'changeInfoUser'])->name('changeInfoUser');
Route::post('change/password/user', [ClientController::class, 'changePasswordUser'])->name('changePasswordUser');
Route::get('get/dashboard/{id}', [ClientController::class, 'getDashboard'])->name('getDashboard');
Route::get('get/solde/{id}', [ClientController::class, 'getSolde'])->name('getSolde');
Route::get('search/client/update/{id}', [ClientController::class, 'searchClientUpdate'])->name('searchClientUpdate');
Route::get('kkiapay/infos', [ClientController::class, 'getKkpInfos'])->name('getKkpInfos');
Route::get('get/services', [ClientController::class, 'getServices'])->name('getServices');
Route::get('get/mobile/wallets', [ClientController::class, 'getMobileWallet'])->name('getMobileWallet');

