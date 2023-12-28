<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Retrait;
use App\Models\Depot;
use App\Models\Service;
use App\Models\Frai;
use App\Models\Partenaire;
use App\Models\UserClient;
use App\Models\UserPartenaire;
use App\Models\MouchardPartenaire;
use App\Models\GtpRequest;
use App\Models\PartnerWallet;
use GuzzleHttp\Client;
use App\Models\CompteCommission;
use App\Models\CompteCommissionOperation;
use App\Models\AccountCommission;
use App\Models\AccountCommissionOperation;
use App\Models\AccountDistribution;
use App\Models\AccountDistributionOperation;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Mail\MailAlerte;
use App\Models\ApiPartenaireAccount;
use App\Models\ApiPartenaireFee;
use App\Models\ApiPartenaireTransaction;
use App\Models\PartnerWalletWithdraw;
use DB;
use Illuminate\Support\Facades\Auth as Auth;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

class PartenaireController extends Controller
{

    public function __construct() {
        //$this->middleware('auth:apiPartenaire', ['except' => ['loginPartenaire','addRetraitPartenaire','userPermissions','permissions']]);
    }

    public function loginPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors(), [],422);
            }

            if (! $token = Auth::guard('apiPartenaire')->attempt($validator->validated())) {
                return  sendError('Identifiants incorrectes', [],401);
            }
            
            $user = auth('apiPartenaire')->user();
            
            if($user->status == 0){
                return sendError('Ce compte est désactivé. Veuillez contactez le service clientèle', [], 401);
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

            return sendResponse($resultat, 'Connexion réussie');
            
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function getFees(Request $request){
        try {            
            $fees = Frai::where('deleted',0)->get();
            return sendResponse($fees, 'fees.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function getServices(Request $request){
        try {            
            $modules = Service::where('deleted',0)->where('type','partenaire')->get();
            return sendResponse($modules, 'Modules.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function configPin(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_partner_id' => 'required|string',
                'pin' => 'required|string',
            ]);

            if ($validator->fails())
            {
                return response()->json([
                    "error" => $validator->errors()->first()
                ], 422);
            }

            $user = UserPartenaire::where('id',$request->user_partner_id)->first();

            if(!$user){
                return sendError('L\'utilisateur n\'existe pas', [], 401);
            }
            

            $user->pin = $request->pin;
            $user->save();
            $user->makeHidden(['password','pin']);
            return sendResponse($user, 'PIN configurer avec succes');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function getDashboardPartenaire(Request $request){
        try {            
            $partenaire = Partenaire::where('id',$request->id)->first();
            $distribution = $partenaire->accountDistribution;
            $commission = $partenaire->accountCommission;   
            
            $data['distribution'] = $distribution;
            $data['commission'] = $commission;
            return sendResponse($data, 'Dashboard.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function getComptePartenaireInfo(Request $request){
        try {            
            $user = UserPartenaire::where('id',$request->id)->where('deleted',0)->first();
            $resultat = [];
            if($user){
                if($user->status == 0){
                    return sendError('Ce compte est désactivé.', [], 500);
                }
                $transactions = DB::select(DB::raw("SELECT libelle , montant , typeOperation , dateOperation , user , sens
                FROM
                (
                    select libelle , montant , 'depot' as typeOperation , created_at as dateOperation , user_client_id as user, 'depot' as sens
                    From depots
                    Where partenaire_id = $user->partenaire_id
                    and status = 'completed'
                Union
                    select libelle , montant , 'retrait' as typeOperation , created_at as dateOperation , user_client_id as user, 'retrait' as sens
                    From retraits
                    Where partenaire_id = $user->partenaire_id
                    and status = 'completed'
                ) 
                transactions order by dateOperation desc"));

                $trans = [];
                foreach ($transactions as $key => $value) {
                    $value->id = $key + 1;
                    $trans[] = $value;
                }
                $resultat['utilisateur'] = $user;
                $resultat['transactions'] = $transactions;
                return sendResponse($resultat, 'Informations récupérées avec succes.');
            }else{
                return sendError('Cet utilisateur n\'exite pas dans la base', [], 500);
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function getPartnerAllTransactions(Request $request){
        try {          
            $userPartenaire = UserPartenaire::where('id',$request->user_partenaire_id)->first();

            $partenaire = $userPartenaire->partenaire;

            $retraits = Retrait::select('id', 'libelle', 'montant', 'created_at')->where('partenaire_id',$partenaire->id)->where('status','completed')->where('deleted',0)->get();
            foreach($retraits as $retrait){
                $retrait->type = 'retrait';
                $retrait->partenaire = $retrait->partenaire;
                $retrait->userClient = $retrait->userClient;
            }

            $depots = Depot::select('id', 'libelle', 'montant', 'created_at')->where('partenaire_id',$partenaire->id)->where('status','completed')->where('deleted',0)->get();
            foreach($depots as $depot){
                $depot->type = 'depot';
                $depot->partenaire = $depot->partenaire;
                $depot->userClient = $depot->userClient;
            }

            $partnerRetraits = PartnerWalletWithdraw::where('status','completed')->where('deleted',0)->get();
            foreach($retraits as $retrait){
                $retrait->type = 'retrait';
                $retrait->partenaire;
            }

            $transactions = array_merge($retraits->toArray(), $depots->toArray());
            $transactions = array_merge($transactions, $partnerRetraits->toArray());
            krsort($transactions);
            $transactions = array_values($transactions);
            return sendResponse($transactions, 'transactions.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function getPartnerPendingCustomersTransactions(Request $request){
        try {          
            $userPartenaire = UserPartenaire::where('id',$request->user_partenaire_id)->first();

            $partenaire = $userPartenaire->partenaire;

            $retraits = Retrait::select('id', 'libelle', 'montant', 'created_at')->where('partenaire_id',$partenaire->id)->where('status','pending')->where('deleted',0)->get();
            foreach($retraits as $retrait){
                $retrait->type = 'retrait';
                $retrait->partenaire = $retrait->partenaire;
                $retrait->userClient = $retrait->userClient;
            }

            $depots = Depot::select('id', 'libelle', 'montant', 'created_at')->where('partenaire_id',$partenaire->id)->where('status','pending')->where('deleted',0)->get();
            foreach($depots as $depot){
                $depot->type = 'depot';
                $depot->partenaire = $depot->partenaire;
                $depot->userClient = $depot->userClient;
            }

            $transactions = array_merge($retraits->toArray(), $depots->toArray());
            krsort($transactions);
            $transactions = array_values($transactions);
            return sendResponse($transactions, 'transactions.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function getPartnerPendingAdminsTransactions(Request $request){
        try {            
            $userPartenaire = UserPartenaire::where('id',$request->user_partenaire_id)->first();
            $partenaire = $userPartenaire->partenaire;

            $retraits = PartnerWalletWithdraw::where('status','pending')->where('deleted',0)->get();
            foreach($retraits as $retrait){
                $retrait->type = 'retrait';
                $retrait->partenaire;
            }

            return sendResponse($retraits, 'transactions.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }    



    public function addWithdrawPartenaire(Request $request){
        try {
            $encrypt_Key = env('ENCRYPT_KEY');
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $validator = Validator::make($request->all(), [
                'username' => ["required" , "string"],
                'montant' => ["required" , "integer"],
                'user_partenaire_id' => ["required" , "string"]
            ]);

            if ($validator->fails()) {
                return  sendError($validator->errors(), [],422);
            }

            $client = UserClient::where('username',$request->username)->where('deleted',0)->first();

            if(!$client){
                return sendError('Ce compte client n\'exite pas. Verifier le numero de telephone et recommencer');
            }else{
                if($client->status == 0){
                    return sendError('Ce compte client est inactif');
                }
                if($client->verification == 0){
                    return sendError('Ce compte client n\'est pas encore verifié');
                }
            }
            
            $card = $client->userCard->first();
            $userPartenaire = UserPartenaire::where('id',$request->user_partenaire_id)->first();
            
            $montant = $request->montant;

            $fraisAndRepartition = getFeeAndRepartition('retrait', $montant);
            $frais = 0;
            if($fraisAndRepartition){
                if($fraisAndRepartition->type == 'pourcentage'){
                    $frais = $montant * $fraisAndRepartition->value / 100;
                }else{
                    $frais = $fraisAndRepartition->value;
                }
            }

            $montantWithFee = $montant + $frais;
                 
            $clienthTTP = new Client();
            $url = $base_url."accounts/".decryptData($card->customer_id, $encrypt_Key)."/balance";
    
            $headers = [
                'programId' => $programID,
                'requestId' => Uuid::uuid4()->toString(),
            ];
    
            $auth = [
                $authLogin,
                $authPass
            ];
        
            try {
                $response = $clienthTTP->request('GET', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                ]);
        
                $balance = json_decode($response->getBody());                    
            
                if($balance->balance < $montantWithFee){
                    return sendError('Le solde du client ne suffit pas pour cet opération');
                }
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return  sendError($error);
            }  
            
            $isRestrictByAdmin = isRestrictByAdmin($montant,$client->id,$userPartenaire->partenaire->id,'retrait');

            if($isRestrictByAdmin != 'ok'){
                return sendError($isRestrictByAdmin);
            }

            $isRestrictByPartenaire = isRestrictByPartenaire($montant,$userPartenaire->partenaire->id,$userPartenaire->id,'retrait');

            if($isRestrictByPartenaire != 'ok'){
                return sendError($isRestrictByPartenaire);
            }
            
            $retrait = Retrait::create([
                'id' => Uuid::uuid4()->toString(),
                'user_client_id' => $client->id,
                'partenaire_id' => $userPartenaire->partenaire->id,
                'user_partenaire_id' => $userPartenaire->id,
                'user_card_id' => $card->id,
                'libelle' => 'Retrait du compte BCV '.$client->username. ' chez le marchand ' .$userPartenaire->partenaire->libelle,
                'montant' => $montant,
                'frais_bcb' => $frais,
                'status' => 'pending',
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            MouchardPartenaire::create([
                'id' => Uuid::uuid4()->toString(),
                'libelle' => 'Retrait de '. $request->montant.' effectué sur le compte '. $client->username . ' de '.$client->lastname.' '.$client->name.'.',
                'user_partenaire_id' => $userPartenaire->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            
            $message = "Un retrait de ". $montant ." xof à été initié sur votre compte BCV. Frais de retrait : ".$frais." XOF";
            sendSms($client->username,$message);

            return sendResponse($retrait,'Retrait initié avec succes. Le client doit maintenant valider l\'opération', 'Success');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }
    
    public function cancelClientWithdrawAsPartner(Request $request){
        try {                
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }

            $token = JWTAuth::getToken();
            $userId = JWTAuth::getPayload($token)->toArray()['sub'];

            $partenaire = UserPartenaire::where('id',$userId)->first()->partenaire;

      
            $retrait = Retrait::where('id',$request->transaction_id)->where('deleted',0)->where('status','pending')->first();

            if($partenaire->id != $retrait->partenaire_id && $userId != $retrait->user_partenaire_id){
                return  sendError('Vous n\'etes pas autorisé à faire cette opération', [$userId,$retrait->user_client_id],401);
            }

            $retrait->status = 'canceled';
            $retrait->deleted = 1;
            $retrait->save();
            
            return sendResponse($retrait, 'Succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }
    


    public function addDepotPartenaire(Request $request){
        $encrypt_Key = env('ENCRYPT_KEY');
        $base_url = env('BASE_GTP_API');
        $programID = env('PROGRAM_ID');
        $authLogin = env('AUTH_LOGIN');
        $authPass = env('AUTH_PASS');
        
        $validator = Validator::make($request->all(), [
            'username' => ["required" , "string"],
            'montant' => ["required" , "integer"],
            'user_partenaire_id' => ["required" , "string"]
        ]);

        if ($validator->fails()) {
            return  sendError($validator->errors(), [],422);
        }

        $client = UserClient::where('username',$request->username)->where('deleted',0)->first();
        if(!$client){
            return sendError('Ce compte client n\'exite pas. Verifier le numero de telephone et recommencer');
        }else{
            if($client->status == 0){
                return sendError('Ce compte client est inactif');
            }
            if($client->verification == 0){
                return sendError('Ce compte client n\'est pas encore verifié');
            }
        }

        $card = $client->userCard->first();
        $userPartenaire = UserPartenaire::where('id',$request->user_partenaire_id)->first();
        
        $distribution_account = AccountDistribution::where('partenaire_id',$userPartenaire->partenaire->id)->where('deleted',0)->first();

        $montant = $request->montant;
        $fraisAndRepartition = getFeeAndRepartition('depot', $montant);
        $frais = 0;
        if($fraisAndRepartition){
            if($fraisAndRepartition->type == 'pourcentage'){
                $frais = $montant * $fraisAndRepartition->value / 100;
            }else{
                $frais = $fraisAndRepartition->value;
            }
        }
        $montantWithoutFee = $montant - $frais;
        
        if($distribution_account->solde < $montantWithoutFee){
            return sendError('Votre solde ne suffit pas pour cette opération');
        }
             
        $compte = $userPartenaire->partenaire->accountDistribution;

        if($compte->solde < $montant){
            return sendError('Votre solde ne suffit pas pour cette opération');
        }

        $referenceBcb = 'ret-'.Uuid::uuid4()->toString();
        
        $depot = Depot::create([
            'id' => Uuid::uuid4()->toString(),
            'user_client_id' => $client->id,
            'user_partenaire_id' => $userPartenaire->id,
            'partenaire_id' => $userPartenaire->partenaire->id,
            'libelle' => 'Depot du compte BCV '.$client->username. ' chez le marchand ' .$userPartenaire->partenaire->libelle,
            'montant' => $montant,
            'reference_bcb'=> $referenceBcb,
            'frais_bcb' => $frais,
            'status' => 'pending',
            'deleted' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        MouchardPartenaire::create([
            'id' => Uuid::uuid4()->toString(),
            'libelle' => 'Initiation d\'un depot de '. $montant.' XOF effectué sur le compte BCV '. $client->username . ' de '.$client->lastname.' '.$client->name.'.',
            'user_partenaire_id' => $userPartenaire->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $referenceBcb = 'ret-'.Uuid::uuid4()->toString();
        $comptePartenaire = AccountDistribution::where('partenaire_id',$userPartenaire->partenaire->id)->where('deleted',0)->first();

        $soldeAvDecr = $comptePartenaire->solde;
        $soldeApDecr = $comptePartenaire->solde - $montant;

        $distribution_account_operation = AccountDistributionOperation::create([
            'id' => Uuid::uuid4()->toString(),
            'reference_bcb' => $referenceBcb,
            'solde_avant' => $soldeAvDecr,
            'montant' => $montant,
            'solde_apres' => $soldeApDecr,
            'libelle' => 'Depot de '. $montant .' XOF effectué sur le compte '.$client->username.'.',
            'type' => 'debit',
            'account_distribution_id' => $comptePartenaire->id,
            'deleted' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        
        $comptePartenaire->solde -= $montant;
        $comptePartenaire->save();

        $depot->partner_is_debited == 1;
        $depot->save();
        
        try{
            $clientHttp = new Client();
            $url = $base_url."accounts/".decryptData((string)$card->customer_id,$encrypt_Key)."/transactions";
            
            $body = [
                "transferType" => "WalletToCard",
                "transferAmount" => round($montantWithoutFee,2),
                "currencyCode" => "XOF",
                "referenceMemo" => "Depot de ".$montantWithoutFee." XOF sur votre carte XOF.",
                "last4Digits" => decryptData((string)$card->last_digits,$encrypt_Key)
            ];

            $body = json_encode($body);
            
            $headers = [
                'programId' => $programID,
                'requestId' => Uuid::uuid4()->toString(),
                'Content-Type' => 'application/json', 'Accept' => 'application/json'
            ];
        
            $auth = [
                $authLogin,
                $authPass
            ];

            try {
                $response = $clientHttp->request('POST', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                    'body' => $body,
                    'verify'  => false,
                ]);
            
                $responseBody = json_decode($response->getBody());

                $referenceGtp = $responseBody->transactionId;
                
                $soldeAvantRetrait = getUserSolde($client->id);
                $soldeApresRetrait = $soldeAvantRetrait + $montantWithoutFee;

                $depot->reference_gtp = $referenceGtp;
                $depot->montant_recu = $montantWithoutFee;
                $depot->solde_avant = $soldeAvantRetrait;
                $depot->solde_apres = $soldeApresRetrait;
                $depot->save();

                $distribution_account_operation->reference_gtp = $referenceGtp;
                $distribution_account_operation->save();

                
                $compteCommissionPartenaire = AccountCommission::where('partenaire_id',$userPartenaire->partenaire->id)->where('deleted',0)->first();
                $this->repartitionCommission($compteCommissionPartenaire,$comptePartenaire,$fraisAndRepartition,$frais,$montant,$referenceBcb,$referenceGtp);
                
                $depot->status = 'completed';
                $depot->save();

                $message = "Depot de ".$montant." XOF sur votre carte ". decryptData((string)$card->customer_id,$encrypt_Key)." Frais de retrait : ".$frais." XOF. Montant reçu : ".$montantWithoutFee." XOF";
                if($client->sms == 1){
                    sendSms($client->username,$message);
                }else{
                    $arr = ['messages'=> $message,'objet'=>'Confirmation du depot','from'=>'noreply-bcv@bestcash.me'];
                    Mail::to([$client->kycClient->email,])->send(new MailAlerte($arr));
                }
                
                $message = 'Depot effectué à '.$client->name.' '.$client->lastname.'.';
                sendSms($depot->partenaire->telephone,$message);
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return sendError($error, [], 500);
            }
            return sendResponse($depot, 'Succès');
        }catch (\Exception $e) {
            return sendError($e->getMessage());
        }  
    }

    public function completeDepotPartenaire(Request $request){
        $encrypt_Key = env('ENCRYPT_KEY');
        $base_url = env('BASE_GTP_API');
        $programID = env('PROGRAM_ID');
        $authLogin = env('AUTH_LOGIN');
        $authPass = env('AUTH_PASS');
        
        $validator = Validator::make($request->all(), [
            'transaction_id' => ["required" , "string"],
            'user_partenaire_id' => ["required" , "string"]
        ]);

        if ($validator->fails()) {
            return  sendError($validator->errors(), [],422);
        }

        $referenceBcb = 'ret-'.Uuid::uuid4()->toString();
        $userPartenaire = UserPartenaire::where('id',$request->user_partenaire_id)->first();
        $depot = Depot::where('id',$request->transaction_id)->first();
        $card = $depot->userClient->userCard->first();
        $client = $depot->userClient;
        
        $montant = $depot->montant;
        $fraisAndRepartition = getFeeAndRepartition('depot', $montant);
        $frais = 0;
        if($fraisAndRepartition){
            if($fraisAndRepartition->type == 'pourcentage'){
                $frais = $montant * $fraisAndRepartition->value / 100;
            }else{
                $frais = $fraisAndRepartition->value;
            }
        }
        $montantWithoutFee = $montant - $frais;
            
        $compte = $userPartenaire->partenaire->accountDistribution;

        if($compte->solde < $montant){
            return sendError('Votre solde ne suffit pas pour cette opération');
        }

        if($depot->partner_is_debited == 0 || $depot->partner_is_debited == null){
            $comptePartenaire = AccountDistribution::where('partenaire_id',$userPartenaire->partenaire->id)->where('deleted',0)->first();

            $soldeAvDecr = $comptePartenaire->solde;
            $soldeApDecr = $comptePartenaire->solde - $montant;

            AccountDistributionOperation::create([
                'id' => Uuid::uuid4()->toString(),
                'solde_avant' => $soldeAvDecr,
                'montant' => $montant,
                'solde_apres' => $soldeApDecr,
                'libelle' => 'Depot de '. $montant .' XOF effectué sur le compte '.$client->username.'.',
                'type' => 'debit',
                'account_distribution_id' => $comptePartenaire->id,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            $comptePartenaire->solde -= $montant;
            $comptePartenaire->save();

            $depot->partner_is_debited == 1;
            $depot->save();
        }
        
        if($depot->reference_gtp == null){
            try{
                $clientHttp = new Client();
                $url = $base_url."accounts/".decryptData((string)$card->customer_id,$encrypt_Key)."/transactions";
                
                $body = [
                    "transferType" => "WalletToCard",
                    "transferAmount" => round($montantWithoutFee,2),
                    "currencyCode" => "XOF",
                    "referenceMemo" => "Depot de ".$montant." XOF sur votre carte ". decryptData((string)$card->customer_id,$encrypt_Key)." Frais de retrait : ".$frais." XOF. Montant reçu : ".$montantWithoutFee." XOF.",
                    "last4Digits" => decryptData((string)$card->last_digits,$encrypt_Key)
                ];

                $body = json_encode($body);
                
                $headers = [
                    'programId' => $programID,
                    'requestId' => Uuid::uuid4()->toString(),
                    'Content-Type' => 'application/json', 'Accept' => 'application/json'
                ];
            
                $auth = [
                    $authLogin,
                    $authPass
                ];

                try {
                    $response = $clientHttp->request('POST', $url, [
                        'auth' => $auth,
                        'headers' => $headers,
                        'body' => $body,
                        'verify'  => false,
                    ]);
                
                    $responseBody = json_decode($response->getBody());

                    $referenceGtp = $responseBody->transactionId;
                    
                    $soldeAvantRetrait = getUserSolde($client->id);
                    $soldeApresRetrait = $soldeAvantRetrait + $montantWithoutFee;

                    $depot->reference_gtp = $referenceGtp;
                    $depot->status = 'completed';
                    $depot->montant_recu = $montantWithoutFee;
                    $depot->solde_avant = $soldeAvantRetrait;
                    $depot->solde_apres = $soldeApresRetrait;
                    $depot->save();

                    
            
                    $compteCommissionPartenaire = AccountCommission::where('partenaire_id',$userPartenaire->partenaire->id)->where('deleted',0)->first();
                    $this->repartitionCommission($compteCommissionPartenaire,$comptePartenaire,$fraisAndRepartition,$frais,$montant,$referenceBcb,$referenceGtp);

                    $message = "Depot de ".$montant." XOF sur votre carte ". decryptData((string)$card->customer_id,$encrypt_Key)." Frais de retrait : ".$frais." XOF. Montant reçu : ".$montantWithoutFee." XOF";
                    if($client->sms == 1){
                        sendSms($client->username,$message);
                    }else{
                        $arr = ['messages'=> $message,'objet'=>'Confirmation du depot','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$client->kycClient->email,])->send(new MailAlerte($arr));
                    }
                    
                    $message = 'Depot effectué à '.$client->name.' '.$client->lastname.'.';
                    sendSms($depot->partenaire->telephone,$message);

                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return sendError($error, [], 500);
                }
            }catch (\Exception $e) {
                return sendError($e->getMessage());
            }  
        }
        return sendResponse($depot, 'Succès');
    }



    
    public function getPartnerWallets(Request $request){
        try {
            $wallets = PartnerWallet::where('partenaire_id',$request->partnerId)->where('deleted',0)->get();
            return sendResponse($wallets, 'Success');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function addPartnerWallet(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'phone_code' => ["nullable" , "string"],
                'phone' => ["nullable" , "string"],
                'customer_id' => ["nullable" , "string"],
                'last_digits' => ["nullable" , "string"],
                'user_partenaire_id' => ["required" , "string"]
            ]);

            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }

            $userPartenaire = UserPartenaire::where('id',$request->user_partenaire_id)->first();
            $partenaire = $userPartenaire->partenaire;

            $wallet = PartnerWallet::create([
                'id' => Uuid::uuid4()->toString(),
                "type" => $request->walletType,
                "phone" => $request->phone,
                "phone_code" => $request->phone_code,
                "customer_id" => $request->customer_id,
                "last_digits" => $request->last_digits,
                "partenaire_id" => $partenaire->id,
                "deleted" => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return sendResponse($wallet, 'Success');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function updatePartnerWallet(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'phone_code' => ["nullable" , "string"],
                'phone' => ["nullable" , "string"],
                'customer_id' => ["nullable" , "string"],
                'last_digits' => ["nullable" , "string"],
                'user_partenaire_id' => ["required" , "string"]
            ]);
            
            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }

            $wallet = PartnerWallet::where('id',$request->walletId)->first();

            if(!$wallet){
                return sendError('Portefeuille non trouvé', [], 401);
            }

            $wallet->phone_code = $request->phone_code;
            $wallet->phone = $request->phone;
            $wallet->customer_id = $request->customer_id;
            $wallet->last_digits = $request->last_digits;
            $wallet->save();

            return sendResponse($wallet, 'Success');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function deletePartnerWallet(Request $request){
        try {
            $wallet = PartnerWallet::where('id',$request->walletId)->first();

            if(!$wallet){
                return sendError('Portefeuille non trouvé', [], 401);
            }

            $wallet->deleted = 1;
            $wallet->save();

            return sendResponse($wallet, 'Success');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function withdrawPartnerToWallet(Request $request){
        try {
            $encrypt_Key = env('ENCRYPT_KEY');
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $wallet = PartnerWallet::where('id',$request->walletId)->first();
            $partner = $wallet->partenaire;
            $montant = $request->montant;

            $commissionAccount = AccountCommission::where('partenaire_id',$partner->id)->first();
            $soldeAvRetrait = $commissionAccount->solde;
            $soldeApRetrait = $soldeAvRetrait - $montant;

            

            $commissionAccount->solde -= $request->montant;
            $commissionAccount->save();
            $referenceBcb = 'ptnret-'.Uuid::uuid4()->toString();

            AccountCommissionOperation::create([
                'id' => Uuid::uuid4()->toString(),
                'reference_bcb'=> $referenceBcb,
                'solde_avant' => $soldeAvRetrait,
                'montant' => $request->montant,
                'solde_apres' => $soldeApRetrait,
                'libelle' => 'Retrait de '.$montant.' XOF de votre compte de commission BCV.',
                'type' => 'debit',
                'account_commission_id' => $commissionAccount->id,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),            
            ]);
                    
            $referenceBcb = 'ptnret-'.Uuid::uuid4()->toString();
            // Retrait sur le compte partenaire

            if($wallet->type == 'card'){
                $libelle = 'la carte '.decryptData($wallet->customer_id, $encrypt_Key).', ****'.decryptData($wallet->last_digits, $encrypt_Key);
            }else {            
                if($wallet->type == 'bcv'){   
                    $libelle = 'le compte BCV '.$wallet->phone_code.$wallet->phone ;
                }else{       
                    $libelle = 'le compte '.$wallet->type.' '.$wallet->phone_code.$wallet->phone ;
                }      
            }


            $retrait = PartnerWalletWithdraw::create([
                'id' => Uuid::uuid4()->toString(),
                'montant' => $request->montant,
                'partenaire_id'=> $partner->id,
                'wallet_id' => $wallet->id,
                'libelle' => 'Cashout vers '.$libelle,
                'status' => 'pending',
                'reference_bcb' => $referenceBcb,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),    
            ]);

            
            if($wallet->type == 'card'){
                // Transfert vers la carte de l'utilisateur

                    $client = new Client();
                    $url = $base_url."accounts/".decryptData($wallet->customer_id, $encrypt_Key)."/transactions";
                    
                    $body = [
                        "transferType" => "WalletToCard",
                        "transferAmount" => round($montant,2),
                        "currencyCode" => "XOF",
                        "last4Digits" => decryptData($wallet->last_digits, $encrypt_Key),
                        "referenceMemo" => 'Transfert de '.$montant.' vers la carte '.decryptData($wallet->customer_id, $encrypt_Key).'.'
                    ];
                    
                    $body = json_encode($body);
            
                    $headers = [
                        'programId' => $programID,
                        'requestId' =>  Uuid::uuid4()->toString(),
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
        
                        $resultat = json_decode($response->getBody());                    
        
                        $referenceGtpCredit = $resultat->transactionId;
                        $soldeApRetrait = $soldeAvRetrait - $montant;

                        $retrait->reference_gtp_credit = $referenceGtpCredit;
                        $retrait->solde_avant = $soldeAvRetrait;
                        $retrait->solde_apres = $soldeApRetrait;
                        $retrait->status = 'completed';
                        $retrait->save();
                        
                    } catch (BadResponseException $e) {
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return sendError($error, [], 500);
                    }

                // Fin transfert vers la carte de l'utilisateur
                
                // A qui notifier?
                return sendResponse($retrait, 'Transfert effectué avec succes.');
            }else {            
                if($wallet->type == 'bcv'){                                
                    $receiver =  UserClient::where('deleted',0)->where('username',$wallet->phone_code.$wallet->phone)->first();
                        
                    $receiverFirstCard =  $receiver->userCard->first();

                    // Transfert vers la carte de l'utilisateur

                        $client = new Client();
                        $url = $base_url."accounts/".decryptData($receiverFirstCard->customer_id, $encrypt_Key)."/transactions";
                        
                        $body = [
                            "transferType" => "WalletToCard",
                            "transferAmount" => round($montant,2),
                            "currencyCode" => "XOF",
                            "last4Digits" => decryptData($receiverFirstCard->last_digits, $encrypt_Key),
                            "referenceMemo" => 'Retrait de '.$montant.' XOF de votre compte de commission BCV.'
                        ];
                        
                        $body = json_encode($body);
                
                        $headers = [
                            'programId' => $programID,
                            'requestId' =>  Uuid::uuid4()->toString(),
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
            
                            $resultat = json_decode($response->getBody());                
        
                            $referenceGtpCredit = $resultat->transactionId;
    
                            $retrait->reference_gtp_credit = $referenceGtpCredit;
                            $retrait->solde_avant = $soldeAvRetrait;
                            $retrait->solde_apres = $soldeApRetrait;
                            $retrait->status = 'completed';
                            $retrait->save();
                            
                        } catch (BadResponseException $e) {
                            $json = json_decode($e->getResponse()->getBody()->getContents());
                            $error = $json->title.'.'.$json->detail;
                            return sendError($error, [], 500);
                        }

                    // Fin transfert vers la carte de l'utilisateur

                    return sendResponse($retrait, 'Transfert effectué avec succes.');
                }else if($wallet->type == 'bmo'){                        
                    try{             
                        $partner_reference = substr($wallet->phone_code.$wallet->phone, -4).time();
                        $base_url_bmo = env('BASE_BMO');
            
                        // Realisation de la transaction
            
                        $client = new Client();
                        $url = $base_url_bmo."/operations/credit";
                        
                        $body = [
                            "amount" => $request->montant,
                            "customer" => [
                                "phone"=> "+".$wallet->phone_code.$wallet->phone,
                                "firstname"=> $request->name,
                                "lastname"=> $request->lastname
                            ]
                        ];
            
                        $body = json_encode($body);
                
                        $headers = [
                            'X-Auth-ApiKey' => env('APIKEY_BMO_CREDIT'),
                            'X-Auth-ApiSecret' => env('APISECRET_BMO_CREDIT'),
                            'Content-Type' => 'application/json', 'Accept' => 'application/json'
                        ];
                
                        $response = $client->request('POST', $url, [
                            'headers' => $headers,
                            'body' => $body,
                            'verify'  => false,
                        ]);
            
                        $resultat_credit_bmo = json_decode($response->getBody());
                        $soldeApRetrait = $soldeAvRetrait - $montant;
                        
                        $retrait->reference_operateur = $resultat_credit_bmo->reference;
                        $retrait->solde_avant = $soldeAvRetrait;
                        $retrait->solde_apres = $soldeApRetrait;
                        $retrait->status = 'completed';
                        $retrait->save();
            
                    } catch (BadResponseException $e) {
                        return sendError($e->getMessage(), [], 401);
                    }
                }else{
                    try { 
                        $base_url_kkp = env('BASE_KKIAPAY');
            
                        $client = new Client();
                        $url = $base_url_kkp."/api/v1/payments/deposit";
                        
                        $partner_reference = substr($wallet->phone_code.$wallet->phone, -4).time();
                        $body = [
                            "phoneNumber" => $wallet->phone_code.$wallet->phone,
                            "amount" => $request->montant,
                            "reason" => 'Retrait de '.$montant.' XOF de votre compte de commission BCV.',
                            "partnerId" => $partner_reference
                        ];



                        $body = json_encode($body);
                        $headers = [
                            'x-private-key' => env('PRIVATE_KEY_KKIAPAY'),
                            'x-secret-key' => env('SECRET_KEY_KKIAPAY'),
                            'x-api-key' => env('API_KEY_KKIAPAY')
                        ];

                        $response = $client->request('POST', $url, [
                            'headers' => $headers,
                            'body' => $body
                        ]);

                        $resultat = json_decode($response->getBody());  

                        
                        $status = "PENDING";
                        $starttime = time();

                        while ($status == "PENDING") {
                            $externalTransaction = $this->resultat_check_status_kkp($resultat->transactionId);
                            if ($externalTransaction->status == "SUCCESS"){
                                $reference_operateur = $externalTransaction->externalTransactionId;
                        
                                $soldeApRetrait = $soldeAvRetrait - $montant;
                                $retrait->reference_operateur = $reference_operateur;
                                $retrait->solde_avant = $soldeAvRetrait;
                                $retrait->solde_apres = $soldeApRetrait;
                                $retrait->status = 'completed';
                                $retrait->save();
                                $status = "SUCCESS";
                            }else if($externalTransaction->status == "FAILED") {
                                $status = "FAILED";
                                return sendError('Echec lors du paiement du transfert. Contacter notre service clientèle', [], 500);
                            }else{
                                $now = time()-$starttime;
                                if ($now > 125) {
                                    return sendError('Echec de confirmation du transfert. Contacter notre service clientèle', [], 500);
                                }
                                $status = $externalTransaction->status;
                            }
                        }
                    } catch (BadResponseException $e) {
                        return json_encode(['message' => $e->getMessage() , 'data' => []]);
                    }
                }      
            }

            
            // Envoie de notification a l'emmeteur 
                $message = 'Retrait de '.$montant.' XOF de votre compte de commission BCV.';   
                sendSms($partner->telephone,$message);

                $email = $partner->email;
                $arr = ['messages'=> $message,'objet'=>'Alerte retrait sur compte de commission','from'=>'noreply-bcv@bestcash.me'];
                Mail::to([$email,])->send(new MailAlerte($arr));
            // Fin envoie de notification a l'emmeteur 


            return sendResponse($wallet, 'Success');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function completeWithdrawPartnerToWallet(Request $request){
        try {
            $encrypt_Key = env('ENCRYPT_KEY');
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $wallet = PartnerWallet::where('id',$request->walletId)->first();
            $partner = $wallet->partenaire;
            $montant = $request->montant;

            $commissionAccount = AccountCommission::where('partenaire_id',$partner->id)->first();
            $soldeAvRetrait = $commissionAccount->solde;
            $soldeApRetrait = $soldeAvRetrait - $montant;

            

            $commissionAccount->solde -= $request->montant;
            $commissionAccount->save();
            $referenceBcb = 'ptnret-'.Uuid::uuid4()->toString();

            AccountCommissionOperation::create([
                'id' => Uuid::uuid4()->toString(),
                'reference_bcb'=> $referenceBcb,
                'solde_avant' => $soldeAvRetrait,
                'montant' => $request->montant,
                'solde_apres' => $soldeApRetrait,
                'libelle' => 'Retrait de '.$montant.' XOF de votre compte de commission BCV.',
                'type' => 'debit',
                'account_commission_id' => $commissionAccount->id,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),            
            ]);
                    
            $referenceBcb = 'ptnret-'.Uuid::uuid4()->toString();
            // Retrait sur le compte partenaire
            $retrait = PartnerWalletWithdraw::create([
                'id' => Uuid::uuid4()->toString(),
                'montant' => $request->montant,
                'partenaire_id'=> $partner->id,
                'wallet_id' => $wallet->id,
                'status' => 'pending',
                'reference_bcb' => $referenceBcb,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),    
            ]);

            
            if($wallet->type == 'card'){
                // Transfert vers la carte de l'utilisateur

                    $client = new Client();
                    $url = $base_url."accounts/".decryptData($wallet->customer_id, $encrypt_Key)."/transactions";
                    
                    $body = [
                        "transferType" => "WalletToCard",
                        "transferAmount" => round($montant,2),
                        "currencyCode" => "XOF",
                        "last4Digits" => decryptData($wallet->last_digits, $encrypt_Key),
                        "referenceMemo" => 'Transfert de '.$montant.' vers la carte '.decryptData($wallet->customer_id, $encrypt_Key).'.'
                    ];
                    
                    $body = json_encode($body);
            
                    $headers = [
                        'programId' => $programID,
                        'requestId' =>  Uuid::uuid4()->toString(),
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
        
                        $resultat = json_decode($response->getBody());                    
        
                        $referenceGtpCredit = $resultat->transactionId;
                        $soldeApRetrait = $soldeAvRetrait - $montant;

                        $retrait->reference_gtp_credit = $referenceGtpCredit;
                        $retrait->solde_avant = $soldeAvRetrait;
                        $retrait->solde_apres = $soldeApRetrait;
                        $retrait->status = 'completed';
                        $retrait->save();
                        
                    } catch (BadResponseException $e) {
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return sendError($error, [], 500);
                    }

                // Fin transfert vers la carte de l'utilisateur
                
                // A qui notifier?
                return sendResponse($retrait, 'Transfert effectué avec succes.');
            }else {            
                if($wallet->type == 'bcv'){                                
                    $receiver =  UserClient::where('deleted',0)->where('username',$wallet->phone_code.$wallet->phone)->first();
                        
                    $receiverFirstCard =  $receiver->userCard->first();

                    // Transfert vers la carte de l'utilisateur

                        $client = new Client();
                        $url = $base_url."accounts/".decryptData($receiverFirstCard->customer_id, $encrypt_Key)."/transactions";
                        
                        $body = [
                            "transferType" => "WalletToCard",
                            "transferAmount" => round($montant,2),
                            "currencyCode" => "XOF",
                            "last4Digits" => decryptData($receiverFirstCard->last_digits, $encrypt_Key),
                            "referenceMemo" => 'Retrait de '.$montant.' XOF de votre compte de commission BCV.'
                        ];
                        
                        $body = json_encode($body);
                
                        $headers = [
                            'programId' => $programID,
                            'requestId' =>  Uuid::uuid4()->toString(),
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
            
                            $resultat = json_decode($response->getBody());                
        
                            $referenceGtpCredit = $resultat->transactionId;
    
                            $retrait->reference_gtp_credit = $referenceGtpCredit;
                            $retrait->solde_avant = $soldeAvRetrait;
                            $retrait->solde_apres = $soldeApRetrait;
                            $retrait->status = 'completed';
                            $retrait->save();
                            
                        } catch (BadResponseException $e) {
                            $json = json_decode($e->getResponse()->getBody()->getContents());
                            $error = $json->title.'.'.$json->detail;
                            return sendError($error, [], 500);
                        }

                    // Fin transfert vers la carte de l'utilisateur

                    return sendResponse($retrait, 'Transfert effectué avec succes.');
                }else if($wallet->type == 'bmo'){                        
                    try{             
                        $partner_reference = substr($wallet->phone_code.$wallet->phone, -4).time();
                        $base_url_bmo = env('BASE_BMO');
            
                        // Realisation de la transaction
            
                        $client = new Client();
                        $url = $base_url_bmo."/operations/credit";
                        
                        $body = [
                            "amount" => $request->montant,
                            "customer" => [
                                "phone"=> "+".$wallet->phone_code.$wallet->phone,
                                "firstname"=> $request->name,
                                "lastname"=> $request->lastname
                            ]
                        ];
            
                        $body = json_encode($body);
                
                        $headers = [
                            'X-Auth-ApiKey' => env('APIKEY_BMO_CREDIT'),
                            'X-Auth-ApiSecret' => env('APISECRET_BMO_CREDIT'),
                            'Content-Type' => 'application/json', 'Accept' => 'application/json'
                        ];
                
                        $response = $client->request('POST', $url, [
                            'headers' => $headers,
                            'body' => $body,
                            'verify'  => false,
                        ]);
            
                        $resultat_credit_bmo = json_decode($response->getBody());
                        $soldeApRetrait = $soldeAvRetrait - $montant;
                        
                        $retrait->reference_operateur = $resultat_credit_bmo->reference;
                        $retrait->solde_avant = $soldeAvRetrait;
                        $retrait->solde_apres = $soldeApRetrait;
                        $retrait->status = 'completed';
                        $retrait->save();
            
                    } catch (BadResponseException $e) {
                        return sendError($e->getMessage(), [], 401);
                    }
                }else{
                    try { 
                        $base_url_kkp = env('BASE_KKIAPAY');
            
                        $client = new Client();
                        $url = $base_url_kkp."/api/v1/payments/deposit";
                        
                        $partner_reference = substr($wallet->phone_code.$wallet->phone, -4).time();
                        $body = [
                            "phoneNumber" => $wallet->phone_code.$wallet->phone,
                            "amount" => $request->montant,
                            "reason" => 'Retrait de '.$montant.' XOF de votre compte de commission BCV.',
                            "partnerId" => $partner_reference
                        ];



                        $body = json_encode($body);
                        $headers = [
                            'x-private-key' => env('PRIVATE_KEY_KKIAPAY'),
                            'x-secret-key' => env('SECRET_KEY_KKIAPAY'),
                            'x-api-key' => env('API_KEY_KKIAPAY')
                        ];

                        $response = $client->request('POST', $url, [
                            'headers' => $headers,
                            'body' => $body
                        ]);

                        $resultat = json_decode($response->getBody());  

                        
                        $status = "PENDING";
                        $starttime = time();

                        while ($status == "PENDING") {
                            $externalTransaction = $this->resultat_check_status_kkp($resultat->transactionId);
                            if ($externalTransaction->status == "SUCCESS"){
                                $reference_operateur = $externalTransaction->externalTransactionId;
                        
                                $soldeApRetrait = $soldeAvRetrait - $montant;
                                $retrait->reference_operateur = $reference_operateur;
                                $retrait->solde_avant = $soldeAvRetrait;
                                $retrait->solde_apres = $soldeApRetrait;
                                $retrait->status = 'completed';
                                $retrait->save();
                                $status = "SUCCESS";
                            }else if($externalTransaction->status == "FAILED") {
                                $status = "FAILED";
                                return sendError('Echec lors du paiement du transfert. Contacter notre service clientèle', [], 500);
                            }else{
                                $now = time()-$starttime;
                                if ($now > 125) {
                                    return sendError('Echec de confirmation du transfert. Contacter notre service clientèle', [], 500);
                                }
                                $status = $externalTransaction->status;
                            }
                        }
                    } catch (BadResponseException $e) {
                        return json_encode(['message' => $e->getMessage() , 'data' => []]);
                    }
                }      
            }

            
            // Envoie de notification a l'emmeteur 
                $message = 'Retrait de '.$montant.' XOF de votre compte de commission BCV.';   
                sendSms($partner->telephone,$message);

                $email = $partner->email;
                $arr = ['messages'=> $message,'objet'=>'Alerte retrait sur compte de commission','from'=>'noreply-bcv@bestcash.me'];
                Mail::to([$email,])->send(new MailAlerte($arr));
            // Fin envoie de notification a l'emmeteur 


            return sendResponse($wallet, 'Success');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function withdrawPartnerToDistributionAccount(Request $request){
        try {
            $userPartenaire = UserPartenaire::where('id',$request->user_partenaire_id)->first();
            $partenaire = $userPartenaire->partenaire;

            $accountCommission = AccountCommission::where('deleted',0)->where('partenaire_id',$partenaire->id)->first();            
            $accountDistribution = AccountDistribution::where('deleted',0)->where('partenaire_id',$partenaire->id)->first();
            $montant = (int)$request->montant;
            if($accountCommission->solde < $montant){
                return sendError('Votre solde commission est insuffisant pour cet opération', [], 500);
            }
            
            $referenceBcb = 'ret-'.Uuid::uuid4()->toString();

            AccountCommissionOperation::create([
                'id' => Uuid::uuid4()->toString(),
                'solde_avant' => $accountCommission->solde,
                'reference_bcb' => $referenceBcb,
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
                'reference_bcb' => $referenceBcb,
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
            
            
            $message = "Vous avez transferer ".$montant." XOF de votre compte commission vers votre compte de distribution.";
            sendSms($partenaire->telephone,$message);
            return sendResponse([], 'Transfert effectué avec succes');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function transfertCommissionDistribution(Request $request){
        try{
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }



    public function compteCommission(Request $request){
        try{
            $partenaire = Partenaire::where('id',$request->id)->first();
            $compte = $partenaire->accountCommission;
            $operations = AccountCommissionOperation::where('deleted',0)->where('account_commission_id',$partenaire->accountCommission->id)->orderBy('id','desc')->get()->all();   
            $data['compte'] = $compte;
            $data['operations'] = $operations;
            return sendResponse($data, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function compteDistribution(Request $request){
        try{
            $partenaire = Partenaire::where('id',$request->id)->first();
            $compte = $partenaire->accountDistribution;
            $operations = AccountDistributionOperation::where('deleted',0)->where('account_distribution_id',$partenaire->accountDistribution->id)->orderBy('id','desc')->get()->all();   
            
            $data['compte'] = $compte;
            $data['operations'] = $operations;
            return sendResponse($data, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }


    public function listeUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'partenaire_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors(), [],422);
            }

            $req = $request->all();

            $users = UserPartenaire::where('partenaire_id',$req['partenaire_id'])->where('deleted',0)->get()->all();

            foreach ($users as $value) {
                $value['libelle'] = $value->partenaire->libelle;
                $value['role'] = $value->role->libelle;
                $value['date'] = $value->created_at->format('d-m-Y H:i');
            }
            
            return sendResponse($users, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function showUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors(), [],422);
            }
            $req = $request->all();

            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();
            
            return sendResponse($user, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
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
                return  sendError($validator->errors(), [],422);
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
            return sendResponse($user, 'Utilisateur crée avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
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
                return  sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();

            $user->name = $request->name;
            $user->lastname = $request->lastname;
            $user->role_id = $req['role'];
            $user->updated_at = Carbon::now();
            $user->save();

            return sendResponse($user, 'Modification effectuée avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function deleteUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();
            
            $user->deleted = 1;
            $user->save();

            return sendResponse($user, 'Supression effectuée avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function resetUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();
            
            $user->password = encrypt(12345678);
            $user->updated_at = Carbon::now();
            $user->save();

            return sendResponse($user, 'Reinitialisation effectuée avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function activationUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();
            
            $user->status = 1;
            $user->updated_at = Carbon::now();
            $user->save();

            return sendResponse($user, 'Activation effectuée avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function desactivationUserPartenaire(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors(), [],422);
            }
            $req = $request->all();
            $user = UserPartenaire::where('id',$req['user_id'])->where('deleted',0)->first();
            
            $user->status = 0;
            $user->updated_at = Carbon::now();
            $user->save();

            return sendResponse($user, 'Desactivation effectuée avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }







    // A ne pas toucher

    public function getUserPartenaireInfo(Request $request){
        try{
            $user = UserPartenaire::where('id',$request->id)->first();
            $user->partenaire;
            $user->rolePartenaire->rolePartenairePermissions;
            $user->makeHidden(['password']);
        
            return sendResponse($user, 'Liste chargée avec succes.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
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
                return  sendError($validator->errors()[0], [],422);
            }

            // Check du partenaire et des clés
            $partenaire = ApiPartenaireAccount::where('id',$request->program_id)->where('deleted',0)->first();

            if(!$partenaire){
                return sendError('Aucun partenaire n\'est lié a ce programme', [], 404);
            }

            if($request->header('API-KEY') == null || $request->header('API-KEY') != $partenaire->api_key ||
                $request->header('PUBLIC-API-KEY') == null || $request->header('PUBLIC-API-KEY') != $partenaire->public_api_key ||
                $request->header('SECRET-API-KEY') == null || $request->header('SECRET-API-KEY') != $partenaire->secret_api_key){
                return sendError('Verifier vos clés API', [], 401);
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
                return sendError('BALANCE INSUFFISANT', [], 401);
            }
    
            $soldeAvant = $partenaire->balance;

            // Préparation de la transaction
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');
            $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');

            
            $clientHttp = new Client();
            $url =  $base_url."accounts/".$request->customer_id."/transactions";
            
            $body = [
                "transferType" => "WalletToCard",
                "transferAmount" => round($request->amount,2),
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
                $response = $clientHttp->request('POST', $url, [
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
                return sendError('Erreur lors de la transaction', [], 500);
            }
            return sendResponse($transaction, 'Client crédité avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function accountBalance(Request $request){
        try {        
            $partenaire = ApiPartenaireAccount::where('id',$request->program_id)->where('deleted',0)->first();

            if(!$partenaire){
                return sendError('Aucun partenaire n\'est lié a ce programme', [], 404);
            }

            if($request->header('API-KEY') == null || $request->header('API-KEY') != $partenaire->api_key ||
                $request->header('PUBLIC-API-KEY') == null || $request->header('PUBLIC-API-KEY') != $partenaire->public_api_key ||
                $request->header('SECRET-API-KEY') == null || $request->header('SECRET-API-KEY') != $partenaire->secret_api_key){
                return sendError('Verifier vos clés API', [], 401);
            }
            
            $data = [];
            $data['balance'] = $partenaire->balance;
            $data['currency'] = 'XOF';

            return sendResponse($data, 'Solde');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function accountTransactions(Request $request){
        try { 
            $partenaire = ApiPartenaireAccount::where('id',$request->program_id)->where('deleted',0)->first();

            if(!$partenaire){
                return sendError('Aucun partenaire n\'est lié a ce programme', [], 404);
            }

            if($request->header('API-KEY') == null || $request->header('API-KEY') != $partenaire->api_key ||
                $request->header('PUBLIC-API-KEY') == null || $request->header('PUBLIC-API-KEY') != $partenaire->public_api_key ||
                $request->header('SECRET-API-KEY') == null || $request->header('SECRET-API-KEY') != $partenaire->secret_api_key){
                return sendError('Verifier vos clés API', [], 401);
            }
            
            $transactions = ApiPartenaireTransaction::where('api_partenaire_account_id',$request->program_id)->get();

            return sendResponse($transactions, 'Supression effectuée avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    
    protected function createNewToken($token){
        return $token;
    }
    
    public function resultat_check_status_kkp($transactionId){
        try {  
            
            $base_url_kkp = env('BASE_KKIAPAY');

            $client = new Client();
            $url = $base_url_kkp."/api/v1/transactions/status";
            
            $headers = [
                'x-api-key' => env('API_KEY_KKIAPAY')
            ];

            $body = [
                'transactionId' => $transactionId
            ];

            $body = json_encode($body);

            $response = $client->request('POST', $url, [
                'headers' => $headers,
                'body' => $body
            ]);

            
            $externalTransaction = json_decode($response->getBody());
    
            return $externalTransaction;
            
        } catch (BadResponseException $e) {
            return $e->getMessage();
        }
    }

    private function repartitionCommission($compteCommissionPartenaire,$compteDistributionPartenaire,$fraisOperation,$frais,$montant,$referenceBcb,$referenceGtp){
        if($fraisOperation){        
            $fraiCompteCommissions = $fraisOperation->fraiCompteCommissions;
            
            foreach ($fraiCompteCommissions as $value) {
                $compteCommission = CompteCommission::where('id',$value->compte_commission_id)->first();
    
                if($value->type == 'pourcentage'){
                    $commission = $frais * $value->value / 100;
                }else{
                    $commission = $value->value;
                }
    
                $compteCommission->solde += $commission;
                $compteCommission->save();
                
                CompteCommissionOperation::create([
                    'id' => Uuid::uuid4()->toString(),
                    'compte_commission_id'=> $compteCommission->id,
                    'type_operation'=>'rechargement',
                    'montant'=> $montant,
                    'frais'=> $frais,
                    'commission'=> $commission,
                    'reference_bcb'=> $referenceBcb,
                    'reference_gtp'=> $referenceGtp,
                    'status'=> 0,
                    'deleted'=> 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()                
                ]);
            }
    
            if($fraisOperation->value_partenaire > 0){
                if($fraisOperation->type_commission_partenaire == 'pourcentage'){
                    $commissionPartenaire = $frais * $fraisOperation->value_commission_partenaire / 100;
                }else{
                    $commissionPartenaire = $fraisOperation->value_commission_partenaire;
                }
    
                $soldeAvIncr = $compteCommissionPartenaire->solde;
                $compteCommissionPartenaire->solde += $commissionPartenaire;
                $compteCommissionPartenaire->save();
                
                
                $soldeApIncr = $compteDistributionPartenaire->solde + $commissionPartenaire;
    
                AccountCommissionOperation::insert([
                    'id' => Uuid::uuid4()->toString(),
                    'reference_bcb'=> $referenceBcb,
                    'reference_gtp'=> $referenceGtp,
                    'solde_avant' => $soldeAvIncr,
                    'montant' => $commissionPartenaire,
                    'solde_apres' => $soldeApIncr,
                    'libelle' => 'Commission sur depot',
                    'type' => 'credit',
                    'account_commission_id' => $compteCommissionPartenaire->id,
                    'deleted' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),            
                ]);
            }
    
        }
    }
}

