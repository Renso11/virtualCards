<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserClient;
use App\Models\Info;
use App\Models\GtpRequest;
use App\Models\KycClient;
use App\Models\UserCardBuy;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailAlerteVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\VenteVirtuelle as MailVenteVirtuelle;
use App\Models\Frai;
use App\Models\UserCard;
use App\Models\Service;
use App\Models\Beneficiaire;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Ramsey\Uuid\Uuid;

class CardController extends Controller
{

    public function buyCard(Request $request){
        try{
            $encrypt_Key = env('ENCRYPT_KEY');
            
            $validator = Validator::make($request->all(), [
                'user_id' => ["required" , "string"],
                'transaction_id' => ["required" , "string"],
                'montant' => ["required" , "integer"],
                'type' => ["required" , "max:255", "regex:(kkiapay|bmo)"],
            ]);

            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }

            $user = UserClient::where('id',$request->user_id)->first();

            if($user->verification == 0){
                return response()->json([
                    'message' => 'Ce compte n\'est pas encore validé',
                ], 401);
            }

            $userCardBuy = UserCardBuy::create([
                'id' => Uuid::uuid4()->toString(),
                'moyen_paiement' => $request->type,
                'reference_paiement' => $request->transaction_id,
                'montant' => $request->montant,
                'user_client_id' => $request->user_id,
                'status' => 'pending',
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            if(checkPayment($request->type, $request->transaction_id, $request->montant) == 'bad_amount'){
                $reason = date('Y-m-d h:i:s : Montant incorrecte');
                $userCardBuy->reasons = $reason;
                $userCardBuy->status = 'failed';
                $userCardBuy->save();
                return sendError('Le montant ne correspond pas au montant de la transaction', [], 500);
            }else if(checkPayment($request->type, $request->transaction_id, $request->montant) == 'not_success'){
                $reason = date('Y-m-d h:i:s : Echec du paiement');
                $userCardBuy->reasons = $reason;
                $userCardBuy->status = 'failed';
                $userCardBuy->save();
                return sendError('Le paiement du montant n\'a pas aboutit', [], 500);
            }

            $userCardBuy->is_paid = 1;
            $userCardBuy->save();

            $base_url = env('BASE_GTP_API');
            $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');

            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $client = new Client();
            $url = $base_url."accounts/virtual";
            
            $name = $user->kycClient->name.' '.$user->kycClient->lastname;
            if (strlen($name) > 19){
                $name = substr($name, 0, 19);
            }
            
            $body = [
                "firstName" => $user->kycClient->name,
                "lastName" => $user->kycClient->lastname,
                "preferredName" => unaccent($name),
                "address1" => $user->kycClient->address,
                "city" => $user->kycClient->city,
                "country" => "BJ",
                "stateRegion" => $user->kycClient->departement,
                "birthDate" =>  $user->kycClient->birthday,
                "idType" => $user->kycClient->piece_type,
                "idValue" => $user->kycClient->piece_id,
                "mobilePhoneNumber" => [
                "countryCode" => explode(' ',$user->kycClient->telephone)[0],
                "number" =>  explode(' ',$user->kycClient->telephone)[1],
                ],
                "emailAddress" => $user->kycClient->email,
                "accountSource" => "OTHER",
                "referredBy" => $accountId,
                "subCompany" => $accountId,
                "return" => "RETURNPASSCODE"
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
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                return sendError($error, [], 500);
            }

            $user->save();

            $oldCard = UserCard::where('deleted',0)->where('user_client_id',$user->id)->get();
            $firstly = 0;
            if(count($oldCard) == 0){
                $firstly = 1;
            }

            $card = UserCard::create([
                'id' => Uuid::uuid4()->toString(),
                'user_client_id' => $user->id,
                'last_digits' => encryptData((string)$responseBody->registrationLast4Digits,$encrypt_Key),
                'customer_id' => encryptData((string)$responseBody->registrationAccountId,$encrypt_Key),
                'type' => $request->type,
                'is_first' => $firstly,
                'is_buy' => 1,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $userCardBuy->user_card_id = $card->id;
            $userCardBuy->status = 'completed';
            $userCardBuy->save();

            Mail::to([$user->kycClient->email,])->send(new MailVenteVirtuelle(['registrationAccountId' => $responseBody->registrationAccountId,'registrationLast4Digits' => $responseBody->registrationLast4Digits,'registrationPassCode' => $responseBody->registrationPassCode,'type' => $request->type])); 
            return sendResponse($card, 'Achat terminé avec succes');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function completeBuyCard(Request $request){
        try{
            $encrypt_Key = env('ENCRYPT_KEY');
            
            $validator = Validator::make($request->all(), [
                'transaction_id' => ["required" , "string"],
            ]);

            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }
            
            $userCardBuy = UserCardBuy::where('id',$request->transaction_id)->first();
            $user = UserClient::where('id',$userCardBuy->user_client_id)->first();


            if($user->verification == 0){
                return response()->json([
                    'message' => 'Ce compte n\'est pas encore validé',
                ], 401);
            }
            
            if($userCardBuy->is_paid == 0){
                // Verification paiement
                    if(checkPayment($userCardBuy->moyen_paiement, $userCardBuy->reference_paiement, $userCardBuy->montant) == 'bad_amount'){
                        $reason = date('Y-m-d h:i:s : Montant incorrecte');
                        $userCardBuy->reasons = $reason;
                        $userCardBuy->status = 'failed';
                        $userCardBuy->save();
                        return sendError('Le montant ne correspond pas au montant de la transaction', [], 500);
                    }else if(checkPayment($userCardBuy->moyen_paiement, $userCardBuy->reference_paiement, $userCardBuy->montant)){
                        $reason = date('Y-m-d h:i:s : Echec du paiement');
                        $userCardBuy->reasons = $reason;
                        $userCardBuy->status = 'failed';
                        $userCardBuy->save();
                        return sendError('Le paiement du montant n\'a pas aboutit', [], 500);
                    }
    
                    $userCardBuy->is_paid = 1;
                    $userCardBuy->save();
    
                    $reference = $request->transaction_id;
                // Fin verification paiement
            }

            $base_url = env('BASE_GTP_API');
            $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');

            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $client = new Client();
            $url = $base_url."accounts/virtual";
            
            $name = $user->kycClient->name.' '.$user->kycClient->lastname;
            if (strlen($name) > 19){
                $name = substr($name, 0, 19);
            }
            
            $body = [
                "firstName" => $user->kycClient->name,
                "lastName" => $user->kycClient->lastname,
                "preferredName" => unaccent($name),
                "address1" => $user->kycClient->address,
                "city" => $user->kycClient->city,
                "country" => "BJ",
                "stateRegion" => $user->kycClient->departement,
                "birthDate" =>  $user->kycClient->birthday,
                "idType" => $user->kycClient->piece_type,
                "idValue" => $user->kycClient->piece_id,
                "mobilePhoneNumber" => [
                "countryCode" => explode(' ',$user->kycClient->telephone)[0],
                "number" =>  explode(' ',$user->kycClient->telephone)[1],
                ],
                "emailAddress" => $user->kycClient->email,
                "accountSource" => "OTHER",
                "referredBy" => $accountId,
                "subCompany" => $accountId,
                "return" => "RETURNPASSCODE"
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
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                return sendError($error, [], 500);
            }

            $user->save();

            $oldCard = UserCard::where('deleted',0)->where('user_client_id',$user->id)->get();
            $firstly = 0;
            if(count($oldCard) == 0){
                $firstly = 1;
            }

            $card = UserCard::create([
                'id' => Uuid::uuid4()->toString(),
                'user_client_id' => $user->id,
                'last_digits' => encryptData((string)$responseBody->registrationLast4Digits,$encrypt_Key),
                'customer_id' => encryptData((string)$responseBody->registrationAccountId,$encrypt_Key),
                'type' => $request->type,
                'is_first' => $firstly,
                'is_buy' => 1,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $userCardBuy->user_card_id = $card->id;
            $userCardBuy->status = 'completed';
            $userCardBuy->save();

            Mail::to([$user->kycClient->email,])->send(new MailVenteVirtuelle(['registrationAccountId' => $responseBody->registrationAccountId,'registrationLast4Digits' => $responseBody->registrationLast4Digits,'registrationPassCode' => $responseBody->registrationPassCode,'type' => $request->type])); 
            
            return sendResponse($card, 'Achat terminé avec succes');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function setDefaultCard(Request $request){
        try{
            $encrypt_Key = env('ENCRYPT_KEY');
            
            $validator = Validator::make($request->all(), [
                'card_id' => ["required" , "string"]
            ]);

            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }
            
            $newDefaultCard = UserCard::where('id',$request->card_id)->first();
            $user = UserClient::where('id',$newDefaultCard->user_client_id)->where('deleted',0)->first();

            foreach($user->userCards as $item){
                $item->is_first = 0;
                $item->save();
            }
            $newDefaultCard->is_first = 1;
            $newDefaultCard->save();

            return sendResponse($newDefaultCard, 'Carte defini avec success');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function liaisonCarte(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'customer_id' => 'required|unique:user_cards',
                'last_digits' => 'required|unique:user_cards',
                'type' => 'required',
                'mobile_phone_number' => 'required'
            ]);

            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }

            $encrypt_Key = env('ENCRYPT_KEY');
            
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            
            try {
                $client = new Client();
                $url = $base_url."accounts/".$request->customer_id;
            
                $headers = [
                    'programId' => $programID,
                    'requestId' => Uuid::uuid4()->toString(),
                ];
            
                $auth = [
                    $authLogin,
                    $authPass
                ];
                $response = $client->request('GET', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                ]);
            
                $clientInfo = json_decode($response->getBody());

                if($clientInfo->cardStatus == 'LC'){
                    return sendError('Cette carte est pour le moment bloqué. Veuillez contacter le service clientèle', [], 403);
                }
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return sendError($error, [], 500);
            }

            
            try {
                $client = new Client();
                $url = $base_url."accounts/phone-number";
    
                $headers = [
                    'programId' => $programID,
                    'requestId' => Uuid::uuid4()->toString()
                ];
        
                $query = [
                    'phoneNumber' => $request->mobile_phone_number
                ];
        
                $auth = [
                    $authLogin,
                    $authPass
                ];

                $response = $client->request('GET', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                    'query' => $query
                ]);
                
                $accountInfo = [];
                $accountInfoLists = json_decode($response->getBody())->accountInfoList;
                //return $response->getBody();

                foreach ($accountInfoLists as $value) {
                    if($value->accountId == $request->customer_id && $value->lastFourDigits == $request->last_digits){
                        $accountInfo = $value;
                        break;
                    }else{
                        return sendError('Les 4 derniers chiffres ne correspondent pas a l\'ID', [], 403);
                    }
                }
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());   
                $error = $json->title.'.'.$json->detail;
                return sendError($error, [], 500);
            }

            
            $user = UserClient::where('id',$request->user_id)->first();

            $oldCard = UserCard::where('deleted',0)->where('user_client_id',$user->id)->get();
            $firstly = 0;

            $card = UserCard::create([
                'id' => Uuid::uuid4()->toString(),
                'user_client_id' => $user->id,
                'customer_id' => encryptData((string)$request->customer_id,$encrypt_Key),
                'last_digits' => encryptData((string)$request->last_digits,$encrypt_Key),
                'type' => $request->type,
                'is_first' => $firstly,
                'is_buy' => 0,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            return sendResponse($card, 'Liaison effectuée avec succes');
            
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function getUserCards(Request $request){
        $nb_card = UserCard::where('user_client_id',$request->id)->orderBy('created_at','DESC')->count();
        $cards = UserCard::where('user_client_id',$request->id)->get();
        
        $data['cards'] = $cards;
        
        $info_card = Info::where('deleted',0)->first();

        $data['infos'] =  [
            'nb_card' => $nb_card,
            'max_card' => $info_card ? $info_card->card_max : 5,
            'price_card' => $info_card ? $info_card->card_price : 0
        ];
        return sendResponse($data, 'Liste de carte.');
    }

    public function getCardsInfos(Request $request){
        $nb_card = UserCard::where('user_client_id',$request->id)->orderBy('created_at','DESC')->count();
        $cards = UserCard::where('user_client_id',$request->id)->get();
        $info_card = Info::where('deleted',0)->first();

        $data =  [
            'nb_card' => $nb_card,
            'max_card' => $info_card ? $info_card->card_max : 5,
            'price_card' => $info_card ? $info_card->card_price : 0
        ];
        return sendResponse($data, 'Carte infos.');
    }
    
    public function getCardInfo(Request $request){
        $card = UserCard::where('id',$request->id)->first();
        $card->info = getCarteInformation((string)$card->customer_id, 'all');
        return sendResponse($card, 'Carte.');
    }

    public function getAccountInfo(Request $request){
        $card = UserCard::where('id',$request->id)->first();
        $card->accountInfo = getCarteInformation((string)$card->customer_id, 'accountInfo');
        return sendResponse($card, 'Carte.');
    }

    public function getBalance(Request $request){
        $card = UserCard::where('id',$request->id)->first();
        $encrypt_Key = env('ENCRYPT_KEY');
        $card->balance = encryptData((string)getCarteInformation((string)$card->customer_id, 'balance'),$encrypt_Key);
        return sendResponse($card, 'Carte.');
    }

    public function getClientInfo(Request $request){
        $card = UserCard::where('id',$request->id)->first();
        $card->clientInfo = getCarteInformation((string)$card->customer_id, 'clientInfo');
        return sendResponse($card, 'Carte.');
    }

    public function changeCardStatus(Request $request){
        try {
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $validator = Validator::make($request->all(), [
                'code' => 'required',
                'telephone' => 'required',
                'last' => 'required',
                'status' => 'required'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }   

            $client = new Client();
            $url = $base_url."accounts/".$request->code."/status";
            
            $body = [
                "last4Digits" => $request->last,
                "mobilePhoneNumber" => $request->telephone,
                "newCardStatus" => $request->status
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
                $response = $client->request('PATCH', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                    'body' => $body,
                    'verify'  => false,
                ]);

                $resultat = json_decode($response->getBody());
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return sendError($error, [], 500);
            }
            if($request->status == "Active"){
                $message = "Déverouillage effectué avec succes";
            }else{
                $message = "Verouillage effectué avec succes";
            }

            return sendResponse([], $message);            
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }
}
