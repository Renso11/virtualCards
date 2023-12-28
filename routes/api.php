<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PartenaireController;
use App\Http\Controllers\Api\ValidatorController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Client\TransfertController;
use App\Http\Controllers\Api\Client\DepotController;
use App\Http\Controllers\Api\Client\BeneficiaryController;

Route::post('initiation/bmo', [ClientController::class, 'initiationBmo'])->name('initiationBmo');
Route::post('confirmation/bmo', [ClientController::class, 'confirmationBmo'])->name('confirmationBmo');


Route::post('add/depot/client', [DepotController::class, 'addNewDepotClient'])->name('addDepotClient');
Route::post('complete/depot/client', [DepotController::class, 'completeDepotClient'])->name('completeDepotClient');

Route::post('add/transfert/client', [TransfertController::class, 'addNewTransfertClient'])->name('addTransfertClient');
Route::post('complete/transfert/client', [TransfertController::class, 'completeTransfertClient'])->name('completeTransfertClient');

Route::get('get/beneficiaries/{user_id}', [BeneficiaryController::class, 'getBeneficiaries'])->name('getBeneficiaries');
Route::post('add/beneficiary/{user_id}', [BeneficiaryController::class, 'addBeneficiary'])->name('addBeneficiary');
Route::get('delete/beneficiary/{id}', [BeneficiaryController::class, 'deleteBeneficiary'])->name('deleteBeneficiary');
Route::post('edit/beneficiary/{id}', [BeneficiaryController::class, 'editBeneficiary'])->name('editBeneficiary');
Route::post('add/contact/{beneficiary_id}', [BeneficiaryController::class, 'addContact'])->name('addContact');
Route::post('edit/contact/{type}/{id}', [BeneficiaryController::class, 'editContact'])->name('editContact');
Route::get('delete/contact/{type}/{id}', [BeneficiaryController::class, 'deleteContact'])->name('deleteContact');

Route::post('create/compte/client', [ClientController::class, 'createCompteClient'])->name('createCompteClient');
Route::post('login/compte/client', [ClientController::class, 'loginCompteClient'])->name('loginCompteClient');
Route::get('send/code/{id}', [ClientController::class, 'sendCode'])->name('sendCode');
Route::get('send/code/telephone/{telephone}', [ClientController::class, 'sendCodeTelephone'])->name('sendCodeTelephone');
Route::get('send/code/telephone/registration/{telephone}', [ClientController::class, 'sendCodeTelephoneRegistration'])->name('sendCodeTelephoneRegistration');
Route::post('check/code/otp', [ClientController::class, 'checkCodeOtp'])->name('checkCodeOtp');
Route::post('reset/password', [ClientController::class, 'resetPassword'])->name('resetPassword');
Route::post('config/pin', [ClientController::class, 'configPin'])->name('configPin');

Route::get('/get/bcv/client/info/{username}', [ClientController::class, 'getCompteClientInfo'])->name('getCompteClientInfo');
Route::get('get/fees', [ClientController::class, 'getFees'])->name('getFees');

Route::get('get/compte/client', [ClientController::class, 'getCompteClient'])->name('getCompteClient');
Route::get('verification/phone/{user_id}', [ClientController::class, 'verificationPhone'])->name('verificationPhone');
Route::post('verification/info/perso', [ClientController::class, 'verificationInfoPerso'])->name('verificationInfoPerso');
Route::post('verification/info/piece', [ClientController::class, 'verificationInfoPiece'])->name('verificationInfoPiece');
Route::post('save/file', [ClientController::class, 'saveFile'])->name('saveFile');
Route::get('get/client/transactions/{id}', [ClientController::class, 'getClientTransaction'])->name('getClientTransaction');
Route::get('get/client/pending/transactions/{id}', [ClientController::class, 'getClientPendingTransaction'])->name('getPendingClientTransaction');
Route::get('get/client/pending/withdraws/{id}', [ClientController::class, 'getClientPendingWithdraws'])->name('getClientPendingWithdraws');
Route::get('get/client/transactions/all/{id}', [ClientController::class, 'getClientAllTransaction'])->name('getClientAllTransaction');


Route::post('validation/retrait/client', [ClientController::class, 'validationRetraitAttenteClient'])->name('validationRetraitAttenteClient');
Route::post('annulation/retrait/client', [ClientController::class, 'annulationRetraitAttenteClient'])->name('annulationRetraitAttenteClient');

Route::get('do/retraits', [ClientController::class, 'retraits'])->name('retraits');

Route::get('liste/departement', [ClientController::class, 'listeDepartement'])->name('listeDepartement');
Route::get('liste/pays', [ClientController::class, 'listePays'])->name('listePays');
Route::get('get/virtuelle/price', [ClientController::class, 'getVirtuellePrice'])->name('getVirtuellePrice');
Route::get('liste/gamme', [ClientController::class, 'listeGamme'])->name('listeGamme');

Route::get('check/client/{id}', [ClientController::class, 'checkClient'])->name('checkClient');
Route::get('check/client/with/username/{username}', [ClientController::class, 'checkClientUsername'])->name('checkClient');

Route::post('login/otp/compte/client', [ClientController::class, 'loginOtpCompteClient'])->name('loginOtpCompteClient');
Route::get('get/compte/client/infos', [ClientController::class, 'getCompteClientInfo'])->name('getCompteClientInfo');

Route::post('token/valide', [ClientController::class, 'tokenValide'])->name('tokenValide');

Route::get('change/card/status', [ClientController::class, 'changeCardStatus'])->name('changeCardStatus');
Route::post('change/info/user', [ClientController::class, 'changeInfoUser'])->name('changeInfoUser');
Route::post('change/password/user', [ClientController::class, 'changePasswordUser'])->name('changePasswordUser');

Route::post('buy/card', [ClientController::class, 'buyCard'])->name('buyCard');
Route::post('complete/buy/card/client', [ClientController::class, 'completeBuyCard'])->name('completeBuyCard');
Route::post('liaison/carte', [ClientController::class, 'liaisonCarte'])->name('liaisonCarte');
Route::get('get/user/cards/{id}', [ClientController::class, 'getUserCards'])->name('getUserCards');
Route::get('search/client/update/{id}', [ClientController::class, 'searchClientUpdate'])->name('searchClientUpdate');
Route::get('card-info/{id}', [ClientController::class, 'getCardInfo'])->name('getCardInfo');
Route::get('card-info/account-info/{id}', [ClientController::class, 'getAccountInfo'])->name('getAccountInfo');
Route::get('card-info/balance/{id}', [ClientController::class, 'getBalance'])->name('getBalance');
Route::get('card-info/client-info/{id}', [ClientController::class, 'getClientInfo'])->name('getClientInfo');
Route::get('get/dashboard/{id}', [ClientController::class, 'getDashboard'])->name('getDashboard');
Route::get('get/solde/{id}', [ClientController::class, 'getSolde'])->name('getSolde');
Route::get('kkiapay/infos', [ClientController::class, 'getKkpInfos'])->name('getKkpInfos');
Route::get('cards/infos', [ClientController::class, 'getCardsInfos'])->name('getCardsInfos');
Route::get('get/services', [ClientController::class, 'getServices'])->name('getServices');
Route::get('get/mobile/wallets', [ClientController::class, 'getMobileWallet'])->name('getMobileWallet');
Route::post('set/default/card', [ClientController::class, 'setDefaultCard'])->name('setDefaultCard');




//Partenaires


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


// Utilitaire de validation

Route::post('login/compte/validator', [ValidatorController::class, 'loginCompteValdiator'])->name('loginCompteClient');
Route::get('get/pending/customers', [ValidatorController::class, 'pendingCustomerAccount'])->name('pendingCustomerAccount');
Route::get('get/motif/list', [ValidatorController::class, 'getMotifList'])->name('getMotifList');
Route::post('validate/pending/customers', [ValidatorController::class, 'validatePendingCustomerAccount'])->name('validatePendingCustomerAccount');
Route::post('reject/pending/customers', [ValidatorController::class, 'rejectPendingCustomerAccount'])->name('rejectPendingCustomerAccount');
