<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GtpRequest;
use App\Models\Retrait;
use App\Models\Commission;
use App\Models\AccountCommission;
use App\Models\AccountCommissionOperation;
use App\Models\AccountDistribution;
use App\Models\AccountDistributionOperation;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailAlerte;
use App\Models\CompteCommission;
use App\Models\CompteCommissionOperation;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Ramsey\Uuid\Uuid;

class RetraitController extends Controller
{
    public function __construct() {
        $this->middleware('is-auth', ['except' => ['addContact','createCompteClient', 'loginCompteClient', 'sendCode', 'checkCodeOtp', 'resetPassword','verificationPhone', 'verificationInfoPerso','verificationInfoPiece','saveFile','sendCodeTelephoneRegistration','getServices','sendCodeTelephone']]);
    }

    public function getClientPendingWithdraws(Request $request){
        $withdrawls = Retrait::where('deleted',0)->where('user_client_id',$request->id)->where('status','pending')->get();
        return sendResponse($withdrawls, 'Liste transactions.');
    }
    
    public function validationRetraitAttenteClient(Request $request){
        try {
            $encrypt_Key = env('ENCRYPT_KEY');
                
            $validator = Validator::make($request->all(), [
                'user_card_id' => 'required',
                'transaction_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }

            $token = JWTAuth::getToken();
            $userId = JWTAuth::getPayload($token)->toArray()['sub'];

      
            $retrait = Retrait::where('id',$request->transaction_id)->where('deleted',0)->where('status',0)->first();
            
            if($userId != $retrait->user_client_id){
                return  sendError('Vous n\'etes pas autorisé à faire cette opération', [$userId,$retrait->user_client_id],401);
            }

            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $clientCode = $retrait->userCard->customer_id;
            
            $soldeAvantRetrait = getUserSolde($retrait->userClient->id);
            
            $fraisAndRepartition = getFeeAndRepartition('retrait', $retrait->montant);
            $frais = 0;
            if($fraisAndRepartition){
                if($fraisAndRepartition->type == 'pourcentage'){
                    $frais = $retrait->montant * $fraisAndRepartition->value / 100;
                }else{
                    $frais = $fraisAndRepartition->value;
                }
            }

            $client = new Client();
            $url = $base_url."accounts/".decryptData($retrait->userCard->customer_id, $encrypt_Key)."/transactions";
            
            $body = [
                "transferType" => "CardToWallet",
                "transferAmount" => $retrait->montant+$retrait->frais_bcb,
                "currencyCode" => "XOF",
                "referenceMemo" => "Retrait de ".$retrait->montant+$retrait->frais_bcb." XOF",
                "last4Digits" => decryptData($retrait->userCard->last_digits, $encrypt_Key)
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
            
                $responseBody = json_decode($response->getBody());

                $referenceGtp = $responseBody->transactionId;

                $comptePartenaire = AccountDistribution::where('partenaire_id',$retrait->userPartenaire->partenaire->id)->where('deleted',0)->first();

                AccountDistributionOperation::create([
                    'id' => Uuid::uuid4()->toString(),
                    'solde_avant' => $comptePartenaire->solde,
                    'montant' => $retrait->montant,
                    'solde_apres' => $comptePartenaire->solde + $retrait->montant,
                    'libelle' => 'Retrait effectué sur le compte '.$retrait->userClient->telephone,
                    'type' => 'credit',
                    'account_distribution_id' => $comptePartenaire->id,
                    'deleted' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $soldeApresRetrait = $soldeAvantRetrait - $retrait->montant - $retrait->frais_bcb;
                $retrait->status = 'completed';
                $retrait->solde_avant = $soldeAvantRetrait;
                $retrait->solde_apres = $soldeApresRetrait;                
                $retrait->reference_gtp = $responseBody->transactionId;
                $retrait->save();

                $comptePartenaire->solde += $retrait->montant;
                $comptePartenaire->save(); 

            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return sendError($error, [], 500);
            }
            
            $compteCommissionPartenaire = AccountCommission::where('partenaire_id',$retrait->userPartenaire->partenaire->id)->where('deleted',0)->first();
            $this->repartitionCommission($compteCommissionPartenaire,$comptePartenaire,$fraisAndRepartition,$frais,$retrait->montant,$retrait->referenceBcb,$referenceGtp);

            $message = 'Vous avez validé un retrait de '.$retrait->montant.' XOF sur votre carte '.$clientCode.'. Partenaire : '.$retrait->partenaire->libelle.'. Frais d\'operation : '.$retrait->frais.' XOF. Montant reçu :'.$retrait->montant.' Votre nouveau solde est :'.$soldeApresRetrait.' XOF.';
            
            if($retrait->userClient->sms == 1){
                sendSms($retrait->userClient->username,$message);
            }else{
                $arr = ['messages'=> $message,'objet'=>'Confirmation du retrait','from'=>'noreply-bcv@bestcash.me'];
                Mail::to([$retrait->userClient->kycClient->email,])->send(new MailAlerte($arr));
            }

            $message = $retrait->userClient->name.' '.$retrait->userClient->lastname.' - Tel : '.$retrait->userClient->username.' a validé le retrait de '.$retrait->montant.'. Frais d\'operation : '.$retrait->frais.' XOF. Montant reçu par le client : '.$retrait->montant.'.';
            sendSms($retrait->userPartenaire->partenaire->telephone,$message);
            
            return sendResponse($retrait, 'Votre opération de retrait a été confirmé avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }
    
    public function annulationRetraitAttenteClient(Request $request){
        try {
            $encrypt_Key = env('ENCRYPT_KEY');
                
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }

            $token = JWTAuth::getToken();
            $userId = JWTAuth::getPayload($token)->toArray()['sub'];

      
            $retrait = Retrait::where('id',$request->transaction_id)->where('deleted',0)->where('status','pending')->first();
            
            if($userId != $retrait->user_client_id){
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
                
                
                $soldeApIncr = $compteCommissionPartenaire->solde + $commissionPartenaire;
    
                AccountCommissionOperation::insert([
                    'id' => Uuid::uuid4()->toString(),
                    'reference_bcb'=> $referenceBcb,
                    'reference_gtp'=> $referenceGtp,
                    'solde_avant' => $soldeAvIncr,
                    'montant' => $commissionPartenaire,
                    'solde_apres' => $soldeApIncr,
                    'libelle' => 'Commission sur retrait',
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
