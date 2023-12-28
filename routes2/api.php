<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PartenaireController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('initiation/bmo', [App\Http\Controllers\Api\ClientController::class, 'initiationBmo'])->name('initiationBmo');
Route::post('confirmation/bmo', [App\Http\Controllers\Api\ClientController::class, 'confirmationBmo'])->name('confirmationBmo');


Route::post('create/compte/client', [App\Http\Controllers\Api\ClientController::class, 'createCompteClient'])->name('createCompteClient');
Route::post('login/compte/client', [App\Http\Controllers\Api\ClientController::class, 'loginCompteClient'])->name('loginCompteClient');
Route::get('send/code/{id}', [App\Http\Controllers\Api\ClientController::class, 'sendCode'])->name('sendCode');
Route::get('send/code/telephone/{telephone}', [App\Http\Controllers\Api\ClientController::class, 'sendCodeTelephone'])->name('sendCodeTelephone');
Route::get('send/code/telephone/registration/{telephone}', [App\Http\Controllers\Api\ClientController::class, 'sendCodeTelephoneRegistration'])->name('sendCodeTelephoneRegistration');
Route::post('check/code/otp', [App\Http\Controllers\Api\ClientController::class, 'checkCodeOtp'])->name('checkCodeOtp');
Route::post('reset/password', [App\Http\Controllers\Api\ClientController::class, 'resetPassword'])->name('resetPassword');
Route::post('config/pin', [App\Http\Controllers\Api\ClientController::class, 'configPin'])->name('configPin');

Route::get('get/compte/client', [App\Http\Controllers\Api\ClientController::class, 'getCompteClient'])->name('getCompteClient');
Route::get('verification/phone/{user_id}', [App\Http\Controllers\Api\ClientController::class, 'verificationPhone'])->name('verificationPhone');
Route::post('verification/info/perso', [App\Http\Controllers\Api\ClientController::class, 'verificationInfoPerso'])->name('verificationInfoPerso');
Route::post('verification/info/piece', [App\Http\Controllers\Api\ClientController::class, 'verificationInfoPiece'])->name('verificationInfoPiece');
Route::post('save/file', [App\Http\Controllers\Api\ClientController::class, 'saveFile'])->name('saveFile');
Route::get('get/client/transactions/{id}', [App\Http\Controllers\Api\ClientController::class, 'getClientTransaction'])->name('getClientTransaction');
Route::get('get/client/pending/transactions/{id}', [App\Http\Controllers\Api\ClientController::class, 'getClientPendingTransaction'])->name('getPendingClientTransaction');
Route::get('get/client/pending/withdraws/{id}', [App\Http\Controllers\Api\ClientController::class, 'getClientPendingWithdraws'])->name('getClientPendingWithdraws');
Route::get('get/client/transactions/all/{id}', [App\Http\Controllers\Api\ClientController::class, 'getClientAllTransaction'])->name('getClientAllTransaction');

Route::post('add/depot/client', [App\Http\Controllers\Api\ClientController::class, 'addNewDepotClient'])->name('addDepotClient');
Route::post('complete/depot/client', [App\Http\Controllers\Api\ClientController::class, 'completeDepotClient'])->name('completeDepotClient');
Route::get('get/depots/client/{id}', [App\Http\Controllers\Api\ClientController::class, 'getDepotsClient'])->name('getDepotsClient');
Route::get('get/depot/detail/{id}', [App\Http\Controllers\Api\ClientController::class, 'getDepotDetailClient'])->name('getDepotDetailClient');
Route::get('get/recharge/detail/{id}', [App\Http\Controllers\Api\ClientController::class, 'getRechargeDetailClient'])->name('getRechargeDetailClient');

Route::post('add/self/retrait/client', [App\Http\Controllers\Api\ClientController::class, 'addNewSelfRetraitClient'])->name('addSelfRetraitClient');
Route::post('complete/self/retrait/client', [App\Http\Controllers\Api\ClientController::class, 'completeSelfRetraitClient'])->name('completeSelfRetraitClient');
Route::get('get/retraits/client/{id}', [App\Http\Controllers\Api\ClientController::class, 'getRetraitsClient'])->name('getRetraitsClient');
Route::get('get/retrait/detail/{id}', [App\Http\Controllers\Api\ClientController::class, 'getRetraitDetailClient'])->name('getRetraitDetailClient');
Route::get('get/self/retrait/detail/{id}', [App\Http\Controllers\Api\ClientController::class, 'getSelfRetraitDetailClient'])->name('getSelfRetraitDetailClient');
Route::post('validation/retrait/client', [App\Http\Controllers\Api\ClientController::class, 'validationRetraitAttenteClient'])->name('validationRetraitAttenteClient');
/*Pas coder encore*/Route::post('annulation/retrait/client', [App\Http\Controllers\Api\ClientController::class, 'annulationRetraitAttenteClient'])->name('annulationRetraitAttenteClient');

Route::post('add/transfert/client', [App\Http\Controllers\Api\ClientController::class, 'addNewTransfertClient'])->name('addTransfertClient');
Route::post('complete/transfert/client', [App\Http\Controllers\Api\ClientController::class, 'completeTransfertClient'])->name('completeTransfertClient');
Route::get('get/transferts/client/{id}', [App\Http\Controllers\Api\ClientController::class, 'getTransfertsClient'])->name('getTransfertsClient');
Route::get('get/transfert/out/detail/{id}', [App\Http\Controllers\Api\ClientController::class, 'getTransfertOutDetailClient'])->name('getTransfertOutDetailClient');
Route::get('get/transfert/in/detail/{id}', [App\Http\Controllers\Api\ClientController::class, 'getTransfertInDetailClient'])->name('getTransfertInDetailClient');
Route::post('validation/transfert/client/{id}', [App\Http\Controllers\Api\ClientController::class, 'validationTransfertAttenteClient'])->name('validationTransfertAttenteClient');
Route::post('annulation/transfert/client/{id}', [App\Http\Controllers\Api\ClientController::class, 'annulationTransfertAttenteClient'])->name('annulationTransfertAttenteClient');

Route::get('do/retraits', [App\Http\Controllers\Api\ClientController::class, 'retraits'])->name('retraits');

Route::get('liste/departement', [App\Http\Controllers\Api\ClientController::class, 'listeDepartement'])->name('listeDepartement');
Route::get('liste/pays', [App\Http\Controllers\Api\ClientController::class, 'listePays'])->name('listePays');
Route::get('get/virtuelle/price', [App\Http\Controllers\Api\ClientController::class, 'getVirtuellePrice'])->name('getVirtuellePrice');
Route::get('liste/gamme', [App\Http\Controllers\Api\ClientController::class, 'listeGamme'])->name('listeGamme');

Route::get('get/beneficiaries/{user_id}', [App\Http\Controllers\Api\ClientController::class, 'getBeneficiaries'])->name('getBeneficiaries');
Route::post('add/beneficiary/{user_id}', [App\Http\Controllers\Api\ClientController::class, 'addBeneficiary'])->name('addBeneficiary');
Route::get('delete/beneficiary/{id}', [App\Http\Controllers\Api\ClientController::class, 'deleteBeneficiary'])->name('deleteBeneficiary');
Route::post('edit/beneficiary/{id}', [App\Http\Controllers\Api\ClientController::class, 'editBeneficiary'])->name('editBeneficiary');
Route::post('add/contact/{beneficiary_id}', [App\Http\Controllers\Api\ClientController::class, 'addContact'])->name('addContact');
Route::post('edit/contact/{type}/{id}', [App\Http\Controllers\Api\ClientController::class, 'editContact'])->name('editContact');
Route::get('delete/contact/{type}/{id}', [App\Http\Controllers\Api\ClientController::class, 'deleteContact'])->name('deleteContact');
Route::get('check/client/{id}', [App\Http\Controllers\Api\ClientController::class, 'checkClient'])->name('checkClient');
Route::get('check/client/with/username/{username}', [App\Http\Controllers\Api\ClientController::class, 'checkClientUsername'])->name('checkClient');

/*Route::post('create/vente/virtuelle', [App\Http\Controllers\Api\VenteController::class, 'createVenteVirtuelle'])->name('createVenteVirtuelle');
Route::post('create/vente/physique', [App\Http\Controllers\Api\VenteController::class, 'createVentePhysique'])->name('createVentePhysique');
Route::post('stock/carte/physique', [App\Http\Controllers\Api\VenteController::class, 'stockCartePhysique'])->name('stockCartePhysique');*/


Route::post('login/otp/compte/client', [App\Http\Controllers\Api\ClientController::class, 'loginOtpCompteClient'])->name('loginOtpCompteClient');
Route::get('get/compte/client/infos', [App\Http\Controllers\Api\ClientController::class, 'getCompteClientInfo'])->name('getCompteClientInfo');

Route::post('token/valide', [App\Http\Controllers\Api\ClientController::class, 'tokenValide'])->name('tokenValide');

Route::get('change/card/status', [App\Http\Controllers\Api\ClientController::class, 'changeCardStatus'])->name('changeCardStatus');


/*Route::get('liste/depot/client/{id}', [App\Http\Controllers\Api\ClientController::class, 'listeDepotClient'])->name('listeDepotClient');
Route::get('liste/retrait/client/{id}', [App\Http\Controllers\Api\ClientController::class, 'listeRetraitClient'])->name('listeRetraitClient');
Route::get('liste/retrait/attente/client/{id}', [App\Http\Controllers\Api\ClientController::class, 'listeRetraitAttenteClient'])->name('listeRetraitAttenteClient');
Route::get('liste/operation/client/{id}', [App\Http\Controllers\Api\ClientController::class, 'listeOperationClient'])->name('listeOperationClient');
Route::get('liste/operation/periode/client/{id}', [App\Http\Controllers\Api\ClientController::class, 'listeOperationPeriodeClient'])->name('listeOperationPeriodeClient');
Route::get('card/infos', [App\Http\Controllers\Api\ClientController::class, 'cardInfos'])->name('cardInfos');
Route::get('statistiques/infos/{user_id}/{mois}', [App\Http\Controllers\Api\ClientController::class, 'statistiquesInfos'])->name('statistiquesInfos');*/

//Route::post('change/doubleauth/user', [App\Http\Controllers\Api\ClientController::class, 'changeDoubleauthUser'])->name('changeDoubleauthUser');
Route::post('change/info/user', [App\Http\Controllers\Api\ClientController::class, 'changeInfoUser'])->name('changeInfoUser');

Route::post('change/name/user', [App\Http\Controllers\Api\ClientController::class, 'changeNameUser'])->name('changeNameUser');
Route::post('change/lastname/user', [App\Http\Controllers\Api\ClientController::class, 'changeLastnameUser'])->name('changeLastnameUser');
Route::post('change/email/user', [App\Http\Controllers\Api\ClientController::class, 'changeEmailUser'])->name('changeEmailUser');
Route::post('change/telephone/user', [App\Http\Controllers\Api\ClientController::class, 'changeTelephoneUser'])->name('changeTelephoneUser');
Route::post('change/password/user', [App\Http\Controllers\Api\ClientController::class, 'changePasswordUser'])->name('changePasswordUser');
Route::post('change/pin/user', [App\Http\Controllers\Api\ClientController::class, 'changePinUser'])->name('changePinUser');
Route::post('change/sms/user', [App\Http\Controllers\Api\ClientController::class, 'changeSmsUser'])->name('changeSmsUser');
Route::post('change/double/user', [App\Http\Controllers\Api\ClientController::class, 'changeDoubleUser'])->name('changeDoubleUser');
Route::post('change/adresse/user', [App\Http\Controllers\Api\ClientController::class, 'changeAdresseUser'])->name('changeAdresseUser');

Route::post('buy/carte/{type}/{moyen}', [App\Http\Controllers\Api\ClientController::class, 'buyCarte'])->name('buyCarte');

Route::post('buy/card', [App\Http\Controllers\Api\ClientController::class, 'buyCard'])->name('buyCard');
Route::post('complete/buy/card/client', [App\Http\Controllers\Api\ClientController::class, 'completeBuyCard'])->name('completeBuyCard');


Route::post('liaison/carte', [App\Http\Controllers\Api\ClientController::class, 'liaisonCarte'])->name('liaisonCarte');
Route::get('get/user/cards/{id}', [App\Http\Controllers\Api\ClientController::class, 'getUserCards'])->name('getUserCards');
Route::get('search/client/update/{id}', [App\Http\Controllers\Api\ClientController::class, 'searchClientUpdate'])->name('searchClientUpdate');
Route::get('card-info/{id}', [App\Http\Controllers\Api\ClientController::class, 'getCardInfo'])->name('getCardInfo');
Route::get('card-info/account-info/{id}', [App\Http\Controllers\Api\ClientController::class, 'getAccountInfo'])->name('getAccountInfo');
Route::get('card-info/balance/{id}', [App\Http\Controllers\Api\ClientController::class, 'getBalance'])->name('getBalance');
Route::get('card-info/client-info/{id}', [App\Http\Controllers\Api\ClientController::class, 'getClientInfo'])->name('getClientInfo');
Route::get('get/dashboard/{id}', [App\Http\Controllers\Api\ClientController::class, 'getDashboard'])->name('getDashboard');
Route::get('get/solde/{id}', [App\Http\Controllers\Api\ClientController::class, 'getSolde'])->name('getSolde');
Route::get('kkiapay/infos', [App\Http\Controllers\Api\ClientController::class, 'getKkpInfos'])->name('getKkpInfos');
Route::get('cards/infos', [App\Http\Controllers\Api\ClientController::class, 'getCardsInfos'])->name('getCardsInfos');
Route::get('get/services', [App\Http\Controllers\Api\ClientController::class, 'getServices'])->name('getServices');
Route::get('get/mobile/wallets', [App\Http\Controllers\Api\ClientController::class, 'getMobileWallet'])->name('getMobileWallet');
Route::post('set/default/card', [App\Http\Controllers\Api\ClientController::class, 'setDefaultCard'])->name('setDefaultCard');


Route::get('get/vente/physique', [App\Http\Controllers\Api\VenteController::class, 'getVentePhysique'])->name('getVentePhysique');

Route::post('get/carte/information/route/{code}', [App\Http\Controllers\Api\ClientController::class, 'getCarteInformationRoute']);
Route::post('carte/transaction/{code}', [App\Http\Controllers\Api\ClientController::class, 'carteTransaction']);





//Partenaires


Route::post('login/partenaire', [PartenaireController::class, 'loginPartenaire'])->name('loginPartenaire');
Route::get('/get/bcv/client/info/{username}', [App\Http\Controllers\Api\ClientController::class, 'getCompteClientInfo'])->name('getCompteClientInfo');
Route::get('get/fees', [App\Http\Controllers\Api\ClientController::class, 'getFees'])->name('getFees');
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

Route::post('login/compte/validator', [App\Http\Controllers\Api\ValidatorController::class, 'loginCompteValdiator'])->name('loginCompteClient');
Route::get('get/pending/customers', [App\Http\Controllers\Api\ValidatorController::class, 'pendingCustomerAccount'])->name('pendingCustomerAccount');
Route::get('get/motif/list', [App\Http\Controllers\Api\ValidatorController::class, 'getMotifList'])->name('getMotifList');
Route::post('validate/pending/customers', [App\Http\Controllers\Api\ValidatorController::class, 'validatePendingCustomerAccount'])->name('validatePendingCustomerAccount');
Route::post('reject/pending/customers', [App\Http\Controllers\Api\ValidatorController::class, 'rejectPendingCustomerAccount'])->name('rejectPendingCustomerAccount');
