<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserClient;
use App\Models\Info;
use App\Models\GtpRequest;
use App\Models\Retrait;
use App\Models\Departement;
use App\Models\Commission;
use App\Models\AccountCommission;
use App\Models\AccountCommissionOperation;
use App\Models\AccountDistribution;
use App\Models\AccountDistributionOperation;
use App\Models\Depot;
use App\Models\Gamme;
use App\Models\CarteVirtuelle;
use App\Models\CartePhysique;
use App\Models\Recharge;
use App\Models\KycClient;
use App\Models\UserCardBuy;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationOtp;
use App\Mail\MailAlerteVerification;
use App\Mail\MailAlerte;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Ixudra\Curl\Facades\Curl;
use App\Mail\AlerteRecharge;
use App\Mail\VentePhysique as MailVentePhysique;
use App\Mail\VenteVirtuelle as MailVenteVirtuelle;
use App\Mail\CodeValidationRetrait;
use App\Mail\CodeValidationTransfert;
use App\Models\Frai;
use App\Models\SelfRetrait;
use App\Models\TransfertIn;
use App\Models\TransfertOut;
use App\Models\UserCard;
use App\Models\Service;
use App\Models\Beneficiaire;
use App\Models\BeneficiaireBcv;
use App\Models\BeneficiaireCard;
use App\Models\BeneficiaireMomo;
use App\Models\EntityAccountCommission;
use App\Models\EntityAccountCommissionOperation;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Ramsey\Uuid\Uuid;

class RetraitController extends Controller
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

            //recuperation solde avant et apres
                $soldeAvantRetrait = getUserSolde($retrait->userClient->id);
                
            // validation du retrait

                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

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
                
                    $responseAfterRetrait = json_decode($response->getBody());
                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return sendError($error, [], 500);
                }
                $soldeApresRetrait = $soldeAvantRetrait - $retrait->montant - $retrait->frais_bcb;

                $montant_recu = $retrait->montant;
    
            //Determination commissions

                $com = Commission::where('deleted',0)->where('type_operation','retrait')->orderBy('created_at','DESC')->first();
                    // frais specific a un partenaire et restriction specific
                $commission = 0;
                if($com){
                    if($com->type == 'Taux pourcentage'){
                        $commission = $retrait->frais * $com->value / 100;
                    }else{
                        $commission = $com->value;
                    }
                }

            //Incrémentation du compte de distribution
                $distribution_account = AccountDistribution::where('partenaire_id',$retrait->userPartenaire->partenaire->id)->where('deleted',0)->first();

                AccountDistributionOperation::create([
                    'id' => Uuid::uuid4()->toString(),
                    'solde_avant' => $distribution_account->solde,
                    'montant' => $retrait->montant,
                    'solde_apres' => $distribution_account->solde + $retrait->montant,
                    'libelle' => 'Retrait effectué sur le compte '.$retrait->userClient->telephone,
                    'type' => 'credit',
                    'account_distribution_id' => $distribution_account->id,
                    'deleted' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $distribution_account->solde += $montant_recu;
                $distribution_account->save();    
            // 
            //Incrémentation du compte commission    
                if($commission > 0){
                    $commission_account = AccountCommission::where('partenaire_id',$retrait->userPartenaire->partenaire->id)->where('deleted',0)->first();
    
                    AccountCommissionOperation::create([
                        'id' => Uuid::uuid4()->toString(),
                        'solde_avant' => $commission_account ? $commission_account->solde : 0,
                        'montant' => $commission,
                        'solde_apres' => $commission_account ? $commission_account->solde + $commission : $commission,
                        'libelle' => 'Commission de retrait sur le compte '.$retrait->userClient->telephone,
                        'type' => 'credit',
                        'account_commission_id' => $commission_account->id,
                        'deleted' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    $commission_account->solde += $commission;
                    $commission_account->save();                        
                }
            //

            //finalisation du retrait
                $retrait->status = 'completed';
                $retrait->solde_avant = $soldeAvantRetrait;
                $retrait->solde_apres = $soldeApresRetrait;
                
                $retrait->reference_gtp = $responseAfterRetrait->transactionId;
                $retrait->montant_recu = $montant_recu;
                $retrait->save();
            
            $message = 'Vous avez validé un retrait de '.$retrait->montant.' XOF sur votre carte '.$clientCode.'. Partenaire : '.$retrait->partenaire->libelle.'. Frais d\'operation : '.$retrait->frais.' XOF. Montant reçu :'.$montant_recu.' Votre nouveau solde est :'.$soldeApresRetrait.' XOF.';
            
            if($retrait->userClient->sms == 1){
                sendSms($retrait->userClient->username,$message);
            }else{
                $arr = ['messages'=> $message,'objet'=>'Confirmation du retrait','from'=>'noreply-bcv@bestcash.me'];
                Mail::to([$retrait->userClient->kycClient->email,])->send(new MailAlerte($arr));
            }

            $message = $retrait->userClient->name.' '.$retrait->userClient->lastname.' - Tel : '.$retrait->userClient->username.' a validé le retrait de '.$retrait->montant.'. Frais d\'operation : '.$retrait->frais.' XOF. Montant reçu par le client :'.$montant_recu.' Commission de l\'operation : '.$commission.' XOF.';
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
}
