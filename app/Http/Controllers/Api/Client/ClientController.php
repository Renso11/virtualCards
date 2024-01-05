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
use App\Models\Recharge;
use App\Models\TransfertOut;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Ramsey\Uuid\Uuid;

class ClientController extends Controller
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

    public function initiationBmo(Request $request){
        try{ 
            $amount = (int)$request->amount;
            $telephone = $request->telephone; //"22962617848";

            $partner_reference = substr($telephone, -4).time();
            
            $base_url_bmo = env('BASE_BMO');

            // Realisation de la transaction

            $client = new Client();
            $url = $base_url_bmo."/operations/transfert-collect";
            
            $body = [
                "amount" => $amount,
                "customer" => ["phone" => "+".$telephone],
                "receiver" => ["phone" => env('COMPTE_DEBPOT_BMO')],
                "partnerReference" => $partner_reference,
                "reason" => "",
                "idType" => "",
                "idNumber" => "",
                "cardExpiration" => "" 
            ];

            $body = json_encode($body);
    
            $headers = [
                'X-Auth-ApiKey' => env('APIKEY_BMO'),
                'X-Auth-ApiSecret' => env('APISECRET_BMO'),
                'Content-Type' => 'application/json', 'Accept' => 'application/json'
            ];
    
            $response = $client->request('POST', $url, [
                'headers' => $headers,
                'body' => $body,
                'verify'  => false,
            ]);

            $resultat_debit_bmo = json_decode($response->getBody());
            return sendResponse($resultat_debit_bmo, 'Operation initié avec succes.');

        } catch (BadResponseException $e) {
            return sendError($e->getMessage(), [], 403);
        }
    }

    public function confirmationBmo(Request $request){
        try{             
            $base_url_bmo = env('BASE_BMO');

            $client = new Client();
            $url = $base_url_bmo."/operations-collect/confirm";
            
            $body = [
                "operation" => $request->operation,
                "confirmationCode" => $request->code,
            ];

            $body = json_encode($body);
    
            $headers = [
                'X-Auth-ApiKey' => env('APIKEY_BMO'),
                'X-Auth-ApiSecret' => env('APISECRET_BMO'),
                'Content-Type' => 'application/json', 'Accept' => 'application/json'
            ];
    
            $response = $client->request('POST', $url, [
                'headers' => $headers,
                'body' => $body,
                'verify'  => false,
            ]);

            $resultat_debit_bmo = json_decode($response->getBody());
            
            return sendResponse($resultat_debit_bmo, 'Operation confirmée avec succes.');

        } catch (BadResponseException $e) {
            return sendError($e->getMessage(), [], 401);
        }
    }

    public function getKkpInfos(){
        try {            
            $encrypt_Key = env('ENCRYPT_KEY');
            $key = encryptData((string)env('API_KEY_KKIAPAY'),$encrypt_Key);

            $data['kkiapayApiKey'] = $key;
            $data['kkiapaySandBox'] = 1;
            $data['kkiapayTheme'] = '#000000';
            return sendResponse($data, 'Api Kkiapay.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function getCompteClientInfo(Request $request){
        try {            
            $encrypt_Key = env('ENCRYPT_KEY');
            $client = UserClient::where('deleted',0)->where('username',$request->username)->first();

            if(!$client){
                return sendError('Ce compte client n\'exite pas. Verifiez le numero de telephone et recommencez');
            }else{
                if($client->status == 0){
                    return sendError('Ce compte client est inactif');
                }
                if($client->verification == 0){
                    return sendError('Ce compte client n\'est pas encore verifié');
                }
            }

            $data = [
                'name' => $client->name,
                'lastname' => $client->lastname,
                'main_card_balance' => encryptData((string)getCarteInformation((string)$client->userCard->first()->customer_id, 'balance'),$encrypt_Key),
                'main_card_last_digits' => $client->userCard->first()->last_digits,
                'is_active' => $client->status,
                'is_valid' => $client->verification,
            ];

            return sendResponse($data, 'Info.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function getServices(){
        try {            
            $services = Service::where('deleted',0)->where('type','client')->get();
            return sendResponse($services, 'Modules.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function getFees(){
        try {
            $frais = Frai::where('deleted',0)->orderBy('created_at','DESC')->get();            
            return sendResponse($frais, 'Frais.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    // A coder de façon dynamique
    public function getMobileWallet(){
        try {            
            $mobileWallets = [
                [
                    'libelle' => 'MTN MOBILE MONEY',
                    'tag' => 'momo',
                    'logo' => '/storage/logowallet/momo.svg',
                    'countries' => 'bj,ci',
                    'is_available' => 1,
                ],
                [
                    'libelle' => 'MOOV MONEY',
                    'tag' => 'flooz',
                    'logo' => '/storage/logowallet/flooz.svg',
                    'countries' => 'bj,tg,ci',
                    'is_available' => 1,
                ],
                [
                    'libelle' => 'ORANGE MONEY',
                    'tag' => 'orange',
                    'logo' => '/storage/logowallet/orange.svg',
                    'countries' => 'sn,ci',
                    'is_available' => 1,
                ],
                [
                    'libelle' => 'TMONEY',
                    'tag' => 'tmoney',
                    'logo' => '/storage/logowallet/tmoney.svg',
                    'countries' => 'tg',
                    'is_available' => 1,
                ],
                [
                    'libelle' => 'CELTIIS CASH',
                    'tag' => 'celtiis',
                    'logo' => '/storage/logowallet/celtiis-cash.svg',
                    'countries' => 'bj',
                    'is_available' => 1,
                ],
                [
                    'libelle' => 'FREE MONEY',
                    'tag' => 'free',
                    'logo' => '/storage/logowallet/free.svg',
                    'countries' => 'sn',
                    'is_available' => 1,
                ],
            ];
            
            return sendResponse($mobileWallets, 'Frais.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }



    public function createCompteClient(Request $request){
        try {            
            $validator = Validator::make($request->all(), [
                'username' => 'required|unique:user_clients',
                'phone_code' => 'required',
                'phone' => 'required',
                'password' => 'required|min:8'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }

            $req = $request->all();
            $code = rand(1000,9999);
            $user = UserClient::create([
                'id' => Uuid::uuid4()->toString(),
                'username' => $req['username'],
                'password' => Hash::make($req['password']),
                'phone_code' => $req['phone_code'],
                'phone' => $req['phone'],
                'status' => 1,
                'double_authentification' => 0,
                'sms' => 0,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            
            $user->makeHidden(['password','code_otp']);

            return sendResponse($user, 'Compte crée avec succès.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function loginCompteClient(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|int',
                'password' => 'required|string|min:8',
            ]);
    
            if ($validator->fails())
            {
                return response()->json([
                    "error" => $validator->errors()->first()
                ], 422);
            }
    
            $user = UserClient::where('username', $request->username)->first();
    
            if ($user) {
                if($user->status == 0){
                    return response()->json([
                        'message' => 'Ce compte est désactivé',
                    ], 401);
                }
    
                $credentials = $request->only('username', 'password');
                $token = Auth::guard('apiUser')->attempt($credentials);
        
                if (!$token) {
                    return response()->json([
                        'message' => 'Informations de connexion incorrectes',
                    ], 401);
                }
    
                $user->last_connexion = Carbon::now();
                $user->save();

                $user->kycClient;
                $user->makeHidden(['password','code_otp']);
                $modules = Service::where('deleted',0)->where('type','client')->get();
                $frais = Frai::where('deleted',0)->orderBy('created_at','DESC')->get();

                return response()->json([
                    'user' => $user,
                    'services' => $modules,
                    'fees' => $frais,
                    'authorization' => [
                        'token' => $token,
                        'type' => 'bearer',
                    ]
                ],200);
            } else {
                return response()->json([
                    "message" =>'L\'utilisateur n\'existe pas'
                ], 422);
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }
    
    public function sendCode(Request $request){
        try {
            $user = UserClient::where('id',$request->id)->first();         

            if(!$user){
                return sendError('L\'utilisateur n\'existe pas', [], 404);
            }

            $code = rand(1000,9999);

            $user->code_otp = $code;
            $user->save();

            $message = 'Votre code OTP de connexion est '.$code.' .';
            sendSms($user->username,$message);
            return sendResponse([], 'Code envoyé avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }
    
    public function sendCodeTelephone(Request $request){
        try {            
            $user = UserClient::where('username',$request->telephone)->first();

            if(!$user){
                return sendError('L\'utilisateur n\'existe pas', [], 404);
            }

            $code = rand(1000,9999);
            $user->code_otp = $code;
            $user->save();

            $message = 'Votre code OTP de connexion est '.$code.' .';
            sendSms($user->username,$message);
            return sendResponse([], 'Code envoyé avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }
    
    public function sendCodeTelephoneRegistration(Request $request){
        try {            
            $user = UserClient::where('username',$request->telephone)->first();

            if($user){
                return sendError('Le compte client existe déjà', [], 409);
            }

            $code = rand(1000,9999);

            $message = 'Votre code OTP de verification est '.$code.' .';
            sendSms($request->telephone,$message);
            

            return sendResponse($code, 'Code envoyé avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function checkCodeOtp(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'code' => 'required|string',
            ]);

            if ($validator->fails())
            {
                return response()->json([
                    "error" => $validator->errors()->first()
                ], 422);
            }

            $user = UserClient::where('id',$request->user_id)->first();
            
            if($user->code_otp != $request->code){
                return sendError('Code OTP incorrect', [], 401);
            }

            return sendResponse([], 'Vérification effectuée avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }
    
    public function resetPassword(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|int',
                'phone' => 'required|int',
                'phone_code' => 'required|int',
                'password' => 'required|min:8'
            ]);

            if ($validator->fails())
            {
                return response()->json([
                    "error" => $validator->errors()->first()
                ], 422);
            }

            $user = UserClient::where('username',$request->phone_code.$request->phone)->first();

            if(!$user){
                return sendError('L\'utilisateur n\'existe pas', [], 401);
            }

            if($user->code_otp != $request->code){
                return sendError('Code OTP incorrect', [], 401);
            }
            $user->password = Hash::make($request->password);
            $user->save();
            return sendResponse([], 'Mot de passe modifé avec succès');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }
    
    public function configPin(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string',
                'pin' => 'required|string',
            ]);

            if ($validator->fails())
            {
                return response()->json([
                    "error" => $validator->errors()->first()
                ], 422);
            }

            $user = UserClient::where('id',$request->user_id)->first();

            if(!$user){
                return sendError('L\'utilisateur n\'existe pas', [], 401);
            }
            

            $user->pin = $request->pin;
            $user->save();
            $user->kycClient;
            $user->makeHidden(['password','code_otp']);
            return sendResponse($user, 'PIN configurer avec succes');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        }
    }

    public function getCompteClient(Request $request){
        try {            
            $token = JWTAuth::getToken();
            $userId = JWTAuth::getPayload($token)->toArray()['sub'];

            $user = UserClient::where('id',$userId)->first();
            $user->kycClient;
            $user->makeHidden(['password','code_otp']);

            return response()->json(compact('user'));
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function verificationPhone(Request $request){
        try {
            $data = [];

            $user = UserClient::where('id',$request->user_id)->where('deleted',0)->first();

            if(!$user){
                return sendError('L\'utilisateur n\'existe pas', [], 404);
            }

            $user->verification_step_one = 1;
            $user->updated_at = carbon::now();
            
            $kyc = KycClient::create([
                'id' => Uuid::uuid4()->toString(),
                'telephone' => $user->phone_code.' '.$user->phone,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            $user->kyc_client_id = $kyc->id;
            $user->save();

            $data['user'] = $user;
            $data['kyc'] = $kyc;

            return sendResponse($data, 'Numero de telephone vérifié avec succes.');            
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function verificationInfoPerso(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user' => 'required',
                'email' => 'required',
                'name' => 'required',
                'lastname' => 'required',
                'birthday' => 'required',
                'country' => 'required',
                'departement' => 'required',
                'city' => 'required',
                'address' => 'required',
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['user'])->where('deleted',0)->first();
            if($user->kycClient == null){
                $kyc = KycClient::create([
                        'id' => Uuid::uuid4()->toString(),
                    'name' => $req['name'],
                    'lastname' => $req['lastname'],
                    'email' => $req['email'],
                    'birthday' => $req['birthday'],
                    'country' => $req['country'],
                    'departement' => $req['departement'],
                    'city' => $req['city'],
                    'address' => $req['address'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                $user->kyc_client_id = $kyc->id;
            }else{
                $user->kycClient->name = $request->name;
                $user->kycClient->lastname = $request->lastname;
                $user->kycClient->email = $req['email'];
                $user->kycClient->birthday = $req['birthday'];
                $user->kycClient->country = $req['country'];
                $user->kycClient->departement = $req['departement'];
                $user->kycClient->city = $req['city'];
                $user->kycClient->address = $req['address'];
                $user->kycClient->updated_at = carbon::now();
                $user->kycClient->save();
            }

            $user->name = $req['name'];
            $user->lastname = $req['lastname'];
            $user->verification_step_two = 1;
            $user->updated_at = carbon::now();
            $user->save();
            return sendResponse($user, 'Informations personnelles enregistrées avec succes.');
            
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function verificationInfoPiece(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user' => 'required',
                'piece_type' => 'required',
                'piece_id' => 'required',
                'piece_file' => 'required',
                'user_with_piece' => 'required'
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['user'])->where('deleted',0)->first();
            
            if($user->kycClient == null){
                $kyc = KycClient::create([
                    'id' => Uuid::uuid4()->toString(),
                    'piece_type' => $req['piece_type'],
                    'piece_id' => $req['piece_id'],
                    'piece_file' => $req['piece_file'],
                    'user_with_piece' => $req['user_with_piece'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                $user->kyc_client_id = $kyc->id;
            }else{

                $user->kycClient->piece_type = $req['piece_type'];
                $user->kycClient->piece_id = $req['piece_id'];
                $user->kycClient->piece_file = $req['piece_file'];
                $user->kycClient->user_with_piece = $req['user_with_piece'];
                $user->kycClient->updated_at = carbon::now();
                $user->kycClient->save();
            }

            $user->verification_step_three = 1;
            $user->updated_at = carbon::now();
            $user->save();

            $name = $user->name.' '.$user->lastname;
            Mail::to(['noreply-bcv@bestcash.me',])->send(new MailAlerteVerification($name));

            return sendResponse($user, 'Enregistrement des pieces effectué avec succes. Patientez maintenant le temps du traitement de votre requete de verification.');
            
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function saveFile(Request $request){        
        try{
            $path = '';
            if ($request->hasFile('piece')) {
                $path = $request->file('piece')->store('pieces','pieces');
                return sendResponse('/storage/pieces/'.$path, 'success');
            }
            if ($request->hasFile('user_with_piece')) {
                $path = $request->file('user_with_piece')->store('user_with_pieces','pieces');
                return sendResponse('/storage/pieces/'.$path, 'success');
            }

        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }
    
    public function checkClient(Request $request){
        try {
            $client = UserClient::where('id',$request->id)->first();
            if(!$client){
                return sendError('Ce client n\'existe pas', [], 404);
            }
            $client->makeHidden(['password','code_otp']);

            return sendResponse($client, 'Success');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function checkClientUsername(Request $request){
        try {
            $client = UserClient::where('username',$request->username)->first();
            if(!$client){
                return sendError('Ce client n\'existe pas', [], 404);
            }
            $cards = UserCard::where('user_client_id',$client->id)->get();
            return sendResponse($cards, 'Success');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function getDashboard(Request $request){
        $data = [];
        $client = UserClient::where('id',$request->id)->first()->makeHidden(['password','code_otp']);

        $cards = UserCard::where('user_client_id',$request->id)->orderBy('created_at','DESC')->limit(5)->get();
        $nb_card = UserCard::where('user_client_id',$request->id)->orderBy('created_at','DESC')->count();

        $beneficiaires = Beneficiaire::where('user_client_id',$request->id)->where('deleted',0)->orderBy('id','DESC')->limit(5)->get();
        
        $transactions = DB::select(DB::raw("SELECT id_operation, libelle , montant , type_operation , created_at
        FROM
        (
            select depots.id as 'id_operation', libelle , montant , 'depot' as type_operation , created_at
            From depots
            Where user_client_id = "."'$request->id'"."
            and status = 'completed'
        Union
            select recharges.id as 'id_operation', 'Rechargement de compte' as libelle , montant , 'recharge' as type_operation , created_at
            From recharges
            Where user_client_id = "."'$request->id'"."
            and status = 'completed'
        Union
            select retraits.id as 'id_operation', libelle , montant , 'retrait' as type_operation , created_at
            From retraits
            Where user_client_id = "."'$request->id'"."
            and status = 'completed'
        Union
            select self_retraits.id as 'id_operation', 'Retrait sur le numero lié' as libelle , montant , 'self_retrait' as type_operation , created_at
            From self_retraits
            Where user_client_id = "."'$request->id'"."
            and status = 'completed'
        Union
            select transfert_outs.id as 'id_operation', libelle , montant , 'transfert_out' as type_operation , created_at
            From transfert_outs
            Where user_client_id = "."'$request->id'"."
            and status = 'completed'
        Union
            select transfert_ins.id as 'id_operation', 'Transfert reçu' as libelle , montant_recu as 'montant' , 'transfert_in' as type_operation , created_at
            From transfert_ins
            Where user_client_id = "."'$request->id'"."
            and status = 'completed'
        ) transactions order by created_at desc limit 5"));

        $data['cards'] = $cards;
        $data['beneficiaires'] = $beneficiaires;
        $data['transactions'] = $transactions;
        $data['client'] = $client;

        $info_card = Info::where('deleted',0)->first();

        $data['infos'] =  [
            'nb_card' => $nb_card,
            'max_card' => $info_card ? $info_card->card_max : 5,
            'price_card' => $info_card ? $info_card->card_price : 0
        ];
        return sendResponse($data, 'Dashboard');
    }

    public function getSolde(Request $request){
        $cards = UserCard::where('user_client_id',$request->id)->orderBy('created_at','DESC')->limit(5)->get();
        $solde = 0;
        foreach ($cards as $key => $card) {
            $solde += (int) getCarteInformation((string)$card->customer_id, 'balance');
        }
        $encrypt_Key = env('ENCRYPT_KEY');
        $solde = encryptData((string)$solde,$encrypt_Key);

        return sendResponse($solde, 'Solde.');
    } 


    public function getClientAllTransaction(Request $request){
        
        $transactions = DB::select(DB::raw("SELECT id_operation, libelle , montant , type_operation , created_at
        FROM
        (
            select depots.id as 'id_operation', libelle , montant , 'depot' as type_operation , created_at
            From depots
            Where user_client_id = "."'$request->id'"."
            and status = 'completed'
        Union
            select recharges.id as 'id_operation', 'Rechargement de compte' as libelle , montant , 'recharge' as type_operation , created_at
            From recharges
            Where user_client_id = "."'$request->id'"."
            and status = 1
        Union
            select retraits.id as 'id_operation', libelle , montant , 'retrait' as type_operation , created_at
            From retraits
            Where user_client_id = "."'$request->id'"."
            and status = 'completed'
        Union
            select self_retraits.id as 'id_operation', 'Retrait sur le numero lié' as libelle , montant , 'self_retrait' as type_operation , created_at
            From self_retraits
            Where user_client_id = "."'$request->id'"."
            and status = 'completed'
        Union
            select transfert_outs.id as 'id_operation', libelle , montant , 'transfert_out' as type_operation , created_at
            From transfert_outs
            Where user_client_id = "."'$request->id'"."
            and status = 'completed'
        ) transactions order by created_at desc"));

        $i = 0;
        $lastKey = '';
        $data = [];
        foreach ($transactions as $key => $value) {
            $date = $this->get_day_name(strtotime($value->created_at));
            if($lastKey !== $date){
                $lastKey = $date;
                $i++;
            }
            $data[$i]['id'] = $i;
            $data[$i]['title'] = $date;
            $data[$i]['transactions'][$value->id_operation] = $value;
        }
        
        return sendResponse(collect($data), 'Liste transactions.');
    }

    public function getClientPendingTransaction(Request $request){

        $recharges = Recharge::select('id', 'montant', 'created_at')->where('user_client_id',$request->id)->where('status','pending')->where('is_debited',0)->where('deleted',0)->get();
        foreach($recharges as $recharge){
            $recharge->libelle = 'Rechargement de compte';
            $recharge->type_operation = 'card_load';
            $recharge->userClient = $recharge->userClient;
        }

        $transfert_outs = TransfertOut::select('id', 'libelle', 'montant', 'created_at')->where('user_client_id',$request->id)->where('status','pending')->where('deleted',0)->where('is_debited',0)->get();
        foreach($transfert_outs as $transfert_out){
            $transfert_out->type_operation = 'transfer';
            $transfert_out->userClient = $transfert_out->userClient;
        }

        $user_card_buys = UserCardBuy::select('id', 'montant', 'created_at')->where('user_client_id',$request->id)->where('status','pending')->where('deleted',0)->get();
        foreach($user_card_buys as $user_card_buy){
            $user_card_buy->libelle = 'Paiement de carte';
            $user_card_buy->type_operation = 'card_buy';
            $user_card_buy->userClient = $user_card_buy->userClient;
        }

        $transactions = array_merge($recharges->toArray(), $transfert_outs->toArray());
        $transactions = array_merge($transactions, $user_card_buys->toArray());
        krsort($transactions);
        $transactions = array_values($transactions);
        return sendResponse($transactions, 'Liste transactions.');
    }





    public function changeInfoUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user' => 'required',
                'name' => 'required',
                'lastname' => 'required',
                'sms' => 'required',
                'double_authentification' => 'required',
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['user'])->where('deleted',0)->first();
            $user->name = $req['name'];
            $user->lastname = $req['lastname'];
            $user->sms = $req['sms'];
            $user->double_authentification = $req['double_authentification'];
            $user->updated_at = carbon::now();
            $user->save();
            return sendResponse($user, 'Success.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function changePasswordUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['id'])->where('deleted',0)->first();
            $user->password = Hash::make($req['password']);
            $user->updated_at = carbon::now();
            $user->save();
            return sendResponse($user, 'Mot de passe changé avec succès.');
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }
    

    
    protected function createNewToken($token){
        return $token;
    }

    function get_day_name($timestamp) {

        $date = date('d/m/Y', $timestamp);
    
        if($date == date('d/m/Y')) {
          $date = 'Aujourd\'hui';
        } 
        else if($date == date('d/m/Y',now()->timestamp - (24 * 60 * 60))) {
          $date = 'Hier';
        }
        return $date;
    }
}