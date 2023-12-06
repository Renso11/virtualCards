<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Retrait;
use App\Models\Depot;
use App\Models\Seuil;
use App\Models\Limit;
use App\Models\Service;
use App\Models\Role;
use App\Models\Frai;
use App\Models\Partenaire;
use App\Models\UserClient;
use App\Models\Permission;
use App\Models\UserPartenaire;
use App\Models\MouchardPartenaire;
use App\Models\GtpRequest;
use GuzzleHttp\Client;
use App\Models\AccountCommission;
use App\Models\AccountCommissionOperation;
use App\Models\AccountDistribution;
use App\Models\AccountDistributionOperation;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Mail\CodeValidationRetrait;
use App\Models\Commission;
use App\Mail\MailAlerte;
use App\Mail\MailAlerteVerification;
use App\Models\ApiPartenaireAccount;
use App\Models\ApiPartenaireFee;
use App\Models\ApiPartenaireTransaction;
use DB;
use Illuminate\Support\Facades\Auth as Auth;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class PartenaireController extends Controller
{

    public function __construct() {
        //$this->middleware('auth:apiPartenaire', ['except' => ['loginPartenaire','addRetraitPartenaire','userPermissions','permissions']]);
    }

    public function getServices(Request $request){
        try {            
            $modules = Service::where('deleted',0)->where('type','partenaire')->get();
            return $this->sendResponse($modules, 'Modules.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getFees(Request $request){
        try {            
            $fees = Frai::where('deleted',0)->get();
            return $this->sendResponse($fees, 'fees.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function loginPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }

            if (! $token = Auth::guard('apiPartenaire')->attempt($validator->validated())) {
                return  $this->sendError('Identifiants incorrectes', [],401);
            }
            
            $user = auth('apiPartenaire')->user();
            
            if($user->status == 0){
                return $this->sendError('Ce compte est désactivé. Veuillez contactez le service clientèle', [], 401);
            }
            $resultat['token'] = $this->createNewToken($token);
            $user->lastconnexion = date('d-M-Y H:i:s');
            $user->partenaire;
            $user->rolePartenaire->rolePartenairePermissions;
            $user->save();
            $user->makeHidden(['password']);
            $modules = Service::where('deleted',0)->where('type','partenaire')->get();
            $resultat['user'] = $user;
            $resultat['services'] = $modules;

            return $this->sendResponse($resultat, 'Connexion réussie');
            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function getDashboardPartenaire(Request $request){
        try {            
            $partenaire = Partenaire::where('id',$request->id)->first();
            $distribution = $partenaire->accountDistribution;
            $commission = $partenaire->accountCommission;   
            
            $data['distribution'] = $distribution;
            $data['commission'] = $commission;
            return $this->sendResponse($data, 'Dashboard.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getComptePartenaireInfo(Request $request){
        try {            
            $user = UserPartenaire::where('id',$request->id)->where('deleted',0)->first();
            $resultat = [];
            if($user){
                if($user->status == 0){
                    return $this->sendError('Ce compte est désactivé.', [], 500);
                }
                $transactions = DB::select(DB::raw("SELECT libelle , montant , typeOperation , dateOperation , user , sens
                FROM
                (
                    select libelle , montant , 'depot' as typeOperation , created_at as dateOperation , user_client_id as user, 'depot' as sens
                    From depots
                    Where partenaire_id = $user->partenaire_id
                    and status = 1
                    and validate = 1
                Union
                    select libelle , montant , 'retrait' as typeOperation , created_at as dateOperation , user_client_id as user, 'retrait' as sens
                    From retraits
                    Where partenaire_id = $user->partenaire_id
                    and status = 1
                    and validate = 1
                ) 
                transactions order by dateOperation desc"));

                $trans = [];
                foreach ($transactions as $key => $value) {
                    $value->id = $key + 1;
                    $trans[] = $value;
                }
                $resultat['utilisateur'] = $user;
                $resultat['transactions'] = $transactions;
                return $this->sendResponse($resultat, 'Informations récupérées avec succes.');
            }else{
                return $this->sendError('Cet utilisateur n\'exite pas dans la base', [], 500);
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function retraitAttentes(Request $request){
        try{
            $retraits = Retrait::where('partenaire_id',$request->id)->where('deleted',0)->where('status',0)->where('validate',1)->get()->all();
            foreach ($retraits as $key => $value) {
                $value['user'] = $value->userClient;
            }
            return $this->sendResponse($retraits, 'Informations récupérées avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function annuleRetraitAttentes(Request $request){
        try{
            $retrait = Retrait::where('id',$request->id)->first();
            $retrait->deleted = 1;
            $retrait->save();
            return $this->sendResponse($retrait, 'Retrait annulé avec succès.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function searchClientInfo(Request $request){
        try {        
            $request->validate([
                'username' => 'required'
            ]);

            $user = UserClient::where('username',$request->username)->where('deleted',0)->first();

            return $this->sendResponse($user, 'Informations récupérées avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addRetraitPartenaire(Request $request){
        try {
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');
            
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'montant' => 'required',
                'user_partenaire_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }

            $code = rand(1000,9999);
            $user = UserClient::where('username',$request->username)->first();
            $userPartenaire = UserPartenaire::where('id',$request->user_partenaire_id)->first();

            if(!$user){
                return $this->sendError('Le client n\'exite pas. Verifier le numero de telephone et recommencer');
            }
            
            //verification solde client            
                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $client = new Client();
                $url = $base_url."accounts/".$user->code."/balance";
        
                $headers = [
                    'programId' => $programID,
                    'requestId' => $requestId->id
                ];
        
                $auth = [
                    $authLogin,
                    $authPass
                ];
            
                try {
                    $response = $client->request('GET', $url, [
                        'auth' => $auth,
                        'headers' => $headers,
                    ]);
            
                    $balance = json_decode($response->getBody());
                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return  $this->sendError($error);
                }  
            
            // Determination frais
                $montant = $request->montant;
                $frais = Frai::where('start','<=',$montant)->where('end','>=',$montant)->where('type_operation','retrait')->orderBy('id','DESC')->first();
                
                $frai = 0;
                if($frais){
                    if($frais->type == 'Taux pourcentage'){
                        $frai = $montant * $frais->value / 100;
                    }else{
                        $frai = $frais->value;
                    }
                }
                
                if($balance->balance < ($montant + $frai)){
                    return $this->sendError('Le solde du client ne suffit pas pour cet opération');
                }

            //traitement des restrictions
                $isRestrictByAdmin = isRestrictByAdmin($montant,$user->id,$userPartenaire->partenaire->id,'retrait');

                if($isRestrictByAdmin != 'ok'){
                    return $this->sendError($isRestrictByAdmin);
                }

                $isRestrictByPartenaire = isRestrictByPartenaire($montant,$userPartenaire->partenaire->id,$userPartenaire->id,'retrait');

                if($isRestrictByPartenaire != 'ok'){
                    return $this->sendError($isRestrictByPartenaire);
                }
            
            /*initiation du retrait sans validation
                $limitByPartenaire = limitByPartenaire('retrait',$montant,$userPartenaire->partenaire->id,$userPartenaire->id);
            
                if($limitByPartenaire != 'ok'){
                    $retrait = Retrait::create([
                        'id' => Uuid::uuid4()->toString(),
                        'user_client_id' => $user->id,
                        'partenaire_id' => $userPartenaire->partenaire->id,
                        'user_partenaire_id' => $userPartenaire->id,
                        'libelle' => 'Retrait effectué chez '.$userPartenaire->partenaire->libelle,
                        'montant' => $montant,
                        'frais' => $frai,
                        'code' => $code,
                        'status' => 0,
                        'validate' => 0,
                        'deleted' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);

                    MouchardPartenaire::create([
                        'id' => Uuid::uuid4()->toString(),
                        'libelle' => 'Retrait de '. $montant.' effectué sur le compte '. $user->username . ' de '.$user->lastname.' '.$user->name.' avec des frais de: '.$frai,
                        'user_partenaire_id' => $userPartenaire->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    return $this->sendResponse($retrait, 'Retrait initié avec succes. Demandez la confirmation d\'un superieur pour finaliser l\'opération');
                }*/

            // Initiation du retrait avec validation 
                $retrait = Retrait::create([
                        'id' => Uuid::uuid4()->toString(),
                    'user_client_id' => $user->id,
                    'partenaire_id' => $userPartenaire->partenaire->id,
                    'user_partenaire_id' => $userPartenaire->id,
                    'libelle' => 'Retrait effectué chez '.$userPartenaire->partenaire->libelle,
                    'montant' => $montant,
                    'frais' => $frai,
                    'code' => $code,
                    'status' => 0,
                    'validate' => 1,
                    'deleted' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

                MouchardPartenaire::create([
                        'id' => Uuid::uuid4()->toString(),
                    'libelle' => 'Retrait de '. $request->montant.' effectué sur le compte '. $user->username . ' de '.$user->lastname.' '.$user->name.'.',
                    'user_partenaire_id' => $userPartenaire->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                
                $data['code'] = $code;
                if($user->sms == 1){
                    $message = "Le code de confirmation de votre dernier retrait est : ".$code;
                    $this->sendSms($user->username,$message);
                }else{
                    $data['name'] = $user->lastname.' '.$user->name;;
                    Mail::to([$user->kycClient->email,])->send(new CodeValidationRetrait($data));
                }
            
                return $this->sendResponse($retrait,'Retrait initié avec succes. Le retrait doit maintenant valider l\'opération', 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addDepotPartenaire(Request $request){
        $base_url = env('BASE_GTP_API');
        $programID = env('PROGRAM_ID');
        $authLogin = env('AUTH_LOGIN');
        $authPass = env('AUTH_PASS');
        
        $validator = Validator::make($request->all(), [
            'telephone' => 'required',
            'montant' => 'required',
            'user_partenaire_id' => 'required'
        ]);
        if ($validator->fails()) {
            return  $this->sendError($validator->errors(), [],422);
        }
        
        $userPartenaire = UserPartenaire::where('id',$request->user_partenaire_id)->first();
        try{
            $distribution_account = AccountDistribution::where('partenaire_id',$userPartenaire->partenaire->id)->where('deleted',0)->first();

            $montant = $request->montant;

            if($distribution_account->solde < $montant){
                return $this->sendError('Votre solde ne suffit pas pour cet opération');
            }

            $userClient = UserClient::where('telephone',$request->telephone)->where('deleted',0)->first();
            $partenaire = Partenaire::where('id',$userPartenaire->partenaire_id)->where('deleted',0)->first();

            if(!$userClient){
                return $this->sendError('L\'utilisateur n\'exite pas. Vérifier le numero de téléphone');
            }

            if(!$partenaire){
                return $this->sendError('Partenaire introuvable');
            }

            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');


            // Determination frais
                $frais = Frai::where('start','<=',$montant)->where('end','>=',$montant)->where('deleted',0)->where('type_operation','depot')->orderBy('id','DESC')->first();
                
                $frai = 0;
                if($frais){
                    if($frais->type == 'Taux pourcentage'){
                        $frai = $montant * $frais->value / 100;
                    }else{
                        $frai = $frais->value;
                    }
                }
                
                $montantReel = $montant - $frai;

            //recuperation solde avant et apres
                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $client = new Client();
                $url = $base_url."accounts/".$userClient->code."/balance";
        
                $headers = [
                    'programId' => $programID,
                    'requestId' => $requestId->id
                ];
        
                $auth = [
                    $authLogin,
                    $authPass
                ];
            
                try {
                    $response = $client->request('GET', $url, [
                        'auth' => $auth,
                        'headers' => $headers,
                    ]);
            
                    $balance = json_decode($response->getBody());
                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return $this->sendError($error, [], 500);
                }
                
                $soldeAv = $balance->balance;
                $soldeAp = $soldeAv + $montantReel;

            // traitement des restrictions
                $isRestrictByAdmin = isRestrictByAdmin($montant,$userClient->id,$partenaire->id,'depot');

                if($isRestrictByAdmin != 'ok'){
                    return $this->sendError($isRestrictByAdmin);
                }

                $isRestrictByPartenaire = isRestrictByPartenaire($montant,$partenaire->id,$userPartenaire->id,'depot');

                if($isRestrictByPartenaire != 'ok'){
                    return $this->sendError($isRestrictByPartenaire);
                }

                /*$limitByPartenaire = limitByPartenaire('depot',$montant,$partenaire->id,$userPartenaire->id);

                if($limitByPartenaire != 'ok'){
                    $depot = Depot::create([
                        'id' => Uuid::uuid4()->toString(),
                        'user_client_id' => $userClient->id,
                        'partenaire_id' => $partenaire->id,
                        'user_partenaire_id' => $userPartenaire->id,
                        'libelle' => 'Depot de '.$montantReel.' effectué chez '.$partenaire->libelle,
                        'montant' => $montantReel,
                        'status' => 1,
                        'frais' => $frai,
                        'deleted' => 0,
                        'validate' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    return $this->sendResponse($depot,'Depot initié avec succes. Demandez la confirmation d\'un superieur pour finaliser l\'opération');
                }*/

            // Realisation du depots
                $client = new Client();
                $url = $base_url."accounts/".$userClient->code."/transactions";
                
                $body = [
                    "transferType" => "WalletToCard",
                    "transferAmount" => $montantReel,
                    "currencyCode" => "XOF",
                    "referenceMemo" => "Depot de ".$montantReel." XOF sur votre carte avec des frais de: ".$frai,
                    "last4Digits" => $userClient->last
                ];

                $body = json_encode($body);
                
                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                
                $headers = [
                    'programId' => $programID,
                    'requestId' => $requestId->id,
                    'Content-Type' => 'application/json', 'Accept' => 'application/json'
                ];
            
                $auth = [
                    $authLogin,
                    $authPass
                ];

                try {
                    $response = $client->request('POST', $url, [
                        'auth' => $auth,
                        'headers' => $headers,
                        'body' => $body,
                        'verify'  => false,
                    ]);
                
                    $responseBody = json_decode($response->getBody());
                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return $this->sendError($error, [], 500);
                }

            //Enregistrement depot           
                $depot = Depot::create([
                        'id' => Uuid::uuid4()->toString(),
                    'user_client_id' => $userClient->id,
                    'partenaire_id' => $partenaire->id,
                    'user_partenaire_id' => $userPartenaire->id,
                    'libelle' => 'Depot effectué chez '.$partenaire->libelle,
                    'solde_avant' => $soldeAv,
                    'montant' => $montantReel,
                    'solde_apres' => $soldeAp,
                    'frais' => $frai,
                    'status' => 1,
                    'deleted' => 0,
                    'validate' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

                MouchardPartenaire::create([
                        'id' => Uuid::uuid4()->toString(),
                    'libelle' => 'Depot de '. $montantReel.' effectué sur le compte '. $userClient->username . ' de '.$userClient->lastname.' '.$userClient->name.'. Frais :'.$frai,
                    'user_partenaire_id' => $userPartenaire->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

            //Determination commission
                $com = Commission::where('deleted',0)->where('type_operation','depot')->orderBy('id','DESC')->first();

                $commission = 0;
                if($com){
                    if($com->type == 'Taux pourcentage'){
                        $commission = $depot->frais * $com->value / 100;
                    }else{
                        $commission = $com->value;
                    }
                }
            
            //Décrémentation du compte de distribution
                $distribution_account_operation = AccountDistributionOperation::create([
                        'id' => Uuid::uuid4()->toString(),
                    'solde_avant' => $distribution_account->solde,
                    'montant' => $depot->montant,
                    'solde_apres' => $distribution_account->solde - ($depot->montant + $depot->frais),
                    'libelle' => 'Depot de '. $depot->montant .' effectué sur le compte '.$depot->userClient->telephone.' avec des frais de '. $depot->frais,
                    'type' => 'debit',
                    'account_distribution_id' => $distribution_account->id,
                    'deleted' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $distribution_account->solde -= ($depot->montant + $depot->frais);
                $distribution_account->save();

                
            //Incrémentation du compte commission
                if($commission > 0){
                    $commission_account = AccountCommission::where('partenaire_id',$depot->partenaire->id)->where('deleted',0)->first();

                    $commission_account_operation = AccountCommissionOperation::create([
                        'id' => Uuid::uuid4()->toString(),
                        'solde_avant' => $commission_account ? $commission_account->solde : 0,
                        'montant' => $commission,
                        'solde_apres' => $commission_account->solde + $commission,
                        'libelle' => 'Commission sur le depot  n°'.$depot->id,
                        'type' => 'credit',
                        'account_commission_id' => $commission_account->id,
                        'deleted' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    $commission_account->solde += $commission;
                    $commission_account->save();
                }

            $message = 'Vous avez reçu un depot de '.$montantReel.' XOF de la part de '.$partenaire->libelle.'.';
            if($userClient->sms == 1){
                $this->sendSms($userClient->username,$message);
            }else{
                $arr = ['messages'=> $message,'objet'=>'Confirmation du depot','from'=>'noreply-bcv@bestcash.me'];
                Mail::to([$userClient->kycClient->email,])->send(new MailAlerte($arr));
            }
            
            $message = 'Depot effectué à '.$userClient->name.' '.$userClient->lastname.'. Commission de l\'operation : '.$commission.' XOF.';
            $this->sendSms($depot->partenaire->telephone,$message);
            return $this->sendResponse($depot,'Depot effectué avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
    

    public function searchCommission(Request $request){
        try{
            $req = $request->all();
            $debut = explode('T',$req['debut'])[0].' 00:00:00';
            $fin = explode('T',$req['fin'])[0].' 23:59:59';
            
            $partenaire = Partenaire::where('id',$request->id)->first();
            $operations = AccountCommissionOperation::where('deleted',0)->where('account_commission_id',$partenaire->accountCommission->id)->whereBetween('created_at',[$debut,$fin])->orderBy('id','desc')->get()->all();  
            return $this->sendResponse($operations, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function searchDistribution(Request $request){
        try{
            $req = $request->all();
            $debut = explode('T',$req['debut'])[0].' 00:00:00';
            $fin = explode('T',$req['fin'])[0].' 23:59:59';

            $partenaire = Partenaire::where('id',$request->id)->first();
            $operations = AccountDistributionOperation::where('deleted',0)->where('account_distribution_id',$partenaire->accountDistribution->id)->whereBetween('created_at',[$debut,$fin])->orderBy('id','desc')->get()->all(); 
            return $this->sendResponse($operations, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    
    public function listeOperationPartenaire(Request $request){
        try {
            $request->validate([
                'type' => 'required',
                'partenaire_id' => 'required'
            ]);
            
            $req = $request->all();

            $debut = explode('T',$req['debut'])[0].' 00:00:00';
            $fin = explode('T',$req['fin'])[0].' 23:59:59';

            if($req['status'] == 0){
                $status = 0;
            }else if($req['status'] == 1){
                $status = 1;
            }else {
                $status = null;
            }

            if($request->type == 'depot'){
                $data = DB::select(DB::raw("select libelle , montant , 'depot' as typeOperation , depots.created_at as dateOperation , user_client_id as user, 'depot' as sens, user_clients.username as username
                    From depots, user_clients
                    Where partenaire_id = $request->partenaire_id
                    and depots.status = $status
                    and depots.created_at between '$debut' and '$fin'
                    and validate = 1
                    and user_clients.id = depots.user_client_id order by dateOperation desc"));
            }else{
                $data = DB::select(DB::raw("select libelle , montant , 'retrait' as typeOperation , retraits.created_at as dateOperation , user_client_id as user, 'retrait' as sens, user_clients.username as username
                From retraits, user_clients
                Where partenaire_id = $request->partenaire_id
                and retraits.status = $status
                and retraits.created_at between '$debut' and '$fin'
                and validate = 1
                and user_clients.id = retraits.user_client_id order by dateOperation desc"));
            }
            
            return $this->sendResponse($data, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    
    public function getPartenaireTransactionsAll(Request $request){
        try {
            $transactions = DB::select(DB::raw("SELECT libelle , montant , typeOperation , dateOperation , user , sens , user_clients.name, user_clients.lastname, user_clients.username
            FROM
            (
                select libelle , montant , 'depot' as typeOperation , created_at as dateOperation , user_client_id as user, 'depot' as sens
                From depots
                Where partenaire_id = $request->id
                and status = 1
                and validate = 1
            Union
                select libelle , montant , 'retrait' as typeOperation , created_at as dateOperation , user_client_id as user, 'retrait' as sens
                From retraits
                Where partenaire_id = $request->id
                and status = 1
                and validate = 1
            ) transactions, user_clients where user_clients.id = transactions.user order by dateOperation desc"));

            $data = [];
            foreach ($transactions as $key => $value) {
                $value->id = $key + 1;
                $data[] = $value;
            }
            return $this->sendResponse($data, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function compteCommission(Request $request){
        try{
            $partenaire = Partenaire::where('id',$request->id)->first();
            $compte = $partenaire->accountCommission;
            $operations = AccountCommissionOperation::where('deleted',0)->where('account_commission_id',$partenaire->accountCommission->id)->orderBy('id','desc')->get()->all();   
            $data['compte'] = $compte;
            $data['operations'] = $operations;
            return $this->sendResponse($data, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function compteDistribution(Request $request){
        try{
            $partenaire = Partenaire::where('id',$request->id)->first();
            $compte = $partenaire->accountDistribution;
            $operations = AccountDistributionOperation::where('deleted',0)->where('account_distribution_id',$partenaire->accountDistribution->id)->orderBy('id','desc')->get()->all();   
            
            $data['compte'] = $compte;
            $data['operations'] = $operations;
            return $this->sendResponse($data, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function updateUserPartenaireInfo(Request $request){
        try{
            $request->validate([
                'id' => 'required|string',
                'name' => 'required|string',
                'lastname' => 'required|string'
            ]);
            
            $user = UserPartenaire::where('id',$request->id)->first();
            $user->name = $request->name;
            $user->lastname = $request->lastname;
            $user->save();

            $user->makeHidden(['password']);
        
            return $this->sendResponse($user, 'User');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function updateUserPartenairePassword(Request $request){
        try{
            $request->validate([
                'user_id' => 'required|string',
                'password' => 'required|string'
            ]);
            $user = UserPartenaire::where('id',$request->user_id)->first();            
            $user->password = Hash::make($request->password);
            $user->save();
        
            return $this->sendResponse($user, 'User');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getUserPartenaireInfo(Request $request){
        try{
            $user = UserPartenaire::where('id',$request->id)->first();
            $user->partenaire;
            $user->rolePartenaire->rolePartenairePermissions;
            $user->makeHidden(['password']);
        
            return $this->sendResponse($user, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function retraitDistribution(Request $request){
        try{
            $partenaire = Partenaire::where('id',$request->id)->first();
            $accountDistribution = AccountDistribution::where('deleted',0)->where('partenaire_id',$partenaire->id)->first();
            $montant = (int)$request->montant;
            if($accountDistribution->solde < $montant){
                return $this->sendError('Votre solde distribution est insuffisant pour cette opération', [], 500);
            }

            $client = new Client();
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $url = $base_url."accounts/".$partenaire->code."/transactions";
            
            $body = [
                "transferType" => "WalletToCard",
                "transferAmount" => $montant,
                "currencyCode" => "XOF",
                "referenceMemo" => "Retrait depuis votre compte de distribution BCB virtuelle",
                "last4Digits" => $partenaire->last
            ];

            $body = json_encode($body);
            
            $requestId = GtpRequest::create([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            $headers = [
                'programId' => $programID,
                'requestId' => $requestId->id,
                'Content-Type' => 'application/json', 'Accept' => 'application/json'
            ];
        
            $auth = [
                $authLogin,
                $authPass
            ];

            try {
                $response = $client->request('POST', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                    'body' => $body,
                    'verify'  => false,
                ]);
            
                $responseBody = json_decode($response->getBody());
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return $this->sendError($error, [], 500);
            }

            AccountDistributionOperation::create([
                'id' => Uuid::uuid4()->toString(),
                'solde_avant' => $accountDistribution->solde,
                'montant' => $montant,
                'solde_apres' => $accountDistribution->solde - $montant,
                'libelle' => 'Retrait du compte de distribution',
                'type' => 'debit',
                'deleted' => 0,
                'account_distribution_id' => $accountDistribution->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $accountDistribution->solde -= $montant;
            $accountDistribution->save();     

            MouchardPartenaire::create([
                        'id' => Uuid::uuid4()->toString(),
                'libelle' => 'Opération de retrait du compte distribution',
                'user_partenaire_id' => $request->user_partenaire_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);      

            $message = "Vous avez retirer ".$montant." XOF de votre compte de distribution.";
            $this->sendSms($partenaire->telephone,$message);     
            
            return $this->sendResponse($accountDistribution, 'Retrait effectué avec succes');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function retraitCommission(Request $request){
        try{
            $partenaire = Partenaire::where('id',$request->id)->first();
            $accountCommission = AccountCommission::where('deleted',0)->where('partenaire_id',$partenaire->id)->first();
            $montant = (int)$request->montant;
            if($accountCommission->solde < $montant){
                return $this->sendError('Votre solde commission est insuffisant pour cette opération', [], 500);
            }

            $client = new Client();
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $url = $base_url."accounts/".$partenaire->code."/transactions";
            
            $body = [
                "transferType" => "WalletToCard",
                "transferAmount" => $montant,
                "currencyCode" => "XOF",
                "referenceMemo" => "Retrait depuis votre compte de distribution BCB virtuelle",
                "last4Digits" => $partenaire->last
            ];

            $body = json_encode($body);
            
            $requestId = GtpRequest::create([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            $headers = [
                'programId' => $programID,
                'requestId' => $requestId->id,
                'Content-Type' => 'application/json', 'Accept' => 'application/json'
            ];
        
            $auth = [
                $authLogin,
                $authPass
            ];

            try {
                $response = $client->request('POST', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                    'body' => $body,
                    'verify'  => false,
                ]);
            
                $responseBody = json_decode($response->getBody());
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return $this->sendError($error, [], 500);
            }

            AccountCommissionOperation::create([
                        'id' => Uuid::uuid4()->toString(),
                'solde_avant' => $accountCommission->solde,
                'montant' => $montant,
                'solde_apres' => $accountCommission->solde - $montant,
                'libelle' => 'Retrait du compte de commission',
                'type' => 'debit',
                'deleted' => 0,
                'account_commission_id' => $accountCommission->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $accountCommission->solde -= $montant;
            $accountCommission->save();    

            MouchardPartenaire::create([
                        'id' => Uuid::uuid4()->toString(),
                'libelle' => 'Opération de retrait du compte commission',
                'user_partenaire_id' => $request->user_partenaire_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);      

            $message = "Vous avez retirer ".$montant." XOF de votre compte de commission.";
            $this->sendSms($partenaire->telephone,$message);  
        
            return $this->sendResponse($accountCommission, 'Retrait effectué avec succes');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function transfertCommissionDistribution(Request $request){
        try{
            $partenaire = Partenaire::where('id',$request->id)->first();
            $accountCommission = AccountCommission::where('deleted',0)->where('partenaire_id',$partenaire->id)->first();            
            $accountDistribution = AccountDistribution::where('deleted',0)->where('partenaire_id',$partenaire->id)->first();
            $montant = (int)$request->montant;
            if($accountCommission->solde < $montant){
                return $this->sendError('Votre solde commission est insuffisant pour cet opération', [], 500);
            }

            AccountCommissionOperation::create([
                        'id' => Uuid::uuid4()->toString(),
                'solde_avant' => $accountCommission->solde,
                'montant' => $montant,
                'solde_apres' => $accountCommission->solde - $montant,
                'libelle' => 'Transfert vers le compte distribution',
                'type' => 'debit',
                'account_commission_id' => $accountCommission->id,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $accountCommission->solde -= $montant;
            $accountCommission->save();

            AccountDistributionOperation::create([
                        'id' => Uuid::uuid4()->toString(),
                'solde_avant' => $accountDistribution->solde,
                'montant' => $montant,
                'solde_apres' => $accountDistribution->solde + $montant,
                'libelle' => 'Transfert depuis le compte de commission',
                'type' => 'credit',
                'deleted' => 0,
                'account_distribution_id' => $accountDistribution->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $accountDistribution->solde += $montant;
            $accountDistribution->save();

            MouchardPartenaire::create([
                        'id' => Uuid::uuid4()->toString(),
                'libelle' => 'Opération de transfert du compte commission vers le compte distribution',
                'user_partenaire_id' => $request->user_partenaire_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            
            
            $message = "Vous avez transferer ".$montant." XOF de votre compte commission vers votre compte de distribution.";
            $this->sendSms($partenaire->telephone,$message);
            return $this->sendResponse([], 'Transfert effectué avec succes');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function showDepotPartenaire(Request $request){
        try {
            $req = $request->all();

            $depot = Depot::where('id',$request->id)->where('deleted',0)->first();  

            $depot['client'] = $depot->userClient->name.' '.$depot->userClient->lastname;
            $retrait['partenaire'] = $depot->partenaire ? $depot->partenaire->libelle : 'Rechargement directe';
            $depot['telephone'] = $depot->userClient->telephone;
            $depot['date'] = $depot->created_at->format('d-m-Y H:i');
            
            return $this->sendResponse($depot, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function showRetraitPartenaire(Request $request){
        try {
            $req = $request->all();

            $retrait = Retrait::where('id',$request->id)->where('deleted',0)->first();

            $retrait['client'] = $retrait->userClient->name.' '.$retrait->userClient->lastname;
            $retrait['partenaire'] = $retrait->partenaire ? $retrait->partenaire->libelle : 'Rechargement directe';
            $retrait['telephone'] = $retrait->userClient->telephone;
            $retrait['date'] = $retrait->created_at->format('d-m-Y H:i');
            
            return $this->sendResponse($retrait, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function listeRetraitUnvalidatePartenaire(Request $request){
        try {
            $request->validate([
                'partenaire_id' => 'required'
            ]);
            $req = $request->all();
            /*
            if(!Auth::attempt($request->only(['username', 'password']))){
                return $this->sendError('Verifier les parametres d\'authentification', [], 500);
            }*/

            $retraits = Retrait::where('partenaire_id',$req['partenaire_id'])->where('deleted',0)->where('status',0)->where('validate',0)->get()->all();

            foreach ($retraits as $value) {
                $value['client'] = $value->userClient->name.' '.$value->userClient->lastname;
                $value['telephone'] = $value->userClient->telephone;
                $value['date'] = $value->created_at->format('d-m-Y H:i');
            }
            
            return $this->sendResponse($retraits, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function cancelRetraitPartenaire(Request $request){
        try {
            $request->validate([
                'motif' => 'required',
                'retrait_id' => 'required',
                'user_id' => 'required'
            ]);
            $req = $request->all();

            $retrait = Retrait::where('id',$request['retrait_id'])->first();
            $retrait->rejet_id = $request['user_id'];
            $retrait->validate = 1;
            $retrait->status = null;
            $retrait->motif_rejet = $request['motif'];
            $retrait->save();
            return $this->sendResponse($retrait, 'Retrait annuler avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function validateRetraitPartenaire(Request $request){
        try {
            $request->validate([
                'retrait_id' => 'required',
                'user_id' => 'required'
            ]);
            $req = $request->all();

            $retrait = Retrait::where('id',$request['retrait_id'])->first();
            $retrait->validateur_id = $request['user_id'];
            $retrait->validate = 1;
            $retrait->save();

            
            $data['code'] = $retrait->code;
            $user = UserClient::where('id',$retrait->user_client_id)->first();
            if($user->sms == 1){
                $message = "Le code de confirmation de votre dernier retrait est : ".$code;
                $this->sendSms($user->username,$message);
            }else{
                $email = $user->kycClient->email;
                Mail::to([$email,])->send(new CodeValidationRetrait($data));
            }
            return $this->sendResponse($retrait, 'Retrait en cours. Le client doit maintenant valider l\'opération');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    } 

    public function listeDepotUnvalidatePartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'partenaire_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();

            $depots = Depot::where('partenaire_id',$req['partenaire_id'])->where('deleted',0)->where('validate',0)->where('status',0)->get()->all();

            foreach ($depots as $value) {
                $value['client'] = $value->userClient->name.' '.$value->userClient->lastname;
                $value['telephone'] = $value->userClient->telephone;
                $value['date'] = $value->created_at->format('d-m-Y H:i');
            }
            
            return $this->sendResponse($depots, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function cancelDepotPartenaire(Request $request){
        try {
            $request->validate([
                'motif' => 'required',
                'depot_id' => 'required',
                'user_id' => 'required'
            ]);
            $req = $request->all();

            $depot = Depot::where('id',$request['depot_id'])->first();
            $depot->rejet_id = $request['user_id'];
            $depot->validate = 1;
            $depot->status = null;
            $depot->motif_rejet = $request['motif'];
            $depot->save();
            return $this->sendResponse($depot, 'Depot annuler avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function validateDepotPartenaire(Request $request){
        try {
            $request->validate([
                'depot_id' => 'required',
                'user_id' => 'required'
            ]);
            $req = $request->all();
            $depot = Depot::where('id',$request['depot_id'])->first();
            $data['code'] = $depot->code;
            $userClient = UserClient::where('id',$depot->user_client_id)->first();

            //recuperation solde avant et apres
            $requestId = GtpRequest::create([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $client = new Client();
            $url = $base_url."accounts/".$userClient->code."/balance";
    
            $headers = [
                'programId' => $programID,
                'requestId' => $requestId->id
            ];
    
            $auth = [
                $authLogin,
                $authPass
            ];
        
            try {
                $response = $client->request('GET', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                ]);
        
                $balance = json_decode($response->getBody());
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return $this->sendError($error, [], 500);
            }
            
            $soldeAv = $balance->balance;
            $soldeAp = $soldeAv + $montantReel;


            $depot->validateur_id = $request['user_id'];
            $depot->validate = 1;
            $depot->save();

            

            $client = new Client();
            $url = $base_url."accounts/".$userClient->code."/transactions";
            
            $body = [
                "transferType" => "WalletToCard",
                "transferAmount" => $req['montant'],
                "currencyCode" => "XOF",
                "referenceMemo" => "Depot de ".$req['montant']." XOF sur votre carte ",
                "last4Digits" => $userClient->last
            ];

            $body = json_encode($body);

            
            //return $partenaire->program;
            
            $requestId = GtpRequest::create([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            $headers = [
                'programId' => $programID,
                'requestId' => $requestId->id,
                'Content-Type' => 'application/json', 'Accept' => 'application/json'
            ];;
        
            $auth = [
                $authLogin,
                $authPass
            ];

            

            try {
                $response = $client->request('POST', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                    'body' => $body,
                    'verify'  => false,
                ]);
            
                $responseBody = json_decode($response->getBody());
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return $this->sendError($error, [], 500);
            }
            $message = 'Vous avez reçu un depot de '.$montantReel.' XOF de la part de'.$partenaire->libelle.'.';
            $this->sendSms($userClient->username,$message);

            
            $message = 'Depot effectué à '.$userClient->name.' '.$userClient->lastname.'. Commission de l\'operation : '.$commission.' XOF.';
            $this->sendSms($userClient->username,$message);
            return $this->sendResponse($depot, 'Depot effectué avec success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }


    public function listeUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'partenaire_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }

            $req = $request->all();

            $users = UserPartenaire::where('partenaire_id',$req['partenaire_id'])->where('deleted',0)->get()->all();

            foreach ($users as $value) {
                $value['libelle'] = $value->partenaire->libelle;
                $value['role'] = $value->role->libelle;
                $value['date'] = $value->created_at->format('d-m-Y H:i');
            }
            
            return $this->sendResponse($users, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function showUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();

            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();
            
            return $this->sendResponse($user, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'partenaire_id' => 'required',
                'name' => 'required',
                'lastname' => 'required',
                "role" => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            
            $req = $request->all();
            
            $user = UserPartenaire::create([
                'id' => Uuid::uuid4()->toString(),
                'name' => $req['name'],
                'lastname' => $req['lastname'],
                'username' => strtolower($req['name'][0].''.explode(' ',$req['lastname'])[0]),
                'password' => Hash::make(12345678),
                'partenaire_id' => $req['partenaire_id'],
                'role_id' => $req['role'],
                'status' => 1,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return $this->sendResponse($user, 'Utilisateur crée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function editUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'name' => 'required',
                'lastname' => 'required',
                'role' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();

            $user->name = $request->name;
            $user->lastname = $request->lastname;
            $user->role_id = $req['role'];
            $user->updated_at = Carbon::now();
            $user->save();

            return $this->sendResponse($user, 'Modification effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function deleteUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();
            
            $user->deleted = 1;
            $user->save();

            return $this->sendResponse($user, 'Supression effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function resetUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();
            
            $user->password = encrypt(12345678);
            $user->updated_at = Carbon::now();
            $user->save();

            return $this->sendResponse($user, 'Reinitialisation effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function activationUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();
            
            $user->status = 1;
            $user->updated_at = Carbon::now();
            $user->save();

            return $this->sendResponse($user, 'Activation effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function desactivationUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();
            
            $user->status = 0;
            $user->updated_at = Carbon::now();
            $user->save();

            return $this->sendResponse($user, 'Desactivation effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }


    public function listePartenaireSeuil(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'partenaire_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }

            $req = $request->all();

            $seuils = Seuil::where('partenaire_id',$req['partenaire_id'])->where('deleted',0)->get()->all();

            foreach ($seuils as $value) {
                $value['libelle'] = $value->partenaire->libelle;
                $value['date'] = $value->created_at->format('d-m-Y H:i');
            }
            
            return $this->sendResponse($seuils, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addPartenaireSeuil(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'partenaire_id' => 'required',
                'type_operation' => 'required',
                'type_restriction' => 'required',
                'valeur' => 'required',
                'periode' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            
            $user = Seuil::create([
                        'id' => Uuid::uuid4()->toString(),
                'type_operation' => $request->type_operation,
                'type_restriction' => $request->type_restriction,
                'valeur' => $request->valeur,
                'periode' => $request->periode,
                'partenaire_id' => $request->partenaire_id,
                'etat' => 1,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return $this->sendResponse($user, 'Restriction crée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function editPartenaireSeuil(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'seuil_id' => 'required',
                'type_operation' => 'required',
                'type_restriction' => 'required',
                'valeur' => 'required',
                'periode' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $seuil = Seuil::where('id',$req['seuil_id'])->where('deleted',0)->first();

            $seuil->type_operation = $request->type_operation;
            $seuil->type_restriction = $request->type_restriction;
            $seuil->valeur = $request->valeur;
            $seuil->periode = $request->periode;
            $seuil->updated_at = Carbon::now();
            $seuil->save();

            return $this->sendResponse($seuil, 'Modification effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function deletePartenaireSeuil(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'seuil_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $seuil = Seuil::where('id',$req['seuil_id'])->where('deleted',0)->first();
            
            $seuil->deleted = 1;
            $seuil->save();

            return $this->sendResponse($seuil, 'Supression effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function activationPartenaireSeuil(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'seuil_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $seuil = Seuil::where('id',$req['seuil_id'])->where('deleted',0)->first();
            
            $seuil->etat = 1;
            $seuil->updated_at = Carbon::now();
            $seuil->save();

            return $this->sendResponse($seuil, 'Activation effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function desactivationPartenaireSeuil(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'seuil_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $seuil = Seuil::where('id',$req['seuil_id'])->where('deleted',0)->first();
            
            $seuil->etat = 0;
            $seuil->updated_at = Carbon::now();
            $seuil->save();

            return $this->sendResponse($seuil, 'Desactivation effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    

    public function listePartenaireLimit(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'partenaire_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }

            $req = $request->all();

            $seuils = Limit::where('partenaire_id',$req['partenaire_id'])->where('deleted',0)->get()->all();

            foreach ($seuils as $value) {
                $value['libelle'] = $value->partenaire->libelle;
                $value['date'] = $value->created_at->format('d-m-Y H:i');
            }
            
            return $this->sendResponse($seuils, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addPartenaireLimit(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'partenaire_id' => 'required',
                'type_operation' => 'required',
                'montant' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            
            $limit = Limit::create([
                        'id' => Uuid::uuid4()->toString(),
                'type_operation' => $request->type_operation,
                'montant' => $request->montant,
                'partenaire_id' => $request->partenaire_id,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return $this->sendResponse($limit, 'Limite crée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function editPartenaireLimit(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'limit_id' => 'required',
                'type_operation' => 'required',
                'montant' => 'required',
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $limit = Limit::where('id',$req['limit_id'])->where('deleted',0)->first();

            $limit->type_operation = $request->type_operation;
            $limit->montant = $request->montant;
            $limit->updated_at = Carbon::now();
            $limit->save();

            return $this->sendResponse($limit, 'Modification effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function deletePartenaireLimit(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'limit_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $limit = Limit::where('id',$req['limit_id'])->where('deleted',0)->first();
            
            $limit->deleted = 1;
            $limit->save();

            return $this->sendResponse($limit, 'Supression effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }




    public function customerCredit(Request $request){
        try {
            // Validation du body de la requete
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required',
                'last_digits' => 'required',
                'amount' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()[0], [],422);
            }

            // Check du partenaire et des clés
            $partenaire = ApiPartenaireAccount::where('id',$request->program_id)->where('deleted',0)->first();

            if(!$partenaire){
                return $this->sendError('Aucun partenaire n\'est lié a ce programme', [], 404);
            }

            if($request->header('API-KEY') == null || $request->header('API-KEY') != $partenaire->api_key ||
                $request->header('PUBLIC-API-KEY') == null || $request->header('PUBLIC-API-KEY') != $partenaire->public_api_key ||
                $request->header('SECRET-API-KEY') == null || $request->header('SECRET-API-KEY') != $partenaire->secret_api_key){
                return $this->sendError('Verifier vos clés API', [], 401);
            }
            
            // Calcul des frais
            $frais = 0;
            $fee = ApiPartenaireFee::where('beguin','<=',$request->amount)->where('end','>=',$request->amount)->where('api_partenaire_account_id',$partenaire->id)->orderBy('id','DESC')->first();
            if(!$fee){
                $fee = ApiPartenaireFee::where('beguin','<=',$request->amount)->where('end','>=',$request->amount)->where('api_partenaire_account_id',0)->orderBy('id','DESC')->first();
                if($fee){
                    if($fee->type_fee == 'pourcentage'){
                        $frais = $request->amount * $fee->value / 100;
                    }else{
                        $frais = $fee->value;
                    }
                }
            }else{
                if($fee->type_fee == 'pourcentage'){
                    $frais = $request->amount * $fee->value / 100;
                }else{
                    $frais = $fee->value;
                }
            }

            // Check de la faisabilité
            if($partenaire->balance < ($request->amount + $frais)){
                return $this->sendError('BALANCE INSUFFISANT', [], 401);
            }
    
            $soldeAvant = $partenaire->balance;

            // Préparation de la transaction
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');
            $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');

            
            $client = new Client();
            $url =  $base_url."accounts/".$request->customer_id."/transactions";
            
            $body = [
                "transferType" => "WalletToCard",
                "transferAmount" => $request->amount,
                "currencyCode" => "XOF",
                "referenceMemo" => "Depot de ".$request->amount." XOF sur votre carte ",
                "last4Digits" => $request->last_digits
            ];

            $body = json_encode($body);
            
            $requestId = GtpRequest::create([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            $headers = [
                'programId' => $programID,
                'requestId' => $requestId->id,
                'accountId' => $accountId,
                'Content-Type' => 'application/json', 'Accept' => 'application/json'
            ];
        
            $auth = [
                $authLogin,
                $authPass
            ];
            
            $transaction = ApiPartenaireTransaction::create([
                        'id' => Uuid::uuid4()->toString(),
                'api_partenaire_account_id' => $partenaire->id,
                'type' => 'Debit',
                'montant' => $request->amount,
                'frais' => $frais,
                'commission' => 0,
                'solde_avant' => $soldeAvant,
                'libelle' => 'Rechargement de la carte '.$request->cutomer_id,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            try {
                $response = $client->request('POST', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                    'body' => $body,
                    'verify'  => false,
                ]);            
                $responseBody = json_decode($response->getBody());

                $referenceGtp = $responseBody->transactionId;
    
                $partenaire->balance = $soldeAvant - $request->amount - $frais;
                $partenaire->save();
                
                $transaction->reference = $referenceGtp;
                $transaction->solde_apres = $partenaire->balance;
                $transaction->status = 1;
                $transaction->save();
            } catch (BadResponseException $e) {
                $transaction->status = 0;
                $transaction->save();
                return $this->sendError('Erreur lors de la transaction', [], 500);
            }
            return $this->sendResponse($transaction, 'Client crédité avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function accountBalance(Request $request){
        try {        
            $partenaire = ApiPartenaireAccount::where('id',$request->program_id)->where('deleted',0)->first();

            if(!$partenaire){
                return $this->sendError('Aucun partenaire n\'est lié a ce programme', [], 404);
            }

            if($request->header('API-KEY') == null || $request->header('API-KEY') != $partenaire->api_key ||
                $request->header('PUBLIC-API-KEY') == null || $request->header('PUBLIC-API-KEY') != $partenaire->public_api_key ||
                $request->header('SECRET-API-KEY') == null || $request->header('SECRET-API-KEY') != $partenaire->secret_api_key){
                return $this->sendError('Verifier vos clés API', [], 401);
            }
            
            $data = [];
            $data['balance'] = $partenaire->balance;
            $data['currency'] = 'XOF';

            return $this->sendResponse($data, 'Solde');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function accountTransactions(Request $request){
        try { 
            $partenaire = ApiPartenaireAccount::where('id',$request->program_id)->where('deleted',0)->first();

            if(!$partenaire){
                return $this->sendError('Aucun partenaire n\'est lié a ce programme', [], 404);
            }

            if($request->header('API-KEY') == null || $request->header('API-KEY') != $partenaire->api_key ||
                $request->header('PUBLIC-API-KEY') == null || $request->header('PUBLIC-API-KEY') != $partenaire->public_api_key ||
                $request->header('SECRET-API-KEY') == null || $request->header('SECRET-API-KEY') != $partenaire->secret_api_key){
                return $this->sendError('Verifier vos clés API', [], 401);
            }
            
            $transactions = ApiPartenaireTransaction::where('api_partenaire_account_id',$request->program_id)->get();

            return $this->sendResponse($transactions, 'Supression effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }
    


    private function sendSms($receiver, $message){
        /*$basic  = new \Nexmo\Client\Credentials\Basic(env("NEXMO_KEY"), env("NEXMO_SECRET"));
        $clientSms = new \Nexmo\Client($basic);
        $receiver = str_replace(' ', '', $receiver);
        
        $sms = $clientSms->message()->send([
            'to' => $receiver,
            'from' => 'BCB VIRTUELLE',
            'text' => $message
        ]);*/

        
        $endpoint = "https://smsapi.moov.bj:8443/sendmsg/api?username=ELG&password=elg@&apikey=7073295b7bbc55100c479e3d941518ec&src=UBA+Bmo&dst=".$receiver."&text=".$message."&refnumber=6334353dc8fb7&type=web";  
            
        $client = new \GuzzleHttp\Client([                                                                                                                                                                   
            'verify' => false                                                                                                                                                                               
        ]);                                                                                                                                                                                               
                                                                                                                                                                                                            
        $response = $client->request('GET', $endpoint);                                                                                                                                   
                                                                                                                                                                                                            
        $statusCode = $response->getStatusCode();  
    }
    
    public function sendResponse($data, $message){
    	$response = [
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    public function sendError($message, $data = [], $code = 404){
    	$response = [
            'success' => false,
            'errors' => $message,
        ];


        if(!empty($data)){
            $response['data'] = $data;
        }


        return response()->json($response, $code);
    }
    
    protected function createNewToken($token){
        return $token;
    }
}