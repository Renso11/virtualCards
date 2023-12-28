<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\HomeController::class, 'welcome'])->name('welcome')->middleware('auth');
Route::post('/search/data', [App\Http\Controllers\HomeController::class, 'searchData'])->name('search.data')->middleware('auth');


Route::post('/search/client', [App\Http\Controllers\ClientController::class, 'searchClient'])->name('search.client')->middleware('auth');

Route::get('/clients', [App\Http\Controllers\ClientController::class, 'clients'])->name('clients')->middleware('auth');
Route::get('/clients/attentes', [App\Http\Controllers\ClientController::class, 'clientsAttentes'])->name('clients.attentes')->middleware('auth');

Route::post('/client/add', [App\Http\Controllers\ClientController::class, 'clientAdd'])->name('client.add')->middleware('auth');
Route::post('/client/edit/{id}', [App\Http\Controllers\ClientController::class, 'clientEdit'])->name('client.edit')->middleware('auth');
Route::post('/client/delete/{id}', [App\Http\Controllers\ClientController::class, 'clientDelete'])->name('client.delete')->middleware('auth');
Route::post('/client/reset/password/{id}', [App\Http\Controllers\ClientController::class, 'clientResetPassword'])->name('client.reset.password')->middleware('auth');
Route::post('/client/activation/{id}', [App\Http\Controllers\ClientController::class, 'clientActivation'])->name('client.activation')->middleware('auth');
Route::post('/client/desactivation/{id}', [App\Http\Controllers\ClientController::class, 'clientDesactivation'])->name('client.desactivation')->middleware('auth');
Route::post('/client/validation/{id}', [App\Http\Controllers\ClientController::class, 'clientValidation'])->name('client.validation')->middleware('auth');
Route::post('/client/rejet/{id}', [App\Http\Controllers\ClientController::class, 'clientRejet'])->name('client.rejet')->middleware('auth');
Route::get('/client/details/{id}', [App\Http\Controllers\ClientController::class, 'clientDetails'])->name('client.details')->middleware('auth');
Route::post('/kyc/edit/{id}', [App\Http\Controllers\ClientController::class, 'kycEdit'])->name('kyc.edit')->middleware('auth');


Route::get('/carte/perso', [App\Http\Controllers\ClientController::class, 'cartePerso'])->name('carte.perso')->middleware('auth');
Route::post('/add/carte/perso/unique', [App\Http\Controllers\ClientController::class, 'addCartePersoUnique'])->name('add.carte.perso.unique')->middleware('auth');
Route::post('/add/carte/perso/multi', [App\Http\Controllers\ClientController::class, 'addCartePersoMulti'])->name('add.carte.perso.multi')->middleware('auth');

// Tout ce qui concerne le partenaire

Route::get('/partenaires', [App\Http\Controllers\PartenaireController::class, 'partenaires'])->name('partenaires')->middleware('auth');
Route::post('/partenaire/add', [App\Http\Controllers\PartenaireController::class, 'partenaireAdd'])->name('partenaire.add')->middleware('auth');
Route::post('/partenaire/edit/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireEdit'])->name('partenaire.edit')->middleware('auth');
Route::post('/partenaire/delete/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireDelete'])->name('partenaire.delete')->middleware('auth');
Route::get('/partenaire/details/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireDetails'])->name('partenaire.details')->middleware('auth');
Route::post('/partenaire/cancel/retrait/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireCancelRetrait'])->name('partenaire.cancel.retrait')->middleware('auth');
Route::post('/partenaire/cancel/depot/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireCancelDepot'])->name('partenaire.cancel.depot')->middleware('auth');
Route::get('/partenaire/compte/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireCompte'])->name('partenaire.compte')->middleware('auth');

Route::post('/partenaire/user/edit/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireUserEdit'])->name('partenaire.user.edit')->middleware('auth');
Route::post('/partenaire/user/delete/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireUserDelete'])->name('partenaire.user.delete')->middleware('auth');
Route::post('/partenaire/user/activation/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireUserActivation'])->name('partenaire.user.activation')->middleware('auth');
Route::post('/partenaire/user/desactivation/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireUserDesactivation'])->name('partenaire.user.desactivation')->middleware('auth');
Route::get('/partenaire/user/details/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireUserDetails'])->name('partenaire.user.details')->middleware('auth');
Route::post('/partenaire/user/reset/password/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireUserResetPassword'])->name('partenaire.user.reset.password')->middleware('auth');

Route::post('/partenaire/vente/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireVente'])->name('partenaire.vente')->middleware('auth');
Route::get('/partenaire/ventes/attentes', [App\Http\Controllers\PartenaireController::class, 'partenaireVenteAttentes'])->name('partenaire.vente.attentes')->middleware('auth');
Route::post('/partenaire/valide/vente/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireValideVente'])->name('partenaire.valide.vente')->middleware('auth');

Route::post('/partenaire/recharge/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireRecharge'])->name('partenaire.recharge')->middleware('auth');
Route::get('/partenaire/recharges/attentes', [App\Http\Controllers\PartenaireController::class, 'partenaireRechargeAttentes'])->name('partenaire.recharge.attentes')->middleware('auth');
Route::post('/partenaire/valide/recharge/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireValideRecharge'])->name('partenaire.valide.recharge')->middleware('auth');

Route::get('/partenaires/api', [App\Http\Controllers\PartenaireController::class, 'partenairesApi'])->name('partenaires.api')->middleware('auth');
Route::post('/partenaires/api/add', [App\Http\Controllers\PartenaireController::class, 'partenaireApiAdd'])->name('partenaire.api.add')->middleware('auth');
Route::post('/partenaires/api/recharge/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireApiRecharge'])->name('partenaire.api.recharge')->middleware('auth');
Route::post('/partenaires/api/edit/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireApiEdit'])->name('partenaire.api.edit')->middleware('auth');
Route::post('/partenaires/api/delete/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireApiDelete'])->name('partenaire.api.delete')->middleware('auth');
Route::get('/partenaires/api/details/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireApiDetails'])->name('partenaire.api.details')->middleware('auth');

Route::get('/partenaires/api/recharge/attentes', [App\Http\Controllers\PartenaireController::class, 'partenaireApiRechargeAttente'])->name('partenaire.api.recharge.attente')->middleware('auth');
Route::post('/partenaires/api/recharge/validate/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireApiRechargeValidate'])->name('partenaire.api.recharge.validate')->middleware('auth');
Route::post('/partenaires/api/recharge/unvalidate/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireApiRechargeUnvalidate'])->name('partenaire.api.recharge.unvalidate')->middleware('auth');

Route::get('/partenaires/api/fees', [App\Http\Controllers\PartenaireController::class, 'partenairesApiFee'])->name('partenaires.api.fee')->middleware('auth');
Route::post('/partenaires/api/fees/add', [App\Http\Controllers\PartenaireController::class, 'partenaireApiFeeAdd'])->name('partenaire.api.fee.add')->middleware('auth');
Route::post('/partenaires/api/fees/edit/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireApiFeeEdit'])->name('partenaire.api.fee.edit')->middleware('auth');
Route::post('/partenaires/api/fees/delete/{id}', [App\Http\Controllers\PartenaireController::class, 'partenaireApiFeeDelete'])->name('partenaire.api.fee.delete')->middleware('auth');

Route::get('/partenaires/api/transactions', [App\Http\Controllers\PartenaireController::class, 'partenairesApiTransactions'])->name('partenaires.api.transactions')->middleware('auth');
Route::post('/partenaires/api/filter/transactions', [App\Http\Controllers\PartenaireController::class, 'partenairesApiFilterTransactions'])->name('partenaires.api.filter.transactions')->middleware('auth');




Route::get('/users', [App\Http\Controllers\UserController::class, 'users'])->name('users')->middleware('auth');
Route::post('/user/add', [App\Http\Controllers\UserController::class, 'userAdd'])->name('user.add')->middleware('auth');
Route::post('/user/edit/{id}', [App\Http\Controllers\UserController::class, 'userEdit'])->name('user.edit')->middleware('auth');
Route::post('/user/delete/{id}', [App\Http\Controllers\UserController::class, 'userDelete'])->name('user.delete')->middleware('auth');
Route::post('/user/activation/{id}', [App\Http\Controllers\UserController::class, 'userActivation'])->name('user.activation')->middleware('auth');
Route::post('/user/desactivation/{id}', [App\Http\Controllers\UserController::class, 'userDesactivation'])->name('user.desactivation')->middleware('auth');
Route::get('/user/details/{id}', [App\Http\Controllers\UserController::class, 'userDetails'])->name('user.details')->middleware('auth');
Route::post('/user/reset/password/{id}', [App\Http\Controllers\UserController::class, 'userResetPassword'])->name('user.reset.password')->middleware('auth');


Route::get('/gammes', [App\Http\Controllers\ParametreController::class, 'gammes'])->name('gammes')->middleware('auth');
Route::post('/gamme/add', [App\Http\Controllers\ParametreController::class, 'gammeAdd'])->name('gammes.add')->middleware('auth');
Route::post('/gamme/edit/{id}', [App\Http\Controllers\ParametreController::class, 'gammeEdit'])->name('gamme.edit')->middleware('auth');
Route::post('/gamme/delete/{id}', [App\Http\Controllers\ParametreController::class, 'gammeDelete'])->name('gamme.delete')->middleware('auth');
Route::post('/gamme/activation/{id}', [App\Http\Controllers\ParametreController::class, 'gammeActivation'])->name('gamme.activation')->middleware('auth');
Route::post('/gamme/desactivation/{id}', [App\Http\Controllers\ParametreController::class, 'gammeDesactivation'])->name('gamme.desactivation')->middleware('auth');


Route::get('/frais', [App\Http\Controllers\ParametreController::class, 'frais'])->name('frais')->middleware('auth');
Route::post('/frais/add', [App\Http\Controllers\ParametreController::class, 'fraisAdd'])->name('frais.add')->middleware('auth');
Route::post('/frais/edit/{id}', [App\Http\Controllers\ParametreController::class, 'fraisEdit'])->name('frais.edit')->middleware('auth');
Route::post('/frais/delete/{id}', [App\Http\Controllers\ParametreController::class, 'fraisDelete'])->name('frais.delete')->middleware('auth');

Route::get('/params/generales', [App\Http\Controllers\ParametreController::class, 'generales'])->name('generales')->middleware('auth');
Route::post('/card/infos/update', [App\Http\Controllers\ParametreController::class, 'cardInfosUpdate'])->name('card.infos.update')->middleware('auth');

Route::get('/commissions', [App\Http\Controllers\ParametreController::class, 'commissions'])->name('commissions')->middleware('auth');
Route::post('/commissions/add', [App\Http\Controllers\ParametreController::class, 'commissionsAdd'])->name('commissions.add')->middleware('auth');
Route::post('/commissions/edit/{id}', [App\Http\Controllers\ParametreController::class, 'commissionsEdit'])->name('commissions.edit')->middleware('auth');
Route::post('/commissions/delete/{id}', [App\Http\Controllers\ParametreController::class, 'commissionsDelete'])->name('commissions.delete')->middleware('auth');


Route::get('/commissions/elg', [App\Http\Controllers\CommissionController::class, 'commissionsElg'])->name('commissions.elg')->middleware('auth');
Route::get('/commissions/uba', [App\Http\Controllers\CommissionController::class, 'commissionsUba'])->name('commissions.uba')->middleware('auth');
Route::get('/commissions/partenaire', [App\Http\Controllers\CommissionController::class, 'commissionsPartenaire'])->name('commissions.partenaire')->middleware('auth');

Route::get('/rechargements/clients', [App\Http\Controllers\RechargementController::class, 'rechargementClients'])->name('rechargements.client')->middleware('auth');


Route::get('/restrictions', [App\Http\Controllers\ParametreController::class, 'restrictions'])->name('restrictions')->middleware('auth');
Route::post('/restrictions/add', [App\Http\Controllers\ParametreController::class, 'restrictionsAdd'])->name('restrictions.add')->middleware('auth');
Route::post('/restrictions/edit/{id}', [App\Http\Controllers\ParametreController::class, 'restrictionsEdit'])->name('restrictions.edit')->middleware('auth');
Route::post('/restrictions/delete/{id}', [App\Http\Controllers\ParametreController::class, 'restrictionsDelete'])->name('restrictions.delete')->middleware('auth');
Route::post('/restrictions/activate/{id}', [App\Http\Controllers\ParametreController::class, 'restrictionsActivate'])->name('restrictions.activate')->middleware('auth');
Route::post('/restrictions/desactivate/{id}', [App\Http\Controllers\ParametreController::class, 'restrictionsDesactivate'])->name('restrictions.desactivate')->middleware('auth');


Route::get('/roles', [App\Http\Controllers\ParametreController::class, 'roles'])->name('roles')->middleware('auth');
Route::post('/roles/add', [App\Http\Controllers\ParametreController::class, 'rolesAdd'])->name('roles.add')->middleware('auth');
Route::post('/roles/edit/{id}', [App\Http\Controllers\ParametreController::class, 'rolesEdit'])->name('roles.edit')->middleware('auth');
Route::post('/roles/delete/{id}', [App\Http\Controllers\ParametreController::class, 'rolesDelete'])->name('roles.delete')->middleware('auth');


Route::get('/cartes/physiques', [App\Http\Controllers\CartePhysiqueController::class, 'cartePhysiques'])->name('carte.physiques')->middleware('auth');
Route::post('/cartes/physiques/add', [App\Http\Controllers\CartePhysiqueController::class, 'cartePhysiquesAdd'])->name('carte.physiques.add')->middleware('auth');

/*Route::get('/ventes/physiques/attentes', [App\Http\Controllers\CartePhysiqueController::class, 'ventePhysiquesAttentes'])->name('vente.physiques.attentes')->middleware('auth');
Route::post('/ventes/physiques/delete/{id}', [App\Http\Controllers\CartePhysiqueController::class, 'ventePhysiquesDelete'])->name('vente.physiques.delete')->middleware('auth');
Route::post('/ventes/physiques/attentes/validation/{id}', [App\Http\Controllers\CartePhysiqueController::class, 'ventePhysiquesAttentesValidation'])->name('vente.physiques.attentes.validation')->middleware('auth');
Route::post('/ventes/physiques/attentes/rejet/{id}', [App\Http\Controllers\CartePhysiqueController::class, 'ventePhysiquesAttentesRejet'])->name('vente.physiques.attentes.rejet')->middleware('auth');
Route::get('/ventes/physiques/finalises', [App\Http\Controllers\CartePhysiqueController::class, 'ventePhysiquesFinalises'])->name('vente.physiques.finalises')->middleware('auth');
Route::get('/ventes/physiques/rejetes', [App\Http\Controllers\CartePhysiqueController::class, 'ventePhysiquesRejetes'])->name('vente.physiques.rejetes')->middleware('auth');

Route::get('/ventes/virtuelles/attentes', [App\Http\Controllers\CarteVirtuelleController::class, 'venteVirtuellesAttentes'])->name('vente.virtuelles.attentes')->middleware('auth');
Route::post('/ventes/virtuelles/delete/{id}', [App\Http\Controllers\CarteVirtuelleController::class, 'venteVirtuellesDelete'])->name('vente.virtuelles.delete')->middleware('auth');
Route::post('/ventes/virtuelles/attentes/validation/{id}', [App\Http\Controllers\CarteVirtuelleController::class, 'venteVirtuellesAttentesValidation'])->name('vente.virtuelles.attentes.validation')->middleware('auth');
Route::post('/ventes/virtuelles/attentes/rejet/{id}', [App\Http\Controllers\CarteVirtuelleController::class, 'ventVirtuellessAttentesRejet'])->name('vente.virtuelles.attentes.rejet')->middleware('auth');
Route::get('/ventes/virtuelles/finalises', [App\Http\Controllers\CarteVirtuelleController::class, 'venteVirtuellesFinalises'])->name('vente.virtuelles.finalises')->middleware('auth');
Route::get('/ventes/virtuelles/rejetes', [App\Http\Controllers\CarteVirtuelleController::class, 'venteVirtuellesRejetes'])->name('vente.virtuelles.rejetes')->middleware('auth');

Route::post('/rechargements/delete/{id}', [App\Http\Controllers\RechargementController::class, 'rechargementAttentesDelete'])->name('rechargement.attentes.delete')->middleware('auth');
Route::post('/rechargements/attentes/validation/{id}', [App\Http\Controllers\RechargementController::class, 'rechargementAttentesValidation'])->name('rechargement.attentes.validation')->middleware('auth');
Route::post('/rechargements/attentes/rejet/{id}', [App\Http\Controllers\RechargementController::class, 'rechargementAttentesRejet'])->name('rechargement.attentes.rejet')->middleware('auth');
Route::get('/rechargements/finalises', [App\Http\Controllers\RechargementController::class, 'rechargementFinalises'])->name('rechargement.finalises')->middleware('auth');
Route::get('/rechargements/rejetes', [App\Http\Controllers\RechargementController::class, 'rechargementRejetes'])->name('rechargement.rejetes')->middleware('auth');*/

Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile')->middleware('auth');
Route::post('/profile/informations/edit', [App\Http\Controllers\UserController::class, 'profileInformationsEdit'])->name('profile.informations.edit')->middleware('auth');
Route::post('/profile/password/change', [App\Http\Controllers\UserController::class, 'profilePasswordChange'])->name('profile.password.change')->middleware('auth');

Route::get('/rapport/depots', [App\Http\Controllers\RapportController::class, 'rapportDepots'])->name('rapport.depots')->middleware('auth');
Route::post('/search/depots', [App\Http\Controllers\RapportController::class, 'searchDepots'])->name('search.depots')->middleware('auth');
Route::get('/download/rapport/depots', [App\Http\Controllers\RapportController::class, 'downloadRapportDepots'])->name('download.rapport.depots')->middleware('auth');

Route::get('/rapport/retraits', [App\Http\Controllers\RapportController::class, 'rapportRetraits'])->name('rapport.retraits')->middleware('auth');
Route::post('/search/retraits', [App\Http\Controllers\RapportController::class, 'searchRetraits'])->name('search.retraits')->middleware('auth');
Route::get('/download/rapport/retraits', [App\Http\Controllers\RapportController::class, 'downloadRapportRetraits'])->name('download.rapport.retraits')->middleware('auth');

Route::get('/rapport/transferts', [App\Http\Controllers\RapportController::class, 'rapportTransferts'])->name('rapport.transferts')->middleware('auth');
Route::post('/search/transferts', [App\Http\Controllers\RapportController::class, 'searchTransferts'])->name('search.transferts')->middleware('auth');
Route::post('/download/rapport/transferts', [App\Http\Controllers\RapportController::class, 'downloadRapportTransferts'])->name('download.rapport.transferts')->middleware('auth');

Route::get('/modules/clients', [App\Http\Controllers\AppController::class, 'modulesClients'])->name('modules.clients')->middleware('auth');
Route::post('/modules/clients/add', [App\Http\Controllers\AppController::class, 'modulesClientsAdd'])->name('modules.clients.add')->middleware('auth');
Route::post('/modules/clients/delete/{id}', [App\Http\Controllers\AppController::class, 'modulesClientsDelete'])->name('modules.clients.delete')->middleware('auth');
Route::post('/modules/clients/activate/{id}', [App\Http\Controllers\AppController::class, 'modulesClientsActivate'])->name('modules.clients.activate')->middleware('auth');
Route::post('/modules/clients/desactivate/{id}', [App\Http\Controllers\AppController::class, 'modulesClientsDesactivate'])->name('modules.clients.desactivate')->middleware('auth');    


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/test', [App\Http\Controllers\HomeController::class, 'test'])->name('test');