<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PartenaireController;
use App\Http\Controllers\Api\ValidatorController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Client\TransfertController;
use App\Http\Controllers\Api\Client\DepotController;
use App\Http\Controllers\Api\Client\BeneficiaryController;

Route::post('login/partenaire', [PartenaireController::class, 'loginPartenaire'])->name('loginPartenaire');
Route::get('get/services/partenaire', [PartenaireController::class, 'getServices'])->name('getServices');
Route::get('get/dashboard/partenaire/{id}', [PartenaireController::class, 'getDashboardPartenaire'])->name('getDashboardPartenaire');

Route::post('add/withdraw/partner', [PartenaireController::class, 'addWithdrawPartenaire'])->name('addWithdrawPartenaire');
Route::post('complete/withdraw/partner', [PartenaireController::class, 'completeWithdrawPartenaire'])->name('completeWithdrawPartenaire');
Route::post('cancel/client/withdraw/as/partner', [PartenaireController::class, 'cancelClientWithdrawAsPartner'])->name('cancelClientWithdrawAsPartner');
Route::post('add/depot/partner', [PartenaireController::class, 'addDepotPartenaire'])->name('addDepotPartenaire');
Route::post('complete/depot/partner', [PartenaireController::class, 'completeDepotPartenaire'])->name('completeDepotPartenaire');

Route::get('get/partner/pending/customers/transactions/{user_partenaire_id}', [PartenaireController::class, 'getPartnerPendingCustomersTransactions'])->name('getPartnerPendingCustomersTransactions');
Route::get('get/partner/pending/admins/transactions/{user_partenaire_id}', [PartenaireController::class, 'getPartnerPendingAdminsTransactions'])->name('getPartnerPendingAdminsTransactions');
Route::get('get/partner/all/transactions/{user_partenaire_id}', [PartenaireController::class, 'getPartnerAllTransactions'])->name('getPartnerAllTransactions');

Route::post('update/user/partenaire/info', [PartenaireController::class, 'updateUserPartenaireInfo'])->name('updateUserPartenaireInfo');
Route::post('update/user/partenaire/password', [PartenaireController::class, 'updateUserPartenairePassword'])->name('updateUserPartenairePassword');
Route::get('get/user/partenaire/info/{id}', [PartenaireController::class, 'getUserPartenaireInfo'])->name('getUserPartenaireInfo');

Route::get('get/partner/wallets/{partnerId}', [PartenaireController::class, 'getPartnerWallets'])->name('getPartnerWallets');
Route::post('add/partner/wallet/{walletType}', [PartenaireController::class, 'addPartnerWallet'])->name('addPartnerWallet');
Route::post('update/partner/wallet/{walletType}/{walletId}', [PartenaireController::class, 'updatePartnerWallet'])->name('updatePartnerWallet');
Route::get('delete/partner/wallet/{walletId}', [PartenaireController::class, 'deletePartnerWallet'])->name('deletePartnerWallet');

Route::post('withdraw/partner/to/wallet/{walletId}', [PartenaireController::class, 'withdrawPartnerToWallet'])->name('withdrawPartnerWallet');
Route::post('complete/withdraw/partner/to/wallet', [PartenaireController::class, 'completeWithdrawPartnerToWallet'])->name('completeWithdrawPartnerWallet');
Route::post('withdraw/partner/to/distribution/account', [PartenaireController::class, 'withdrawPartnerToDistributionAccount'])->name('withdrawPartnerDistributionAccount');
Route::post('withdraw/partner/to/atm', [PartenaireController::class, 'withdrawPartnerToAtm'])->name('withdrawPartnerToAtm');


Route::post('deposit/partner/from/wallet/{walletId}', [PartenaireController::class, 'compteCommission'])->name('compteCommission');

Route::get('get/compte/commission/{id}', [PartenaireController::class, 'compteCommission'])->name('compteCommission');
Route::get('get/compte/distribution/{id}', [PartenaireController::class, 'compteDistribution'])->name('compteDistribution');

Route::post('retrait/commission/{id}', [PartenaireController::class, 'retraitCommission'])->name('retraitCommission');
Route::post('retrait/distribution/{id}', [PartenaireController::class, 'retraitDistribution'])->name('retraitDistribution');

Route::post('config/partner/pin', [App\Http\Controllers\Api\PartenaireController::class, 'configPin'])->name('configPin');

Route::get('liste/retrait/unvalidate/partenaire', [PartenaireController::class, 'listeRetraitUnvalidatePartenaire'])->name('listeRetraitUnvalidatePartenaire');
Route::get('show/retrait/partenaire/{id}', [PartenaireController::class, 'showRetraitPartenaire'])->name('showRetraitPartenaire');
Route::post('cancel/retrait/partenaire', [PartenaireController::class, 'cancelRetraitPartenaire'])->name('cancelRetraitPartenaire');
Route::post('validate/retrait/partenaire', [PartenaireController::class, 'validateRetraitPartenaire'])->name('validateRetraitPartenaire');

Route::get('liste/depot/partenaire', [PartenaireController::class, 'listeDepotPartenaire'])->name('listeDepotPartenaire');
Route::get('liste/depot/unvalidate/partenaire', [PartenaireController::class, 'listeDepotUnvalidatePartenaire'])->name('listeDepotUnvalidatePartenaire');
Route::get('show/depot/partenaire/{id}', [PartenaireController::class, 'showDepotPartenaire'])->name('showDepotPartenaire');
Route::post('cancel/depot/partenaire', [PartenaireController::class, 'cancelDepotPartenaire'])->name('cancelDepotPartenaire');
Route::post('validate/depot/partenaire', [PartenaireController::class, 'validateDepotPartenaire'])->name('validateDepotPartenaire');

Route::get('liste/user/partenaire', [PartenaireController::class, 'listeUserPartenaire'])->name('listeUserPartenaire');
Route::get('show/user/partenaire', [PartenaireController::class, 'showUserPartenaire'])->name('showUserPartenaire');
Route::post('add/user/partenaire', [PartenaireController::class, 'addUserPartenaire'])->name('addUserPartenaire');
Route::post('edit/user/partenaire', [PartenaireController::class, 'editUserPartenaire'])->name('editUserPartenaire');
Route::post('delete/user/partenaire', [PartenaireController::class, 'deleteUserPartenaire'])->name('deleteUserPartenaire');
Route::post('activation/user/partenaire', [PartenaireController::class, 'activationUserPartenaire'])->name('activationUserPartenaire');
Route::post('desactivation/user/partenaire', [PartenaireController::class, 'desactivationUserPartenaire'])->name('desactivationUserPartenaire');
Route::post('reset/user/partenaire', [PartenaireController::class, 'resetUserPartenaire'])->name('resetUserPartenaire');

Route::get('liste/partenaire/seuil', [PartenaireController::class, 'listePartenaireSeuil'])->name('listePartenaireSeuil');
Route::post('add/partenaire/seuil', [PartenaireController::class, 'addPartenaireSeuil'])->name('addPartenaireSeuil');
Route::post('edit/partenaire/seuil', [PartenaireController::class, 'editPartenaireSeuil'])->name('editPartenaireSeuil');
Route::post('delete/partenaire/seuil', [PartenaireController::class, 'deletePartenaireSeuil'])->name('deletePartenaireSeuil');
Route::post('activation/partenaire/seuil', [PartenaireController::class, 'activationPartenaireSeuil'])->name('activationPartenaireSeuil');
Route::post('desactivation/partenaire/seuil', [PartenaireController::class, 'desactivationPartenaireSeuil'])->name('desactivationPartenaireSeuil');

Route::get('liste/partenaire/limit', [PartenaireController::class, 'listePartenaireLimit'])->name('listePartenaireLimit');
Route::post('add/partenaire/limit', [PartenaireController::class, 'addPartenaireLimit'])->name('addPartenaireLimit');
Route::post('edit/partenaire/limit', [PartenaireController::class, 'editPartenaireLimit'])->name('editPartenaireLimit');
Route::post('delete/partenaire/limit', [PartenaireController::class, 'deletePartenaireLimit'])->name('deletePartenaireLimit');

Route::get('role/liste', [PartenaireController::class, 'roleListe'])->name('roleListe');
Route::get('user/permissions', [PartenaireController::class, 'userPermissions'])->name('userPermissions');
Route::get('permissions', [PartenaireController::class, 'permissions'])->name('permissions');

Route::post('customer/credit/{program_id}', [PartenaireController::class, 'customerCredit'])->name('customer.credit');
Route::get('account/balance/{program_id}', [PartenaireController::class, 'accountBalance'])->name('account.balance');
Route::get('account/transactions/{program_id}', [PartenaireController::class, 'accountTransactions'])->name('account.transactions');
