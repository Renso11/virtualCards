<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserClient;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailAlerte;
use App\Models\CompteCommission;
use App\Models\CompteCommissionOperation;
use Illuminate\Support\Facades\Validator;
use App\Models\TransfertOut;
use App\Models\UserCard;
use Ramsey\Uuid\Uuid;

class TransfertController extends Controller
{
    public function __construct() {
        $this->middleware('is-auth', ['except' => ['addContact','createCompteClient', 'loginCompteClient', 'sendCode', 'checkCodeOtp', 'resetPassword','verificationPhone', 'verificationInfoPerso','verificationInfoPiece','saveFile','sendCodeTelephoneRegistration','getServices','sendCodeTelephone']]);
    }

    public function tokenValide(Request $request){
        try {            
            return true;
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }
    
    public function addNewTransfertClient(Request $request){
        try {
            $encrypt_Key = env('ENCRYPT_KEY');
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $validator = Validator::make($request->all(), [
                'name' => ["nullable" , "string"],
                'lastname' => ["nullable" , "string"],
                'montant' => ["required" , "integer"],
                'type' => ["required" , "max:255", "regex:(momo|bmo|card|bcv)"],
                'receveur_telephone' => ["nullable","string"],
                'user_id' => ["required" , "string"],
                'user_card_id' => ["required" , "string"],
                'last_digits' => ["nullable" , "string"],
                'customer_id' => ["nullable" , "string"],
            ]);

            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }

            $sender =  UserClient::where('deleted',0)->where('id',$request->user_id)->first();
            $sender_card =  UserCard::where('deleted',0)->where('id',$request->user_card_id)->first();

            $soldeAvant = getUserSolde($sender->id); 
            
            $montant = $request->montant;
            $fraisAndRepartition = getFeeAndRepartition('transfert', $montant);

            $frais = 0;
            if($fraisAndRepartition){
                if($fraisAndRepartition->type == 'pourcentage'){
                    $frais = $montant * $fraisAndRepartition->value / 100;
                }else{
                    $frais = $fraisAndRepartition->value;
                }
            }

            $montantWithFee = $montant + $frais;    

            $client = new Client();
            $url = $base_url."accounts/".decryptData($sender_card->customer_id, $encrypt_Key)."/transactions";
            
            $body = [
                "transferType" => "CardToWallet",
                "transferAmount" => round($montantWithFee,2),
                "currencyCode" => "XOF",
                "last4Digits" => decryptData($sender_card->last_digits, $encrypt_Key),
                "referenceMemo" => 'Transfert de '.$montant.' vers le numero '.$request->receveur_telephone.'. Frais de transaction : '.$frais
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
                $response = $client->request('POST', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                    'body' => $body,
                    'verify'  => false,
                ]);

                $resultat = json_decode($response->getBody());                    

                $referenceGtpDebit = $resultat->transactionId;
                $soldeApres = $soldeAvant - $montantWithFee;
                    
                $referenceBcb = 'trf-'.Uuid::uuid4()->toString();

                if($request->type == 'card'){
                    $transfert = TransfertOut::create([
                        'id' => Uuid::uuid4()->toString(),
                        "user_client_id" => $sender->id,
                        "user_card_id" => $sender->id,
                        "receveur_customer_id" => $request->customer_id,
                        "receveur_last_digits" => $request->last_digits,
                        "reference_bcb" => $referenceBcb,
                        "reference_gtp_debit" => $referenceGtpDebit,
                        "montant" => $montant,
                        "frais_bcb" => $frais,
                        "moyen_paiement" => $request->type,
                        "libelle" => 'Transfert de '.$montant.' vers la carte '.decryptData($request->last_digits, $encrypt_Key).'.',
                        "is_paid" => 1,
                        "status" => 'pending',
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }else{
                            
                    if($request->type == 'bcv'){
                        
                        $receiver =  UserClient::where('deleted',0)->where('username',$request->receveur_telephone)->first();
                
                        $receiverFirstCard =  $receiver->userCard->first();

                        $transfert = TransfertOut::create([
                            'id' => Uuid::uuid4()->toString(),
                            "user_client_id" => $sender->id,
                            "user_card_id" => $sender->id,
                            "name" => $request->name,
                            "lastname" => $request->lastname,
                            "receveur_customer_id" => $receiverFirstCard->customer_id,
                            "receveur_last_digits" => $receiverFirstCard->last_digits,
                            "receveur_telephone" => $receiver->username,
                            "reference_bcb" => $referenceBcb,
                            "reference_gtp_debit" => $referenceGtpDebit,
                            "montant" => $montant,
                            "frais_bcb" => $frais,
                            "moyen_paiement" => $request->type,
                            "libelle" => 'Transfert de '.$montant.' vers la carte '.decryptData($receiverFirstCard->customer_id, $encrypt_Key).'.',
                            "is_paid" => 1,
                            "status" => 'pending',
                            "deleted" => 0,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }else if($request->type == 'bmo'){ 
                        $transfert = TransfertOut::create([
                            'id' => Uuid::uuid4()->toString(),
                            "user_client_id" => $sender->id,
                            "user_card_id" => $sender->id,
                            "name" => $request->name,
                            "lastname" => $request->lastname,
                            "receveur_telephone" => $request->receveur_telephone,
                            "reference_bcb" => $referenceBcb,
                            "reference_gtp_debit" => $referenceGtpDebit,
                            "montant" => $montant,
                            "frais_bcb" => $frais,
                            "moyen_paiement" => $request->type,
                            "is_paid" => 1,
                            "libelle" => 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.',
                            "status" => 'pending',
                            "deleted" => 0,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);                
                    }else{
                        $transfert = TransfertOut::create([
                            'id' => Uuid::uuid4()->toString(),
                            "user_client_id" => $sender->id,
                            "user_card_id" => $sender->id,
                            "name" => $request->name,
                            "lastname" => $request->lastname,
                            "receveur_telephone" => $request->receveur_telephone,
                            "reference_bcb" => $referenceBcb,
                            "reference_gtp_debit" => $referenceGtpDebit,
                            "montant" => $montant,
                            "frais_bcb" => $frais,
                            "moyen_paiement" => $request->type,
                            "is_paid" => 1,
                            "libelle" => 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.',
                            "status" => 'pending',
                            "deleted" => 0,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]); 
                    }
                }
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return sendError($error, [], 500);
            }

            
            if($request->type == 'card'){

                $client = new Client();
                $url = $base_url."accounts/".decryptData($request->customer_id, $encrypt_Key)."/transactions";
                
                $body = [
                    "transferType" => "WalletToCard",
                    "transferAmount" => $montant,
                    "currencyCode" => "XOF",
                    "last4Digits" => decryptData($request->last_digits, $encrypt_Key),
                    "referenceMemo" => 'Transfert de '.$montant.' vers la carte '.decryptData($request->customer_id, $encrypt_Key).'.'
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
                    $soldeApres = $soldeAvant - $montantWithFee;

                    $transfert->reference_gtp_credit = $referenceGtpCredit;
                    $transfert->solde_avant = $soldeAvant;
                    $transfert->solde_apres = $soldeApres;
                    $transfert->status = 'completed';
                    $transfert->is_debited = 1;
                    $transfert->save();
                    
                    $message = 'Transfert de '.$request->montant.' vers la carte '.$request->customer_id.'.';   
                    if($sender->sms == 1){
                        sendSms($sender->username,$message);
                    }else{
                        $email = $sender->kycClient->email;
                        $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$email,])->send(new MailAlerte($arr));
                    }
                } catch (BadResponseException $e) {
                    $transfert->is_debited = 1;
                    $transfert->save();
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return sendError($error, [], 500);
                }

                $message = 'Transfert de '.$request->montant.' vers la carte '.$request->customer_id.'.';   
                if($sender->sms == 1){
                    sendSms($sender->username,$message);
                }else{
                    $email = $sender->kycClient->email;
                    $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                    Mail::to([$email,])->send(new MailAlerte($arr));
                }
                
                return sendResponse($transfert, 'Transfert effectué avec succes.');
            }else {            
                if($request->type == 'bcv'){

                    $client = new Client();
                    $url = $base_url."accounts/".decryptData($receiverFirstCard->customer_id, $encrypt_Key)."/transactions";
                    
                    $body = [
                        "transferType" => "WalletToCard",
                        "transferAmount" => round($montant,2),
                        "currencyCode" => "XOF",
                        "last4Digits" => decryptData($receiverFirstCard->last_digits, $encrypt_Key),
                        "referenceMemo" => 'Transfert de '.$montant.' XOF vers la carte principale '.decryptData($receiverFirstCard->last_digits, $encrypt_Key).' de '. $receiver->name.' '.$receiver->lastname.'.'
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
                        $soldeApres = $soldeAvant - $montantWithFee;

                        $transfert->reference_gtp_credit = $referenceGtpCredit;
                        $transfert->solde_avant = $soldeAvant;
                        $transfert->solde_apres = $soldeApres;
                        $transfert->status = 'completed';
                        $transfert->is_debited = 1;
                        $transfert->save();
                         
                        $message = 'Transfert de '.$montant.' XOF vers la carte principale '.decryptData($receiverFirstCard->last_digits, $encrypt_Key).' de '. $receiver->name.' '.$receiver->lastname.'.';   
                        if($sender->sms == 1){
                            sendSms($sender->username,$message);
                        }else{
                            $email = $sender->kycClient->email;
                            $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                            Mail::to([$email,])->send(new MailAlerte($arr));
                        }
                    } catch (BadResponseException $e) {
                        $transfert->is_debited = 1;
                        $transfert->save();
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return sendError($error, [], 500);
                    }

                    return sendResponse($transfert, 'Transfert effectué avec succes.');
                }else if($request->type == 'bmo'){                        
                    try{             
                        $partner_reference = substr($request->receveur_telephone, -4).time();
                        $base_url_bmo = env('BASE_BMO');
            
                        $client = new Client();
                        $url = $base_url_bmo."/operations/credit";
                        
                        $body = [
                            "amount" => $montant,
                            "customer" => [
                                "phone"=> "+".$request->receveur_telephone,
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
            
                        $resultat_debit_bmo = json_decode($response->getBody());
                        
                        $transfert->receveur_telephone = $request->receveur_telephone;
                        $transfert->reference_operateur = $resultat_debit_bmo->reference;
                        $transfert->solde_avant = $soldeAvant;
                        $transfert->solde_apres = $soldeApres;
                        $transfert->status = 'completed';
                        $transfert->is_debited = 1;
                        $transfert->save();
            
                    } catch (BadResponseException $e) {
                        $transfert->is_debited = 1;
                        $transfert->save();
                        return sendError($e->getMessage(), [], 401);
                    }
                }else{
                    try { 
                        $base_url_kkp = env('BASE_KKIAPAY');
            
                        $client = new Client();
                        $url = $base_url_kkp."/api/v1/payments/deposit";
                        
                        $partner_reference = substr($request->receveur_telephone, -4).time();
                        $body = [
                            "phoneNumber" => $request->receveur_telephone,
                            "amount" => $request->montant,
                            "reason" => 'Transfert de '.$request->montant.' provenant de '.$sender->username.' effectué depuis son compte BCB Virtuelle. ID de la carte : '.$sender->customer_id.'.',
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
                            $externalTransaction = resultat_check_status_kkp($resultat->transactionId);
                            if ($externalTransaction->status == "SUCCESS"){
                                $reference_operateur = $externalTransaction->externalTransactionId;
                                
                                $transfert->receveur_telephone = $request->receveur_telephone;
                                $transfert->reference_operateur = $reference_operateur;
                                $transfert->solde_avant = $soldeAvant;
                                $transfert->solde_apres = $soldeApres;
                                $transfert->status = 'completed';
                                $transfert->is_debited = 1;
                                $transfert->save();
                                $status = "SUCCESS";
                            }else if($externalTransaction->status == "FAILED") {
                                $status = "FAILED";
                                $transfert->status = 'failed';
                                $transfert->is_debited = 1;
                                $transfert->save();
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
                        $transfert->is_debited = 1;
                        $transfert->save();
                        return json_encode(['message' => $e->getMessage() , 'data' => []]);
                    }
                }

                $message = 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.';   
                if($sender->sms == 1){
                    sendSms($sender->username,$message);
                }else{
                    $email = $sender->kycClient->email;
                    $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                    Mail::to([$email,])->send(new MailAlerte($arr));
                }   
                return sendResponse($transfert, 'Transfert effectué avec succes.'); 
      
            }

        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function completeTransfertClient(Request $request){
        try {
            $encrypt_Key = env('ENCRYPT_KEY');
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $validator = Validator::make($request->all(), [
                'transaction_id' => ["required" , "string"],
            ]);

            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }

            $sender =  UserClient::where('deleted',0)->where('id',$request->user_id)->first();
            
            $transfert = TransfertOut::where('id',$request->transaction_id)->first();                    
            $soldeAvant = getUserSolde($sender->id); 
            $montant = $transfert->montant;
            $frais = $transfert->frais_bcb;
            $montantWithFee = $montant + $frais; 
            
            if($request->type == 'card'){
                $client = new Client();
                $url = $base_url."accounts/".decryptData($transfert->customer_id, $encrypt_Key)."/transactions";
                
                $body = [
                    "transferType" => "WalletToCard",
                    "transferAmount" => $montant,
                    "currencyCode" => "XOF",
                    "last4Digits" => decryptData($transfert->last_digits, $encrypt_Key),
                    "referenceMemo" => 'Transfert de '.$montant.' vers la carte '.decryptData($transfert->customer_id, $encrypt_Key).'.'
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
                    $soldeApres = $soldeAvant - $montantWithFee;

                    $transfert->reference_gtp_credit = $referenceGtpCredit;
                    $transfert->solde_avant = $soldeAvant;
                    $transfert->solde_apres = $soldeApres;
                    $transfert->status = 'completed';
                    $transfert->save();
                     
                    $message = 'Transfert de '.$request->montant.' vers la carte '.$request->customer_id.'.';   
                    if($sender->sms == 1){
                        sendSms($sender->username,$message);
                    }else{
                        $email = $sender->kycClient->email;
                        $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$email,])->send(new MailAlerte($arr));
                    }
                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return sendError($error, [], 500);
                }
                
                $message = 'Transfert de '.$montant.' vers la carte '.$transfert->customer_id.'.';   
                if($sender->sms == 1){
                    sendSms($sender->username,$message);
                }else{
                    $email = $sender->kycClient->email;
                    $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                    Mail::to([$email,])->send(new MailAlerte($arr));
                }
                
                return sendResponse($transfert, 'Transfert effectué avec succes.');
            }else {            
                if($request->type == 'bcv'){                                
                    $receiver =  UserClient::where('deleted',0)->where('username',$transfert->receveur_telephone)->first();
                        
                    $receiverFirstCard =  $receiver->userCard->first();

                    $client = new Client();
                    $url = $base_url."accounts/".decryptData($receiverFirstCard->customer_id, $encrypt_Key)."/transactions";
                    
                    $body = [
                        "transferType" => "WalletToCard",
                        "transferAmount" => round($montant,2),
                        "currencyCode" => "XOF",
                        "last4Digits" => decryptData($receiverFirstCard->last_digits, $encrypt_Key),
                        "referenceMemo" => 'Transfert de '.$montant.' XOF vers la carte principale '.decryptData($receiverFirstCard->last_digits, $encrypt_Key).' de '. $receiver->name.' '.$receiver->lastname.'.'
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
                        $soldeApres = $soldeAvant - $montantWithFee;

                        $transfert->reference_gtp_credit = $referenceGtpCredit;
                        $transfert->solde_avant = $soldeAvant;
                        $transfert->solde_apres = $soldeApres;
                        $transfert->status = 'completed';
                        $transfert->save();

                        $message = 'Transfert de '.$montant.' XOF vers la carte principale '.decryptData($receiverFirstCard->last_digits, $encrypt_Key).' de '. $receiver->name.' '.$receiver->lastname.'.';   
                        if($sender->sms == 1){
                            sendSms($sender->username,$message);
                        }else{
                            $email = $sender->kycClient->email;
                            $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                            Mail::to([$email,])->send(new MailAlerte($arr));
                        }
                    } catch (BadResponseException $e) {
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return sendError($error, [], 500);
                    }

                    return sendResponse($transfert, 'Transfert effectué avec succes.');
                }else if($request->type == 'bmo'){                        
                    try{             
                        $partner_reference = substr($transfert->receveur_telephone, -4).time();
                        $base_url_bmo = env('BASE_BMO');
            
                        $client = new Client();
                        $url = $base_url_bmo."/operations/credit";
                        
                        $body = [
                            "amount" => $transfert->montant,
                            "customer" => [
                                "phone"=> "+".$transfert->receveur_telephone,
                                "firstname"=> $transfert->name,
                                "lastname"=> $transfert->lastname
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
            
                        $resultat_debit_bmo = json_decode($response->getBody());
                        
                        $soldeApres = $soldeAvant - $montantWithFee;
                        $transfert->receveur_telephone = $request->receveur_telephone;
                        $transfert->reference_operateur = $resultat_debit_bmo->reference;
                        $transfert->solde_avant = $soldeAvant;
                        $transfert->solde_apres = $soldeApres;
                        $transfert->status = 'completed';
                        $transfert->save();
            
                    } catch (BadResponseException $e) {
                        return sendError($e->getMessage(), [], 401);
                    }
                }else{
                    try { 
                        $base_url_kkp = env('BASE_KKIAPAY');
            
                        $client = new Client();
                        $url = $base_url_kkp."/api/v1/payments/deposit";
                        
                        $partner_reference = substr($transfert->receveur_telephone, -4).time();
                        $body = [
                            "phoneNumber" => $transfert->receveur_telephone,
                            "amount" => $montant,
                            "reason" => 'Transfert de '.$montant.' provenant de '.$sender->username.' effectué depuis son compte BCB Virtuelle. ID de la carte : '.$sender->customer_id.'.',
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
                            $externalTransaction = resultat_check_status_kkp($resultat->transactionId);
                            if ($externalTransaction->status == "SUCCESS"){
                                $reference_operateur = $externalTransaction->externalTransactionId;
                                $soldeApres = $soldeAvant - $montantWithFee;

                                $transfert->receveur_telephone = $request->receveur_telephone;
                                $transfert->reference_operateur = $reference_operateur;
                                $transfert->solde_avant = $soldeAvant;
                                $transfert->solde_apres = $soldeApres;
                                $transfert->status = 'completed';
                                $transfert->save();
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

                $message = 'Transfert de '.$montant.' vers le numero '.$transfert->receveur_telephone.'.';   
                if($sender->sms == 1){
                    sendSms($sender->username,$message);
                }else{
                    $email = $sender->kycClient->email;
                    $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                    Mail::to([$email,])->send(new MailAlerte($arr));
                }   
                return sendResponse($transfert, 'Transfert effectué avec succes.');       
            }

        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }
    

    private function repartitionCommission($fraisOperation,$frais,$montant,$referenceBcb,$referenceGtp){
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
        }
    }
}
