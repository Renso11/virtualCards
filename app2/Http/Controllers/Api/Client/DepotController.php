<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserClient;
use App\Models\Recharge;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailAlerte;
use App\Models\CompteCommission;
use App\Models\CompteCommissionOperation;
use Illuminate\Support\Facades\Validator;
use App\Models\UserCard;
use Ramsey\Uuid\Uuid;

class DepotController extends Controller
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

    public function addNewDepotClient(Request $request){
        try {
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');
            $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');

            $validator = Validator::make($request->all(), [
                'user_id' => ["required" , "string"],
                'user_card_id' => 'required',
                'reference' => ["required" , "string"],
                'montant' => ["required" , "integer"],
                'moyen_paiement' => ["required" , "max:255", "regex:(momo|flooz|bmo)"],
            ]);

            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }

            $user = UserClient::where('id',$request->user_id)->first();
            $card = UserCard::where('id',$request->user_card_id)->first();

            $montant = $request->montant;
            $fraisAndRepartition = getFeeAndRepartition('rechargement', $montant);
            $soldeAvantDepot = getUserSolde($user->id);

            $frais = 0;
            if($fraisAndRepartition){
                if($fraisAndRepartition->type == 'pourcentage'){
                    $frais = $montant * $fraisAndRepartition->value / 100;
                }else{
                    $frais = $fraisAndRepartition->value;
                }
            }

            $montantWithoutFee = $montant - $frais;
            
            $recharge = Recharge::create([
                'id' => Uuid::uuid4()->toString(),
                'user_client_id' => $request->user_id,
                'user_card_id' => $request->user_card_id,
                'montant' => $request->montant,
                'frais_bcb' => $frais,
                'montant_recu' => $montantWithoutFee,
                'reference_operateur' => $request->reference,
                'moyen_paiement' => $request->moyen_paiement,
                'status' => 'pending',
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);            

            if(checkPayment($request->moyen_paiement, $request->reference, $request->montant) == 'bad_amount'){
                $reason = date('Y-m-d h:i:s : Montant incorrecte');
                $recharge->reasons = $reason;
                $recharge->status = 'failed';
                $recharge->save();
                return sendError('Le montant ne correspond pas au montant de la transaction', [], 500);
            }else if(checkPayment($request->moyen_paiement, $request->reference, $request->montant) == 'not_success'){
                $reason = date('Y-m-d h:i:s : Echec du paiement');
                $recharge->reasons = $reason;
                $recharge->status = 'failed';
                $recharge->save();
                return sendError('Le paiement du montant n\'a pas aboutit', [], 500);
            }                
            $recharge->is_paid = 1;
            $recharge->save();

            $client = new Client();
            $encrypt_Key = env('ENCRYPT_KEY');
            $url =  $base_url."accounts/".decryptData((string)$card->customer_id, $encrypt_Key)."/transactions";
            
            $body = [
                "transferType" => "WalletToCard",
                "transferAmount" => round($montantWithoutFee,2),
                "currencyCode" => "XOF",
                "referenceMemo" => "Rechargement de ".$montant." XOF sur votre carte. Frais de rechargement : ".$frais." XOF",
                "last4Digits" => decryptData((string)$card->last_digits, $encrypt_Key)
            ];

            $body = json_encode($body);
            
            $headers = [
                'programId' => $programID,
                'requestId' => Uuid::uuid4()->toString(),
                'accountId' => $accountId,
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

                $referenceBcb = 'rec-'.Uuid::uuid4()->toString();
                $referenceGtp = $responseBody->transactionId;
                
                $soldeApresDepot = $soldeAvantDepot + $montantWithoutFee;
                
                $recharge->reference_gtp = $referenceGtp;
                $recharge->frais_bcb = $frais;
                $recharge->montant_recu = $montantWithoutFee;
                $recharge->status =  'completed';
                $recharge->solde_avant = $soldeAvantDepot;
                $recharge->solde_apres = $soldeApresDepot;
                $recharge->reference_bcb = $referenceBcb;
                
                $message = 'Vous avez faire un rechargement de '.$montant.' XOF sur votre compte BCB Virtuelle. Frais de rechargement Votre nouveau solde est: '.$soldeApresDepot.' XOF.';
                
                if($user->sms == 1){
                    sendSms($user->username,$message);
                }else{
                    $arr = ['messages'=> $message,'objet'=>'Confirmation du rechargement','from'=>'noreply-bcv@bestcash.me'];
                    Mail::to([$user->kycClient->email,])->send(new MailAlerte($arr));
                }
                
                $this->repartitionCommission($fraisAndRepartition,$frais,$montant,$referenceBcb,$referenceGtp);
                
                $recharge->save();

            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return sendError($error, [], 500);
            }

            return sendResponse($recharge, 'Rechargement effectué avec succes. Consulter votre solde');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function completeDepotClient(Request $request){
        try {
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');
            $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');

            $validator = Validator::make($request->all(), [
                'transaction_id' => ["required" , "string"]
            ]);

            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }
            
            $recharge = Recharge::where('id',$request->transaction_id)->first();

            $user = UserClient::where('id',$recharge->user_client_id)->first();
            $card = UserCard::where('id',$recharge->user_card_id)->first();

            
            if($recharge->is_paid == 0){
                if(checkPayment($recharge->moyen_paiement, $recharge->reference_operateur, $recharge->montant) == 'bad_amount'){
                    $reason = date('Y-m-d h:i:s : Montant incorrecte');
                    $recharge->reasons = $reason;
                    $recharge->status = 'failed';
                    $recharge->save();
                    return sendError('Le montant ne correspond pas au montant de la transaction', [], 500);
                }else if(checkPayment($recharge->moyen_paiement, $recharge->reference_operateur, $recharge->montant) == 'not_success'){
                    $reason = date('Y-m-d h:i:s : Echec du paiement');
                    $recharge->reasons = $reason;
                    $recharge->status = 'failed';
                    $recharge->save();
                    return sendError('Le paiement du montant n\'a pas aboutit', [], 500);
                }
                
                $recharge->is_paid = 1;
                $recharge->save();
            }

            $soldeAvantDepot = getUserSolde($user->id);

            $montant = $recharge->montant;
            $fraisAndRepartition = getFeeAndRepartition('rechargement', $montant);

            $frais = 0;
            if($fraisAndRepartition){
                if($fraisAndRepartition->type == 'pourcentage'){
                    $frais = $montant * $fraisAndRepartition->value / 100;
                }else{
                    $frais = $fraisAndRepartition->value;
                }
            }

            $montantWithoutFee = $montant - $frais;

            $client = new Client();
            $encrypt_Key = env('ENCRYPT_KEY');
            $url =  $base_url."accounts/".decryptData((string)$card->customer_id, $encrypt_Key)."/transactions";
            
            $body = [
                "transferType" => "WalletToCard",
                "transferAmount" => round($montantWithoutFee,2),
                "currencyCode" => "XOF",
                "referenceMemo" => "Rechargement de ".$montant." XOF sur votre carte. Frais de rechargement : ".$frais." XOF",
                "last4Digits" => decryptData((string)$card->last_digits, $encrypt_Key)
            ];

            $body = json_encode($body);
            
            $headers = [
                'programId' => $programID,
                'requestId' => Uuid::uuid4()->toString(),
                'accountId' => $accountId,
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

                $referenceBcb = 'rec-'.Uuid::uuid4()->toString();
                $referenceGtp = $responseBody->transactionId;                
                $soldeApresDepot = $soldeAvantDepot + $montantWithoutFee;
                
                $recharge->reference_gtp = $referenceGtp;
                $recharge->frais_bcb = $frais;
                $recharge->montant_recu = $montantWithoutFee;
                $recharge->status =  'completed';
                $recharge->solde_avant = $soldeAvantDepot;
                $recharge->solde_apres = $soldeApresDepot;
                $recharge->reference_bcb = $referenceBcb;
                $recharge->save();
                
                $message = 'Vous avez faire un rechargement de '.$montant.' XOF sur votre compte BCB Virtuelle. Frais de rechargement Votre nouveau solde est: '.$soldeApresDepot.' XOF.';
                
                if($user->sms == 1){
                    sendSms($user->username,$message);
                }else{
                    $arr = ['messages'=> $message,'objet'=>'Confirmation du rechargement','from'=>'noreply-bcv@bestcash.me'];
                    Mail::to([$user->kycClient->email,])->send(new MailAlerte($arr));
                }

                $this->repartitionCommission($fraisAndRepartition,$frais,$montant,$referenceBcb,$referenceGtp);

            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return sendError($error, [], 500);
            }

            return sendResponse($recharge, 'Rechargement effectué avec succes. Consulter votre solde');
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
