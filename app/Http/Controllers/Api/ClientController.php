<?php

namespace App\Http\Controllers\Api;

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

class ClientController extends Controller
{
    
    public function __construct() {
        $this->middleware('is-auth', ['except' => ['addContact','createCompteClient', 'loginCompteClient', 'sendCode', 'checkCodeOtp', 'resetPassword','verificationPhone', 'verificationInfoPerso','verificationInfoPiece','saveFile','sendCodeTelephoneRegistration','getServices','sendCodeTelephone']]);
    }

    public function tokenValide(Request $request){
        try {            
            return true;
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getKkpInfos(Request $request){
        try {            
            $encrypt_Key = env('ENCRYPT_KEY');
            $key = encryptData((string)env('API_KEY_KKIAPAY'),$encrypt_Key);

            $data['kkiapayApiKey'] = $key;
            $data['kkiapaySandBox'] = 0;
            $data['kkiapayTheme'] = '#000000';
            return $this->sendResponse($data, 'Api Kkiapay.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getCompteClientInfo(Request $request){
        try {            
            $encrypt_Key = env('ENCRYPT_KEY');
            $client = UserClient::where('deleted',0)->where('username',$request->username)->first();

            

            if(!$client){
                return $this->sendError('Ce compte client n\'exite pas. Verifiez le numero de telephone et recommencez');
            }else{
                if($client->status == 0){
                    return $this->sendError('Ce compte client est inactif');
                }
                if($client->verification == 0){
                    return $this->sendError('Ce compte client n\'est pas encore verifié');
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


            return $this->sendResponse($data, 'Info.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getServices(Request $request){
        try {            
            $modules = Service::where('deleted',0)->where('type','client')->get();
            return $this->sendResponse($modules, 'Modules.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getFees(Request $request){
        try {            
            $frais = Frai::where('deleted',0)->orderBy('created_at','DESC')->get();
            
            return $this->sendResponse($frais, 'Frais.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getMobileWallet(Request $request){
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
            
            return $this->sendResponse($mobileWallets, 'Frais.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
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
                return  $this->sendError($validator->errors()->first(), [],422);
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

            return $this->sendResponse($user, 'Compte crée avec succès.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
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
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    
    public function sendCode(Request $request){
        try {
            $user = UserClient::where('id',$request->id)->first();         

            if(!$user){
                return $this->sendError('L\'utilisateur n\'existe pas', [], 404);
            }

            $code = rand(1000,9999);

            $user->code_otp = $code;
            $user->save();

            $message = 'Votre code OTP de connexion est '.$code.' .';
            $this->sendSms($user->username,$message);
            return $this->sendResponse([], 'Code envoyé avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    
    public function sendCodeTelephone(Request $request){
        try {            
            $user = UserClient::where('username',$request->telephone)->first();

            if(!$user){
                return $this->sendError('L\'utilisateur n\'existe pas', [], 404);
            }

            $code = rand(1000,9999);
            $user->code_otp = $code;
            $user->save();

            $message = 'Votre code OTP de connexion est '.$code.' .';
            $this->sendSms($user->username,$message);
            return $this->sendResponse([], 'Code envoyé avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    
    public function sendCodeTelephoneRegistration(Request $request){
        try {            
            $user = UserClient::where('username',$request->telephone)->first();

            if($user){
                return $this->sendError('Le compte client existe déjà', [], 409);
            }

            $code = rand(1000,9999);

            $message = 'Votre code OTP de connexion est '.$code.' .';
            $this->sendSms($request->telephone,$message);
            
            $encrypt_Key = env('ENCRYPT_KEY');
            $code = encryptData((string)$code,$encrypt_Key);
            //return $this->sendSms($request->telephone,$message);

            return $this->sendResponse($code, 'Code envoyé avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
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
                return $this->sendError('Code OTP incorrect', [], 401);
            }

            return $this->sendResponse([], 'Vérification effectuée avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
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
                return $this->sendError('L\'utilisateur n\'existe pas', [], 401);
            }

            if($user->code_otp != $request->code){
                return $this->sendError('Code OTP incorrect', [], 401);
            }
            $user->password = Hash::make($request->password);
            $user->save();
            return $this->sendResponse([], 'Mot de passe modifé avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
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
                return $this->sendError('L\'utilisateur n\'existe pas', [], 401);
            }
            

            $user->pin = $request->pin;
            $user->save();
            $user->kycClient;
            $user->makeHidden(['password','code_otp']);
            return $this->sendResponse($user, 'PIN configurer avec succes');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
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
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function verificationPhone(Request $request){
        try {
            $data = [];

            $user = UserClient::where('id',$request->user_id)->where('deleted',0)->first();

            if(!$user){
                return $this->sendError('L\'utilisateur n\'existe pas', [], 404);
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

            return $this->sendResponse($data, 'Numero de telephone vérifié avec succes.');            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
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
                return  $this->sendError($validator->errors()->first(), [],422);
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
            return $this->sendResponse($user, 'Informations personnelles enregistrées avec succes.');
            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
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
                return  $this->sendError($validator->errors()->first(), [],422);
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

            return $this->sendResponse($user, 'Enregistrement des pieces effectué avec succes. Patientez maintenant le temps du traitement de votre requete de verification.');
            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function saveFile(Request $request){        
        try{
            $path = '';
            if ($request->hasFile('piece')) {
                $path = $request->file('piece')->store('pieces','pieces');
                return $this->sendResponse('/storage/pieces/'.$path, 'success');
            }
            if ($request->hasFile('user_with_piece')) {
                $path = $request->file('user_with_piece')->store('user_with_pieces','pieces');
                return $this->sendResponse('/storage/pieces/'.$path, 'success');
            }

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
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
        return $this->sendResponse($data, 'Liste de carte.');
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
        return $this->sendResponse($data, 'Carte infos.');
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
        return $this->sendResponse($data, 'Dashboard');
    }

    public function getSolde(Request $request){
        $cards = UserCard::where('user_client_id',$request->id)->orderBy('created_at','DESC')->limit(5)->get();
        $solde = 0;
        foreach ($cards as $key => $card) {
            $solde += (int) getCarteInformation((string)$card->customer_id, 'balance');
        }
        $encrypt_Key = env('ENCRYPT_KEY');
        $solde = encryptData((string)$solde,$encrypt_Key);

        return $this->sendResponse($solde, 'Solde.');
    }
    

    public function getCardInfo(Request $request){
        $card = UserCard::where('id',$request->id)->first();
        $card->info = getCarteInformation((string)$card->customer_id, 'all');
        return $this->sendResponse($card, 'Carte.');
    }

    public function getAccountInfo(Request $request){
        $card = UserCard::where('id',$request->id)->first();
        $card->accountInfo = getCarteInformation((string)$card->customer_id, 'accountInfo');
        return $this->sendResponse($card, 'Carte.');
    }

    public function getBalance(Request $request){
        $card = UserCard::where('id',$request->id)->first();
        $encrypt_Key = env('ENCRYPT_KEY');
        $card->balance = encryptData((string)getCarteInformation((string)$card->customer_id, 'balance'),$encrypt_Key);
        return $this->sendResponse($card, 'Carte.');
    }

    public function getClientInfo(Request $request){
        $card = UserCard::where('id',$request->id)->first();
        $card->clientInfo = getCarteInformation((string)$card->customer_id, 'clientInfo');
        return $this->sendResponse($card, 'Carte.');
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
        
        return $this->sendResponse(collect($data), 'Liste transactions.');
    }

    public function getClientPendingTransaction(Request $request){
        
        $transactions = DB::select(DB::raw("SELECT id, libelle , montant , type_operation , created_at
        FROM
        (
            select recharges.id, 'Rechargement de compte' as libelle , montant , 'card_load' as type_operation , created_at
            From recharges
            Where user_client_id = "."'$request->id'"."
            and status = 'pending'
        Union
            select self_retraits.id, 'Retrait sur le numero lié' as libelle , montant , 'self_withdrawl' as type_operation , created_at
            From self_retraits
            Where user_client_id = "."'$request->id'"."
            and status = 'pending'
        Union
            select transfert_outs.id, libelle , montant , 'transfer' as type_operation , created_at
            From transfert_outs
            Where user_client_id = "."'$request->id'"."
            and status = 'pending'
        Union
            select user_card_buys.id, 'Paiement de carte' as libelle , montant as 'montant' , 'card_buy' as type_operation , user_card_buys.created_at
            From user_card_buys
            Where user_card_buys.user_client_id = "."'$request->id'"."
            and status = 'pending'
        ) transactions order by created_at desc"));
        
        return $this->sendResponse($transactions, 'Liste transactions.');
    }

    public function getClientPendingWithdraws(Request $request){        
        $withdrawls = Retrait::where('deleted',0)->where('user_client_id',$request->id)->where('status','pending')->get();
        return $this->sendResponse($withdrawls, 'Liste transactions.');
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
            return $this->sendResponse($resultat_debit_bmo, 'Operation initié avec succes.');

        } catch (BadResponseException $e) {
            return $this->sendError($e->getMessage(), [], 403);
        }
    }

    public function confirmationBmo(Request $request){
        try{             
            $base_url_bmo = env('BASE_BMO');

            // Realisation de la transaction

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
            
            return $this->sendResponse($resultat_debit_bmo, 'Operation confirmée avec succes.');

        } catch (BadResponseException $e) {
            return $this->sendError($e->getMessage(), [], 401);
        }
    }



    public function addDepotClient(Request $request){
        try {
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');
            $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');
            
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'montant' => 'required',
                'reference' => 'required',
                'moyen_paiement' => 'required',
                'user_card_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }

            $user = UserClient::where('id',$request->user_id)->first();
            $card = UserCard::where('id',$request->user_card_id)->first();

        //recuperation solde avant
            $soldeAvantDepot = getUserSolde($user->id);
        
        // Calcule de frais
            $montant = $request->montant;
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


        // Rechargement de la carte
            $client = new Client();
            $encrypt_Key = env('ENCRYPT_KEY');
            $url =  $base_url."accounts/".decryptData((string)$card->customer_id, $encrypt_Key)."/transactions";
            //  return $this->sendError($montantWithoutFee, [], 500);
            $body = [
                "transferType" => "WalletToCard",
                "transferAmount" => $montantWithoutFee,
                "currencyCode" => "XOF",
                "referenceMemo" => "Rechargement de ".$montant." XOF sur votre carte. Frais de rechargement : ".$frais." XOF",
                "last4Digits" => decryptData((string)$card->last_digits, $encrypt_Key)
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

            $referenceBcb = 'rec-'.Uuid::uuid4()->toString();
            $referenceGtp = $responseBody->transactionId;
            
            $soldeApresDepot = $soldeAvantDepot + $montantWithoutFee;

            // Repartition des frais
            
            $compteUba = EntityAccountCommission::where('libelle','UBA')->where('deleted',0)->first();
            $compteElg = EntityAccountCommission::where('libelle','ELG')->where('deleted',0)->first();

            if($fraisAndRepartition){
                if($fraisAndRepartition->type_commission_elg == 'pourcentage'){
                    $commissionElg = $frais * $fraisAndRepartition->value_commission_elg / 100;
                }else{
                    $commissionElg = $fraisAndRepartition->value_commission_elg;
                }

                if($fraisAndRepartition->type_commission_bank == 'pourcentage'){
                    $commissionBank = $frais * $fraisAndRepartition->value_commission_bank / 100;
                }else{
                    $commissionBank = $fraisAndRepartition->value_commission_bank;
                }

                $compteElg->solde += $commissionElg;
                $compteElg->save();

                EntityAccountCommissionOperation::insert([
                    [
                        'id' => Uuid::uuid4()->toString(),
                        'entity_account_commission_id'=> $compteUba->id,
                        'type_operation'=>'rechargement',
                        'montant'=> $montant,
                        'frais'=> $frais,
                        'commission'=> $commissionBank,
                        'reference_bcb'=> $referenceBcb,
                        'reference_gtp'=> $referenceGtp,
                        'status'=> 0,
                        'deleted'=> 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()                
                    ],
                    [
                        'id' => Uuid::uuid4()->toString(),
                        'entity_account_commission_id'=> $compteElg->id,
                        'type_operation'=>'rechargement',
                        'montant'=> $montant,
                        'frais'=> $frais,
                        'commission'=> $commissionElg,
                        'reference_bcb'=> $referenceBcb,
                        'reference_gtp'=> $referenceGtp,
                        'status'=> 0,
                        'deleted'=> 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]
                ]);

                $compteUba->solde += $commissionBank;
                $compteUba->save();
            }

            $recharge = Recharge::create([
                'id' => Uuid::uuid4()->toString(),
                'user_client_id' => $request->user_id,
                'user_card_id' => $request->user_card_id,
                'montant' => $montant,
                'reference_operateur' => $request->reference,
                'reference_gtp' => $referenceGtp,
                'reference_bcb' => $referenceBcb,
                'moyen_paiement' => $request->moyen_paiement,
                'frais_bcb' => $frais,
                'montant_recu' => $montantWithoutFee,
                'status' => 1,
                'solde_avant' => $soldeAvantDepot,
                'solde_apres' => $soldeApresDepot,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            
            $message = 'Vous avez faire un rechargement de '.$montant.' XOF sur votre compte BCB Virtuelle. Frais de rechargement Votre nouveau solde est: '.$soldeApresDepot.' XOF.';
            
            if($user->sms == 1){
                $this->sendSms($user->username,$message);
            }else{
                $arr = ['messages'=> $message,'objet'=>'Confirmation du rechargement','from'=>'noreply-bcv@bestcash.me'];
                Mail::to([$user->kycClient->email,])->send(new MailAlerte($arr));
            }
            return $this->sendResponse($recharge, 'Rechargement effectué avec succes. Consulter votre solde');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addNewDepotClient(Request $request){
        try {
            // Recuperation variable et verification faisabilité

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
                    return  $this->sendError($validator->errors()->first(), [],422);
                }

                $user = UserClient::where('id',$request->user_id)->first();
                $card = UserCard::where('id',$request->user_card_id)->first();

            // Fin recuperation variable et faisabilité

            // Recuperation solde avant depot et calcul des frais

                $soldeAvantDepot = getUserSolde($user->id);

                $montant = $request->montant;
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
            
            // Fin recuperation solde avant depot et calcul des frais

            // Verification paiement et initiation transaction
            
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
                    return $this->sendError('Le montant ne correspond pas au montant de la transaction', [], 500);
                }else if(checkPayment($request->moyen_paiement, $request->reference, $request->montant) == 'not_success'){
                    $reason = date('Y-m-d h:i:s : Echec du paiement');
                    $recharge->reasons = $reason;
                    $recharge->status = 'failed';
                    $recharge->save();
                    return $this->sendError('Le paiement du montant n\'a pas aboutit', [], 500);
                }
                
                $recharge->is_paid = 1;
                $recharge->save();

            // Fin verification paiement et initiation transaction

            // Rechargement de la carte

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

                try {
                    $response = $client->request('POST', $url, [
                        'auth' => $auth,
                        'headers' => $headers,
                        'body' => $body,
                        'verify'  => false,
                    ]);
                
                    $responseBody = json_decode($response->getBody());

                    // Finalisation de l'operation

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
                            $this->sendSms($user->username,$message);
                        }else{
                            $arr = ['messages'=> $message,'objet'=>'Confirmation du rechargement','from'=>'noreply-bcv@bestcash.me'];
                            Mail::to([$user->kycClient->email,])->send(new MailAlerte($arr));
                        }

                    // Fin finalisation de l'operation

                    // Repartition des frais
                    
                        $compteUba = EntityAccountCommission::where('libelle','UBA')->where('deleted',0)->first();
                        $compteElg = EntityAccountCommission::where('libelle','ELG')->where('deleted',0)->first();

                        if($fraisAndRepartition){
                            if($fraisAndRepartition->type_commission_elg == 'pourcentage'){
                                $commissionElg = $frais * $fraisAndRepartition->value_commission_elg / 100;
                            }else{
                                $commissionElg = $fraisAndRepartition->value_commission_elg;
                            }

                            if($fraisAndRepartition->type_commission_bank == 'pourcentage'){
                                $commissionBank = $frais * $fraisAndRepartition->value_commission_bank / 100;
                            }else{
                                $commissionBank = $fraisAndRepartition->value_commission_bank;
                            }

                            $compteElg->solde += $commissionElg;
                            $compteElg->save();

                            EntityAccountCommissionOperation::insert([
                                [
                                    'id' => Uuid::uuid4()->toString(),
                                    'entity_account_commission_id'=> $compteUba->id,
                                    'type_operation'=>'rechargement',
                                    'montant'=> $montant,
                                    'frais'=> $frais,
                                    'commission'=> $commissionBank,
                                    'reference_bcb'=> $referenceBcb,
                                    'reference_gtp'=> $referenceGtp,
                                    'status'=> 0,
                                    'deleted'=> 0,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()                
                                ],
                                [
                                    'id' => Uuid::uuid4()->toString(),
                                    'entity_account_commission_id'=> $compteElg->id,
                                    'type_operation'=>'rechargement',
                                    'montant'=> $montant,
                                    'frais'=> $frais,
                                    'commission'=> $commissionElg,
                                    'reference_bcb'=> $referenceBcb,
                                    'reference_gtp'=> $referenceGtp,
                                    'status'=> 0,
                                    'deleted'=> 0,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]
                            ]);

                            $compteUba->solde += $commissionBank;
                            $compteUba->save();
                        }

                    // Fin repartition des frais
                    $recharge->save();

                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return $this->sendError($error, [], 500);
                }
            // Fin rechargement de la carte

            return $this->sendResponse($recharge, 'Rechargement effectué avec succes. Consulter votre solde');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function completeDepotClient(Request $request){
        //try {
            // Recuperation variable et verification faisabilité

                $base_url = env('BASE_GTP_API');
                $programID = env('PROGRAM_ID');
                $authLogin = env('AUTH_LOGIN');
                $authPass = env('AUTH_PASS');
                $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');

                $validator = Validator::make($request->all(), [
                    'transaction_id' => ["required" , "string"]
                ]);

                if ($validator->fails()) {
                    return  $this->sendError($validator->errors()->first(), [],422);
                }
                
                $recharge = Recharge::where('id',$request->transaction_id)->first();

                $user = UserClient::where('id',$recharge->user_client_id)->first();
                $card = UserCard::where('id',$recharge->user_card_id)->first();

            // Fin recuperation variable et faisabilité

            
            if($recharge->is_paid == 0){
                // Verification paiement et initiation transaction

                    if(checkPayment($recharge->moyen_paiement, $recharge->reference_operateur, $recharge->montant) == 'bad_amount'){
                        $reason = date('Y-m-d h:i:s : Montant incorrecte');
                        $recharge->reasons = $reason;
                        $recharge->status = 'failed';
                        $recharge->save();
                        return $this->sendError('Le montant ne correspond pas au montant de la transaction', [], 500);
                    }else if(checkPayment($recharge->moyen_paiement, $recharge->reference_operateur, $recharge->montant) == 'not_success'){
                        $reason = date('Y-m-d h:i:s : Echec du paiement');
                        $recharge->reasons = $reason;
                        $recharge->status = 'failed';
                        $recharge->save();
                        return $this->sendError('Le paiement du montant n\'a pas aboutit', [], 500);
                    }
                    
                    $recharge->is_paid = 1;
                    $recharge->save();

                // Fin verification paiement et initiation transaction
            }
            // Recuperation solde avant depot et calcul des frais

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
            
            // Fin recuperation solde avant depot et calcul des frais

            // Rechargement de la carte

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

                try {
                    $response = $client->request('POST', $url, [
                        'auth' => $auth,
                        'headers' => $headers,
                        'body' => $body,
                        'verify'  => false,
                    ]);
                
                    $responseBody = json_decode($response->getBody());

                    // Finalisation de l'operation

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
                            $this->sendSms($user->username,$message);
                        }else{
                            $arr = ['messages'=> $message,'objet'=>'Confirmation du rechargement','from'=>'noreply-bcv@bestcash.me'];
                            Mail::to([$user->kycClient->email,])->send(new MailAlerte($arr));
                        }

                    // Fin finalisation de l'operation

                    // Repartition des frais
                    
                        $compteUba = EntityAccountCommission::where('libelle','UBA')->where('deleted',0)->first();
                        $compteElg = EntityAccountCommission::where('libelle','ELG')->where('deleted',0)->first();

                        if($fraisAndRepartition){
                            if($fraisAndRepartition->type_commission_elg == 'pourcentage'){
                                $commissionElg = $frais * $fraisAndRepartition->value_commission_elg / 100;
                            }else{
                                $commissionElg = $fraisAndRepartition->value_commission_elg;
                            }

                            if($fraisAndRepartition->type_commission_bank == 'pourcentage'){
                                $commissionBank = $frais * $fraisAndRepartition->value_commission_bank / 100;
                            }else{
                                $commissionBank = $fraisAndRepartition->value_commission_bank;
                            }

                            $compteElg->solde += $commissionElg;
                            $compteElg->save();

                            EntityAccountCommissionOperation::insert([
                                [
                                    'id' => Uuid::uuid4()->toString(),
                                    'entity_account_commission_id'=> $compteUba->id,
                                    'type_operation'=>'rechargement',
                                    'montant'=> $montant,
                                    'frais'=> $frais,
                                    'commission'=> $commissionBank,
                                    'reference_bcb'=> $referenceBcb,
                                    'reference_gtp'=> $referenceGtp,
                                    'status'=> 0,
                                    'deleted'=> 0,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()                
                                ],
                                [
                                    'id' => Uuid::uuid4()->toString(),
                                    'entity_account_commission_id'=> $compteElg->id,
                                    'type_operation'=>'rechargement',
                                    'montant'=> $montant,
                                    'frais'=> $frais,
                                    'commission'=> $commissionElg,
                                    'reference_bcb'=> $referenceBcb,
                                    'reference_gtp'=> $referenceGtp,
                                    'status'=> 0,
                                    'deleted'=> 0,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]
                            ]);

                            $compteUba->solde += $commissionBank;
                            $compteUba->save();
                        }

                    // Fin repartition des frais

                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return $this->sendError($error, [], 500);
                }
            // Fin rechargement de la carte

            return $this->sendResponse($recharge, 'Rechargement effectué avec succes. Consulter votre solde');
        /*} catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };*/
    }

    public function getDepotsClient(Request $request){
        try {
            $depots = Depot::where('user_client_id',$request->id)->where('deleted',0)->orderBy('created_at','desc')->get();
            $recharges = Recharge::where('user_client_id',$request->id)->where('deleted',0)->orderBy('created_at','desc')->get();

            $data = $dataDepots = $dataRecharges = [];

            $i = 0;
            $lastKey = '';
            foreach ($depots as $key => $value) {
                $date = $this->get_day_name($value->created_at->timestamp);
                if($lastKey !== $date){
                    $lastKey = $date;
                    $i++;
                }
                $dataDepots[$i]['id'] = $i;
                $dataDepots[$i]['title'] = $date;
                $dataDepots[$i]['transactions'][$value->id] = $value;
            }

            $i = 0;
            $lastKey = '';
            foreach ($recharges as $key => $value) {
                $date = $this->get_day_name($value->created_at->timestamp);
                if($lastKey !== $date){
                    $lastKey = $date;
                    $i++;
                }
                $dataRecharges[$i]['id'] = $i;
                $dataRecharges[$i]['title'] = $date;
                $dataRecharges[$i]['transactions'][$value->id] = $value;
            }

            $data['depots'] = $dataDepots;
            $data['recharges'] = $dataRecharges;

            return $this->sendResponse(collect($data), 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function getDepotDetailClient(Request $request){
        try {
            $depot = Retrait::where('id',$request->id)->first();
            $depot->partenaire_name = $depot->partenaire->libelle;
            
            return $this->sendResponse($depot, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }



    public function getRechargeDetailClient(Request $request){
        try {
            $recharge = Recharge::where('id',$request->id)->first();
            
            return $this->sendResponse($recharge, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addSelfRetraitClient(Request $request){
        try {
            $encrypt_Key = env('ENCRYPT_KEY');
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');
            $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');
            
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'user_card_id' => 'required',
                'montant' => 'required',
                'type' => 'required',
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }

            $user = UserClient::where('id',$request->user_id)->first();
            $card = UserCard::where('id',$request->user_card_id)->first();

            //recuperation solde avant
            $soldeAvantRetrait = getUserSolde($user->id);
        
        
            // Calcule de frais
            $montant = $request->montant;
            $fraisAndRepartition = getFeeAndRepartition('self_retrait', $montant);

            $frais = 0;
            if($fraisAndRepartition){
                if($fraisAndRepartition->type == 'pourcentage'){
                    $frais = $montant * $fraisAndRepartition->value / 100;
                }else{
                    $frais = $fraisAndRepartition->value;
                }
            }

            $montantWithFee = $montant + $frais;    

            // Retrait de la carte
                $client = new Client();
                $url =  $base_url."accounts/".decryptData($card->customer_id, $encrypt_Key)."/transactions";
                
                $body = [
                    "transferType" => "CardToWallet",
                    "transferAmount" => $montantWithFee,
                    "currencyCode" => "XOF",
                    "referenceMemo" => "Retrait de ".$montant." XOF de votre carte. Frais de retrait ".$frais." XOF",
                    "last4Digits" => decryptData($card->last_digits, $encrypt_Key)
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
                $referenceGtp = $responseBody->transactionId;
                $referenceBcb = 'srt-'.Uuid::uuid4()->toString();
                $soldeApresRetrait =  $soldeAvantRetrait - $montantWithFee;
                $frais_gtp = 0;

                // Repartition des frais
            
                    $compteUba = EntityAccountCommission::where('libelle','UBA')->where('deleted',0)->first();
                    $compteElg = EntityAccountCommission::where('libelle','ELG')->where('deleted',0)->first();

                    if($fraisAndRepartition){
                        if($fraisAndRepartition->type_commission_elg == 'pourcentage'){
                            $commissionElg = $frais * $fraisAndRepartition->value_commission_elg / 100;
                        }else{
                            $commissionElg = $fraisAndRepartition->value_commission_elg;
                        }

                        if($fraisAndRepartition->type_commission_bank == 'pourcentage'){
                            $commissionBank = $frais * $fraisAndRepartition->value_commission_bank / 100;
                        }else{
                            $commissionBank = $fraisAndRepartition->value_commission_bank;
                        }

                        $compteElg->solde += $commissionElg;
                        $compteElg->save();

                        EntityAccountCommissionOperation::insert([
                            [
                                'id' => Uuid::uuid4()->toString(),
                                'entity_account_commission_id'=> $compteUba->id,
                                'type_operation'=>'self_retrait',
                                'montant'=> $montant,
                                'frais'=> $frais,
                                'commission'=> $commissionBank,
                                'reference_bcb'=> $referenceBcb,
                                'reference_gtp'=> $referenceGtp,
                                'status'=> 0,
                                'deleted'=> 0,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()                
                            ],
                            [
                                'id' => Uuid::uuid4()->toString(),
                                'entity_account_commission_id'=> $compteElg->id,
                                'type_operation'=>'self_retrait',
                                'montant'=> $montant,
                                'frais'=> $frais,
                                'commission'=> $commissionElg,
                                'reference_bcb'=> $referenceBcb,
                                'reference_gtp'=> $referenceGtp,
                                'status'=> 0,
                                'deleted'=> 0,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]
                        ]);

                        $compteUba->solde += $commissionBank;
                        $compteUba->save();
                    }

                $retrait = SelfRetrait::create([
                    'id' => Uuid::uuid4()->toString(),
                    'user_client_id' => $request->user_id,
                    'montant' => $request->montant,
                    'telephone' => $user->username,
                    'reference_gtp' => $referenceGtp,
                    'reference_bcb' => $referenceBcb,
                    'moyen_paiement' => $request->type,
                    'frais_bcb' => $frais,
                    'user_card_id' => $request->user_card_id,
                    'status' => 1,
                    'montant_recu' => $montant,
                    'solde_avant' => $soldeAvantRetrait,
                    'solde_apres' => $soldeApresRetrait,
                    'validateur_id' => 1,
                    'deleted' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            
            // Rechargement du compte de l'utilisateur

                if($request->type == 'bmo'){
                    try{             
                        $partner_reference = substr($user->username, -4).time();
                        $base_url_bmo = env('BASE_BMO');
            
                        // Realisation de la transaction
            
                        $client = new Client();
                        $url = $base_url_bmo."/operations/credit";
                        
                        $body = [
                            "amount" => $montant,
                            "customer" => [
                                "phone"=> "+".$user->username,
                                "firstname"=> $user->lastname,
                                "lastname"=> $user->name
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
            
                    } catch (BadResponseException $e) {
                        return $this->sendError($e->getMessage(), [], 401);
                    }
                }else{
                    try { 
                        $partner_reference = substr($user->username, -4).time();
                        $base_url_kkp = env('BASE_KKIAPAY');
            
                        $client = new Client();
                        $url = $base_url_kkp."/api/v1/payments/deposit";
                        
                        $body = [
                            "phoneNumber" => $user->username,
                            "amount" => $montant,
                            "reason" => "Retrait de Bcb virtuelle",
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

                        $resultat_credit_kkp = json_decode($response->getBody());
                        
                        $starttime = time();

                        $status = "PENDING";

                        while ($status == "PENDING") {
                            $externalTransaction = $this->resultat_check_status_kkp($resultat_credit_kkp->transactionId);
                            if ($externalTransaction->status == "SUCCESS"){
                                $retrait->reference_operateur = $externalTransaction->externalTransactionId;
                                $status = "SUCCESS";
                            }else if($externalTransaction->status == "FAILED") {
                                $status = "FAILED";
                                return $this->sendError('Echec lors du crédit de votre compte', [], 500);
                            }else{
                                $now = time()-$starttime;
                                if ($now > 125) {
                                    return $this->sendError('Erreur de confirmation du depot. Vous pouvez vous rapprocher de nos services pour plus d\'informations', [], 500);
                                }
                                $status = $externalTransaction->status;
                            }
                        }
                    } catch (BadResponseException $e) {
                        return json_encode(['message' => $e->getMessage() , 'data' => []]);
                    }
                }

                $message = 'Vous avez retirer '.$request->montant.' XOF de votre compte BCB Virtuelle. Frais d\'operation '.$frais_gtp+$frais.' XOF. Votre nouveau solde est: '.$soldeApresRetrait.' XOF.';
                
                if($user->sms == 1){
                    $this->sendSms($user->username,$message);
                }else{
                    $arr = ['messages'=> $message,'objet'=>'Confirmation du rechargement','from'=>'noreply-bcv@bestcash.me'];
                    Mail::to([$user->kycClient->email,])->send(new MailAlerte($arr));
                }
            return $this->sendResponse($retrait, 'Retrait effectué avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addNewSelfRetraitClient(Request $request){
        try {
            // Recuperation variable et verification faisabilité

                $encrypt_Key = env('ENCRYPT_KEY');
                $base_url = env('BASE_GTP_API');
                $programID = env('PROGRAM_ID');
                $authLogin = env('AUTH_LOGIN');
                $authPass = env('AUTH_PASS');
                $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');
                
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'user_card_id' => 'required',
                    'montant' => 'required',
                    'type' => 'required',
                ]);
                if ($validator->fails()) {
                    return  $this->sendError($validator->errors()->first(), [],422);
                }

                $user = UserClient::where('id',$request->user_id)->first();
                $card = UserCard::where('id',$request->user_card_id)->first();

            // Fin recuperation variable et verification faisabilité

            // Recuperation solde avant et calcul des frais

                $soldeAvantRetrait = getUserSolde($user->id);
                
                $montant = $request->montant;
                $fraisAndRepartition = getFeeAndRepartition('self_retrait', $montant);

                $frais = 0;
                if($fraisAndRepartition){
                    if($fraisAndRepartition->type == 'pourcentage'){
                        $frais = $montant * $fraisAndRepartition->value / 100;
                    }else{
                        $frais = $fraisAndRepartition->value;
                    }
                }

                $montantWithFee = $montant + $frais;    

            // Fin recuperation solde et calcul des frais

            // Retrait de la carte

                $client = new Client();
                $url =  $base_url."accounts/".decryptData($card->customer_id, $encrypt_Key)."/transactions";
                
                $body = [
                    "transferType" => "CardToWallet",
                    "transferAmount" => $montantWithFee,
                    "currencyCode" => "XOF",
                    "referenceMemo" => "Retrait de ".$montant." XOF de votre carte. Frais de retrait ".$frais." XOF",
                    "last4Digits" => decryptData($card->last_digits, $encrypt_Key)
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

                try {
                    $response = $client->request('POST', $url, [
                        'auth' => $auth,
                        'headers' => $headers,
                        'body' => $body,
                        'verify'  => false,
                    ]);            
                    $responseBody = json_decode($response->getBody());

                    $referenceGtp = $responseBody->transactionId;
                    $referenceBcb = 'srt-'.Uuid::uuid4()->toString();
                    $soldeApresRetrait =  $soldeAvantRetrait - $montantWithFee;
                    $frais_gtp = 0;                    
            
                    // Initiation transaction
                    
                        $retrait = SelfRetrait::create([
                            'id' => Uuid::uuid4()->toString(),
                            'user_client_id' => $request->user_id,
                            'user_card_id' => $request->user_card_id,
                            'montant' => $request->montant,
                            'moyen_paiement' => $request->type,
                            'status' => 'pending',
                            'deleted' => 0,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);

                    // Fin verification paiement et initiation transaction

                    // Repartition des frais
                
                        $compteUba = EntityAccountCommission::where('libelle','UBA')->where('deleted',0)->first();
                        $compteElg = EntityAccountCommission::where('libelle','ELG')->where('deleted',0)->first();

                        if($fraisAndRepartition){
                            if($fraisAndRepartition->type_commission_elg == 'pourcentage'){
                                $commissionElg = $frais * $fraisAndRepartition->value_commission_elg / 100;
                            }else{
                                $commissionElg = $fraisAndRepartition->value_commission_elg;
                            }

                            if($fraisAndRepartition->type_commission_bank == 'pourcentage'){
                                $commissionBank = $frais * $fraisAndRepartition->value_commission_bank / 100;
                            }else{
                                $commissionBank = $fraisAndRepartition->value_commission_bank;
                            }

                            $compteElg->solde += $commissionElg;
                            $compteElg->save();

                            EntityAccountCommissionOperation::insert([
                                [
                                    'id' => Uuid::uuid4()->toString(),
                                    'entity_account_commission_id'=> $compteUba->id,
                                    'type_operation'=>'self_retrait',
                                    'montant'=> $montant,
                                    'frais'=> $frais,
                                    'commission'=> $commissionBank,
                                    'reference_bcb'=> $referenceBcb,
                                    'reference_gtp'=> $referenceGtp,
                                    'status'=> 0,
                                    'deleted'=> 0,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()                
                                ],
                                [
                                    'id' => Uuid::uuid4()->toString(),
                                    'entity_account_commission_id'=> $compteElg->id,
                                    'type_operation'=>'self_retrait',
                                    'montant'=> $montant,
                                    'frais'=> $frais,
                                    'commission'=> $commissionElg,
                                    'reference_bcb'=> $referenceBcb,
                                    'reference_gtp'=> $referenceGtp,
                                    'status'=> 0,
                                    'deleted'=> 0,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]
                            ]);

                            $compteUba->solde += $commissionBank;
                            $compteUba->save();
                        }
                        
                    // Fin repartition des frais
                        
                    // Rechargement du compte de l'utilisateur

                        if($request->type == 'bmo'){
                            try{             
                                $partner_reference = substr($user->username, -4).time();
                                $base_url_bmo = env('BASE_BMO');
                    
                                // Realisation de la transaction
                    
                                $client = new Client();
                                $url = $base_url_bmo."/operations/credit";
                                
                                $body = [
                                    "amount" => $montant,
                                    "customer" => [
                                        "phone"=> "+".$user->username,
                                        "firstname"=> $user->lastname,
                                        "lastname"=> $user->name
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
                                $reference = $resultat_debit_bmo->reference;
                    
                            } catch (BadResponseException $e) {
                                return $this->sendError($e->getMessage(), [], 401);
                            }
                        }else{
                            try { 
                                $partner_reference = substr($user->username, -4).time();
                                $base_url_kkp = env('BASE_KKIAPAY');
                    
                                $client = new Client();
                                $url = $base_url_kkp."/api/v1/payments/deposit";
                                
                                $body = [
                                    "phoneNumber" => $user->username,
                                    "amount" => $montant,
                                    "reason" => "Retrait de Bcb virtuelle",
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

                                $resultat_credit_kkp = json_decode($response->getBody());
                                $reference = $resultat_credit_kkp->transactionId;
                                
                                $starttime = time();

                                $status = "PENDING";

                                while ($status == "PENDING") {
                                    $externalTransaction = $this->resultat_check_status_kkp($resultat_credit_kkp->transactionId);
                                    if ($externalTransaction->status == "SUCCESS"){
                                        $retrait->reference_operateur = $externalTransaction->externalTransactionId;
                                        $status = "SUCCESS";
                                    }else if($externalTransaction->status == "FAILED") {
                                        $status = "FAILED";
                                        return $this->sendError('Echec lors du crédit de votre compte', [], 500);
                                    }else{
                                        $now = time()-$starttime;
                                        if ($now > 125) {
                                            return $this->sendError('Erreur de confirmation du depot. Vous pouvez vous rapprocher de nos services pour plus d\'informations', [], 500);
                                        }
                                        $status = $externalTransaction->status;
                                    }
                                }
                            } catch (BadResponseException $e) {
                                return json_encode(['message' => $e->getMessage() , 'data' => []]);
                            }
                        }

                        $retrait->telephone = $user->username;
                        $retrait->reference_gtp = $referenceGtp;
                        $retrait->reference_bcb = $referenceBcb;
                        $retrait->reference_operateur = $reference;
                        $retrait->status = 'completed';
                        $retrait->montant_recu = $montant;
                        $retrait->solde_avant = $soldeAvantRetrait;
                        $retrait->solde_apres = $soldeApresRetrait;
                        $retrait->frais_bcb = $frais;
                        $retrait->validateur_id = 0;
                        $retrait->save();

                    // Fin rechargement du compte de l'utilisateur               

                    $message = 'Vous avez retirer '.$request->montant.' XOF de votre compte BCB Virtuelle. Frais d\'operation '.$frais_gtp+$frais.' XOF. Votre nouveau solde est: '.$soldeApresRetrait.' XOF.';
                    
                    if($user->sms == 1){
                        $this->sendSms($user->username,$message);
                    }else{
                        $arr = ['messages'=> $message,'objet'=>'Confirmation du rechargement','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$user->kycClient->email,])->send(new MailAlerte($arr));
                    }

                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return $this->sendError($error, [], 500);
                }

            // Fin retrait de la carte
            return $this->sendResponse($retrait, 'Retrait effectué avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function completeSelfRetraitClient(Request $request){
        try {
            // Recuperation variable et verification faisabilité

                $encrypt_Key = env('ENCRYPT_KEY');
                $base_url = env('BASE_GTP_API');
                $programID = env('PROGRAM_ID');
                $authLogin = env('AUTH_LOGIN');
                $authPass = env('AUTH_PASS');
                $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');
                
                $validator = Validator::make($request->all(), [
                    'transaction_id' => ["required" , "string"]
                ]);

                if ($validator->fails()) {
                    return  $this->sendError($validator->errors()->first(), [],422);
                }
                
                $retrait = SelfRetrait::where('id',$request->transaction_id)->first();

                $user = UserClient::where('id',$retrait->user_client_id)->first();
                $card = UserCard::where('id',$retrait->user_card_id)->first();

            // Fin recuperation variable et verification faisabilité
            
            // Recuperation solde avant et calcul des frais

                $soldeAvantRetrait = getUserSolde($user->id);
                
                $montant = $request->montant;
                $fraisAndRepartition = getFeeAndRepartition('self_retrait', $montant);

                $frais = 0;
                if($fraisAndRepartition){
                    if($fraisAndRepartition->type == 'pourcentage'){
                        $frais = $montant * $fraisAndRepartition->value / 100;
                    }else{
                        $frais = $fraisAndRepartition->value;
                    }
                }

                $montantWithFee = $montant + $frais;    

            // Fin recuperation solde et calcul des frais

            // Retrait de la carte

                $client = new Client();
                $url =  $base_url."accounts/".decryptData($card->customer_id, $encrypt_Key)."/transactions";
                
                $body = [
                    "transferType" => "CardToWallet",
                    "transferAmount" => $montantWithFee,
                    "currencyCode" => "XOF",
                    "referenceMemo" => "Retrait de ".$montant." XOF de votre carte. Frais de retrait ".$frais." XOF",
                    "last4Digits" => decryptData($card->last_digits, $encrypt_Key)
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

                try {
                    $response = $client->request('POST', $url, [
                        'auth' => $auth,
                        'headers' => $headers,
                        'body' => $body,
                        'verify'  => false,
                    ]);            
                    $responseBody = json_decode($response->getBody());

                    $referenceGtp = $responseBody->transactionId;
                    $referenceBcb = 'srt-'.Uuid::uuid4()->toString();
                    $soldeApresRetrait =  $soldeAvantRetrait - $montantWithFee;
                    $frais_gtp = 0;

                    // Repartition des frais
                
                        $compteUba = EntityAccountCommission::where('libelle','UBA')->where('deleted',0)->first();
                        $compteElg = EntityAccountCommission::where('libelle','ELG')->where('deleted',0)->first();

                        if($fraisAndRepartition){
                            if($fraisAndRepartition->type_commission_elg == 'pourcentage'){
                                $commissionElg = $frais * $fraisAndRepartition->value_commission_elg / 100;
                            }else{
                                $commissionElg = $fraisAndRepartition->value_commission_elg;
                            }

                            if($fraisAndRepartition->type_commission_bank == 'pourcentage'){
                                $commissionBank = $frais * $fraisAndRepartition->value_commission_bank / 100;
                            }else{
                                $commissionBank = $fraisAndRepartition->value_commission_bank;
                            }

                            $compteElg->solde += $commissionElg;
                            $compteElg->save();

                            EntityAccountCommissionOperation::insert([
                                [
                                    'id' => Uuid::uuid4()->toString(),
                                    'entity_account_commission_id'=> $compteUba->id,
                                    'type_operation'=>'self_retrait',
                                    'montant'=> $montant,
                                    'frais'=> $frais,
                                    'commission'=> $commissionBank,
                                    'reference_bcb'=> $referenceBcb,
                                    'reference_gtp'=> $referenceGtp,
                                    'status'=> 0,
                                    'deleted'=> 0,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()                
                                ],
                                [
                                    'id' => Uuid::uuid4()->toString(),
                                    'entity_account_commission_id'=> $compteElg->id,
                                    'type_operation'=>'self_retrait',
                                    'montant'=> $montant,
                                    'frais'=> $frais,
                                    'commission'=> $commissionElg,
                                    'reference_bcb'=> $referenceBcb,
                                    'reference_gtp'=> $referenceGtp,
                                    'status'=> 0,
                                    'deleted'=> 0,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]
                            ]);

                            $compteUba->solde += $commissionBank;
                            $compteUba->save();
                        }
                    // Fin repartition des frais
                    
                    
                    // Rechargement du compte de l'utilisateur

                        if($request->type == 'bmo'){
                            try{             
                                $partner_reference = substr($user->username, -4).time();
                                $base_url_bmo = env('BASE_BMO');
                    
                                // Realisation de la transaction
                    
                                $client = new Client();
                                $url = $base_url_bmo."/operations/credit";
                                
                                $body = [
                                    "amount" => $montant,
                                    "customer" => [
                                        "phone"=> "+".$user->username,
                                        "firstname"=> $user->lastname,
                                        "lastname"=> $user->name
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
                                $reference = $resultat_debit_bmo->reference;
                    
                            } catch (BadResponseException $e) {
                                return $this->sendError($e->getMessage(), [], 401);
                            }
                        }else{
                            try { 
                                $partner_reference = substr($user->username, -4).time();
                                $base_url_kkp = env('BASE_KKIAPAY');
                    
                                $client = new Client();
                                $url = $base_url_kkp."/api/v1/payments/deposit";
                                
                                $body = [
                                    "phoneNumber" => $user->username,
                                    "amount" => $montant,
                                    "reason" => "Retrait de Bcb virtuelle",
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

                                $resultat_credit_kkp = json_decode($response->getBody());
                                $reference = $resultat_credit_kkp->transactionId;
                                
                                $starttime = time();

                                $status = "PENDING";

                                while ($status == "PENDING") {
                                    $externalTransaction = $this->resultat_check_status_kkp($resultat_credit_kkp->transactionId);
                                    if ($externalTransaction->status == "SUCCESS"){
                                        $retrait->reference_operateur = $externalTransaction->externalTransactionId;
                                        $status = "SUCCESS";
                                    }else if($externalTransaction->status == "FAILED") {
                                        $status = "FAILED";
                                        return $this->sendError('Echec lors du crédit de votre compte', [], 500);
                                    }else{
                                        $now = time()-$starttime;
                                        if ($now > 125) {
                                            return $this->sendError('Erreur de confirmation du depot. Vous pouvez vous rapprocher de nos services pour plus d\'informations', [], 500);
                                        }
                                        $status = $externalTransaction->status;
                                    }
                                }
                            } catch (BadResponseException $e) {
                                return json_encode(['message' => $e->getMessage() , 'data' => []]);
                            }
                        }

                        $retrait->telephone = $user->username;
                        $retrait->reference_gtp = $referenceGtp;
                        $retrait->reference_bcb = $referenceBcb;
                        $retrait->reference_operateur = $reference;
                        $retrait->status = 'completed';
                        $retrait->montant_recu = $montant;
                        $retrait->solde_avant = $soldeAvantRetrait;
                        $retrait->solde_apres = $soldeApresRetrait;
                        $retrait->frais_bcb = $frais;
                        $retrait->validateur_id = 0;
                        $retrait->save();

                    // Fin rechargement du compte de l'utilisateur               

                    $message = 'Vous avez retirer '.$request->montant.' XOF de votre compte BCB Virtuelle. Frais d\'operation '.$frais_gtp+$frais.' XOF. Votre nouveau solde est: '.$soldeApresRetrait.' XOF.';
                    
                    if($user->sms == 1){
                        $this->sendSms($user->username,$message);
                    }else{
                        $arr = ['messages'=> $message,'objet'=>'Confirmation du rechargement','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$user->kycClient->email,])->send(new MailAlerte($arr));
                    }

                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return $this->sendError($error, [], 500);
                }

            // Fin retrait de la carte
            return $this->sendResponse($retrait, 'Retrait effectué avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function getRetraitsClient(Request $request){
        try {
            $retraitsAttentes = Retrait::where('user_client_id',$request->id)->where('deleted',0)->where('status',0)->orderBy('created_at','desc')->get();
            $retraitsFinalises = Retrait::where('user_client_id',$request->id)->where('deleted',0)->where('status',1)->orderBy('created_at','desc')->get();
            $selfRetraits = SelfRetrait::where('user_client_id',$request->id)->where('deleted',0)->where('status',1)->orderBy('created_at','desc')->get();
            
            $data = $dataAttentes = $dataFinalises= $dataSelfs = [];

            $i = 0;
            $lastKey = '';
            foreach ($retraitsAttentes as $key => $value) {
                $date = $this->get_day_name($value->created_at->timestamp);
                if($lastKey !== $date){
                    $lastKey = $date;
                    $i++;
                }
                $dataAttentes[$i]['id'] = $i;
                $dataAttentes[$i]['title'] = $date;
                $dataAttentes[$i]['transactions'][$value->id] = $value;
            }  

            $i = 0;
            $lastKey = '';
            foreach ($retraitsFinalises as $key => $value) {
                $date = $this->get_day_name($value->created_at->timestamp);
                if($lastKey !== $date){
                    $lastKey = $date;
                    $i++;
                }
                $dataFinalises[$i]['id'] = $i;
                $dataFinalises[$i]['title'] = $date;
                $dataFinalises[$i]['transactions'][$value->id] = $value;
            }  

            $i = 0;
            $lastKey = '';
            foreach ($selfRetraits as $key => $value) {
                $date = $this->get_day_name($value->created_at->timestamp);
                if($lastKey !== $date){
                    $lastKey = $date;
                    $i++;
                }
                $dataSelfs[$i]['id'] = $i;
                $dataSelfs[$i]['title'] = $date;
                $dataSelfs[$i]['transactions'][$value->id] = $value;
            }    
            
            
            $data['retraitsAttentes'] = $dataAttentes;
            $data['retraitsFinalises'] = $dataFinalises;
            $data['selfRetraits'] = $dataSelfs;
            
            return $this->sendResponse(collect($data), 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function getRetraitDetailClient(Request $request){
        try {
            $retrait = Retrait::where('id',$request->id)->first();
            $retrait->partenaire_name = $retrait->userPartenaire->partenaire->libelle;
            
            return $this->sendResponse($retrait, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function getSelfRetraitDetailClient(Request $request){
        try {
            $retrait = SelfRetrait::where('id',$request->id)->first();
            $retrait->carte_customer_id = $retrait->userCard->customer_id;
            $retrait->carte_last_digits = $retrait->userCard->last_digits;
            
            return $this->sendResponse($retrait, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }
    
    public function validationRetraitAttenteClient(Request $request){
        try {
            $encrypt_Key = env('ENCRYPT_KEY');
                
            $validator = Validator::make($request->all(), [
                'user_card_id' => 'required',
                'transaction_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }

            $token = JWTAuth::getToken();
            $userId = JWTAuth::getPayload($token)->toArray()['sub'];

      
            $retrait = Retrait::where('id',$request->transaction_id)->where('deleted',0)->where('status',0)->first();
            
            if($userId != $retrait->user_client_id){
                return  $this->sendError('Vous n\'etes pas autorisé à faire cette opération', [$userId,$retrait->user_client_id],401);
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
                    return $this->sendError($error, [], 500);
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
                $this->sendSms($retrait->userClient->username,$message);
            }else{
                $arr = ['messages'=> $message,'objet'=>'Confirmation du retrait','from'=>'noreply-bcv@bestcash.me'];
                Mail::to([$retrait->userClient->kycClient->email,])->send(new MailAlerte($arr));
            }

            $message = $retrait->userClient->name.' '.$retrait->userClient->lastname.' - Tel : '.$retrait->userClient->username.' a validé le retrait de '.$retrait->montant.'. Frais d\'operation : '.$retrait->frais.' XOF. Montant reçu par le client :'.$montant_recu.' Commission de l\'operation : '.$commission.' XOF.';
            $this->sendSms($retrait->userPartenaire->partenaire->telephone,$message);
            
            return $this->sendResponse($retrait, 'Votre opération de retrait a été confirmé avec succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }
    
    public function annulationRetraitAttenteClient(Request $request){
        try {
            $encrypt_Key = env('ENCRYPT_KEY');
                
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }

            $token = JWTAuth::getToken();
            $userId = JWTAuth::getPayload($token)->toArray()['sub'];

      
            $retrait = Retrait::where('id',$request->transaction_id)->where('deleted',0)->where('status','pending')->first();
            
            if($userId != $retrait->user_client_id){
                return  $this->sendError('Vous n\'etes pas autorisé à faire cette opération', [$userId,$retrait->user_client_id],401);
            }

            $retrait->status = 'canceled';
            $retrait->deleted = 1;
            $retrait->save();
            
            return $this->sendResponse($retrait, 'Succès');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }
    


    public function getBeneficiaries(Request $request){
        try {
            $beneficiaries = Beneficiaire::where('user_client_id',$request->user_id)->where('deleted',0)->get();

            foreach($beneficiaries as $beneficiary){
                $beneficiary->bcvBeneficiaries;
                $beneficiary->cardBeneficiaries;
                $beneficiary->momoBeneficiaries;
            }

            return $this->sendResponse(collect($beneficiaries), 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addBeneficiary(Request $request){
        try {
            $beneficiary = Beneficiaire::where('user_client_id',$request->user_id)->where('name',$request->name)->where('deleted',0)->first();

            if($beneficiary){
                return $this->sendError('Vous avez déjà un contact avec ce nom', [], 401);
            }
            
            $contacts = $request->data;
            foreach($contacts as $contact){
                if($contact['type'] != 'momo' && $contact['type'] != 'bmo' && $contact['type'] != 'bcv' && $contact['type'] != 'visa'){
                    return $this->sendError('Type de contact '.$contact['type'].' inconnu', [], 401);
                }
            }

            $beneficiary = Beneficiaire::create([
                'id' => Uuid::uuid4()->toString(),
                "user_client_id" => $request->user_id,
                "name" => $request->name,
                "deleted" => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);


            foreach($contacts as $contact){

                if($contact['type'] == 'momo' || $contact['type'] == 'bmo'){
                    BeneficiaireMomo::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "type" => $contact['type'],
                        "code" => $contact['code'],
                        "telephone" => $contact['telephone'],
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }else if($contact['type'] == 'bcv'){
                    $client = UserClient::where('username',$contact['username'])->first();
                    BeneficiaireBcv::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "user_client_id" => $client->id,
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }else if($contact['type'] == 'visa'){
                    BeneficiaireCard::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "customer_id" => $contact['customer_id'],
                        "last_digits" => $contact['last_digits'],
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }

            $beneficiary->bcvBeneficiaries;
            $beneficiary->cardBeneficiaries;
            $beneficiary->momoBeneficiaries;

            return $this->sendResponse($beneficiary, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function deleteBeneficiary(Request $request){
        try {
            $beneficiary = Beneficiaire::where('id',$request->id)->first();

            if(!$beneficiary){
                return $this->sendError('Contact non trouvé', [], 401);
            }

            $beneficiary->deleted = 1;
            $beneficiary->save();

            return $this->sendResponse($beneficiary, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function editBeneficiary(Request $request){
        try {
            $beneficiary = Beneficiaire::where('id',$request->id)->first();

            if(!$beneficiary){
                return $this->sendError('Contact non trouvé', [], 401);
            }

            $beneficiary->name = $request->name;
            $beneficiary->save();

            return $this->sendResponse($beneficiary, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addContact(Request $request){
        try {
            $beneficiary = Beneficiaire::where('id',$request->beneficiary_id)->first();

            $contacts = $request->data;

            foreach($contacts as $contact){

                if($contact['type'] == 'momo' || $contact['type'] == 'bmo'){
                    BeneficiaireMomo::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "type" => $contact['type'],
                        "code" => $contact['code'],
                        "telephone" => $contact['telephone'],
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }else if($contact['type'] == 'bcv'){
                    $client = UserClient::where('username',$contact['username'])->first();
                    BeneficiaireBcv::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "user_client_id" => $client->id,
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }else if($contact['type'] == 'visa'){
                    BeneficiaireCard::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "customer_id" => $contact['customer_id'],
                        "last_digits" => $contact['last_digits'],
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }

            $beneficiary->bcvBeneficiaries;
            $beneficiary->cardBeneficiaries;
            $beneficiary->momoBeneficiaries;

            return $this->sendResponse($beneficiary, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function deleteContact(Request $request){
        try {
            if($request->type == 'momo' || $request->type == 'bmo'){
                $contact = BeneficiaireMomo::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $contact->deleted = 1;
                $contact->save();
            }else if($request->type == 'bcv'){
                $contact = BeneficiaireBcv::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $contact->deleted = 1;
                $contact->save();
            }else if($request->type == 'visa'){
                $contact = BeneficiaireCard::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $contact->deleted = 1;
                $contact->save();
            }else{
                return $this->sendError('Type inconnu', [], 401);
            }

            return $this->sendResponse($contact, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function editContact(Request $request){
        try {
            if($request->type == 'momo' || $request->type == 'bmo'){
                $contact = BeneficiaireMomo::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $contact->code = $request->code;
                $contact->telephone = $request->telepone;
                $contact->save();
            }else if($request->type == 'bcv'){
                $contact = BeneficiaireBcv::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $client = UserClient::where('username',$request->username)->first();
                $contact->user_client_id = $client->id;
                $contact->save();
            }else if($request->type == 'visa'){
                $contact = BeneficiaireCard::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $contact->customer_id = $request->customer_id;
                $contact->last_digits = $request->last_digits;
                $contact->save();
            }else{
                return $this->sendError('Type inconnu', [], 401);
            }
            return $this->sendResponse($contact, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function checkClient(Request $request){
        try {
            $client = UserClient::where('id',$request->id)->first();
            if(!$client){
                return $this->sendError('Ce client n\'existe pas', [], 404);
            }
            $client->makeHidden(['password','code_otp']);

            return $this->sendResponse($client, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function checkClientUsername(Request $request){
        try {
            $client = UserClient::where('username',$request->username)->first();
            if(!$client){
                return $this->sendError('Ce client n\'existe pas', [], 404);
            }
            $cards = UserCard::where('user_client_id',$client->id)->get();
            return $this->sendResponse($cards, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }    

    public function addTransfertClient(Request $request){
        try {
            // Recuperation variable et verification faisabilité

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
                    return  $this->sendError($validator->errors()->first(), [],422);
                }

                $sender =  UserClient::where('deleted',0)->where('id',$request->user_id)->first();
                $sender_card =  UserCard::where('deleted',0)->where('id',$request->user_card_id)->first();
                
                $isRestrictByAdmin = isRestrictByAdmin($request->montant,$sender->id,null,'transfert');
                if($isRestrictByAdmin != 'ok'){
                    return $this->sendError($isRestrictByAdmin, [], 401);
                }

            // Fin recuperation variable et verification faisabilité
            
            // Recuperation solde avant et calcul des frais
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

            // Fin recuperation solde avant et calcul des frais
            
            if($request->type == 'card'){
                // Transfert de la carte        
                    $client = new Client();
                    $url = $base_url."accounts/fund-transfer";
                    
                    $body = [
                        "paymentType" => "C2C",
                        "fromAccountId" => decryptData($sender_card->customer_id, $encrypt_Key),
                        "transferAmount" => $request->montant,
                        "currencyCode" => "XOF",
                        "toAccountId" => decryptData($request->customer_id, $encrypt_Key),
                        "last4Digits" => decryptData($request->last_digits, $encrypt_Key),
                        "referenceMemo" => 'Transfert de '.$request->montant.' vers la carte '.decryptData($request->customer_id, $encrypt_Key).'.'
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
        
                        $referenceGtp = $resultat->transactionId;
                        $soldeApres = $soldeAvant - $request->montant;
                        $referenceBcb = 'TRF-'.time();

                        

                        $transfert = TransfertOut::create([
                            'id' => Uuid::uuid4()->toString(),
                            "type" => $request->type,
                            "name" => $request->name,
                            "lastname" => $request->lastname,
                            "user_client_id" => $sender->id,
                            "user_card_id" => $sender->id,
                            "receveur_customer_id" => $request->customer_id,
                            "receveur_last_digits" => $request->last_digits,
                            "montant" => $request->montant,
                            "reference_gtp_debit" => $referenceGtp,
                            "reference_bcb" => $referenceBcb,
                            "frais_bcb" => 0,
                            "libelle" => 'Transfert de '.$request->montant.' vers la carte '.decryptData($request->customer_id, $encrypt_Key).'.',
                            "solde_avant" => $soldeAvant,
                            "solde_apres" => $soldeApres,
                            "status" => 1,
                            "deleted" => 0,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    } catch (BadResponseException $e) {
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return $this->sendError($error, [], 500);
                    }
                
                // Envoie de notification a l'expediteur 
                    $message = 'Transfert de '.$request->montant.' vers la carte '.$request->customer_id.'.';   
                    if($sender->sms == 1){
                        $this->sendSms($sender->username,$message);
                    }else{
                        $email = $sender->kycClient->email;
                        $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$email,])->send(new MailAlerte($arr));
                    }
                return $this->sendResponse($transfert, 'Transfert effectué avec succes.');
            }else if($request->type == 'bcv'){

                $receveur =  UserClient::where('deleted',0)->where('username',$request->receveur_telephone)->first();
                
                $receiver =  $receveur->userCard->first();
                // Transfert de la carte        
                    $client = new Client();
                    $url = $base_url."accounts/fund-transfer";
                    
                    $body = [
                        "paymentType" => "C2C",
                        "fromAccountId" => decryptData($sender_card->customer_id, $encrypt_Key),
                        "transferAmount" => $request->montant,
                        "currencyCode" => "XOF",
                        "toAccountId" => decryptData($receiver->customer_id, $encrypt_Key),
                        "last4Digits" => decryptData($receiver->last_digits, $encrypt_Key),
                        "referenceMemo" => 'Transfert de '.$request->montant.' vers la carte principale'.decryptData($receiver->customer_id, $encrypt_Key).'.'
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
        
                        $referenceGtp = $resultat->transactionId;
                        $soldeApres = $soldeAvant - $request->montant;
                        $referenceBcb = 'TRF-'.time();

                        

                        $transfert = TransfertOut::create([
                            'id' => Uuid::uuid4()->toString(),
                            "type" => $request->type,
                            "user_client_id" => $sender->id,
                            "user_card_id" => $sender->id,
                            "receveur_customer_id" => $receiver->customer_id,
                            "receveur_last_digits" => $receiver->last_digits,
                            "montant" => $request->montant,
                            "reference_gtp_debit" => $referenceGtp,
                            "reference_bcb" => $referenceBcb,
                            "frais_bcb" => 0,
                            "libelle" => 'Transfert de '.$request->montant.' vers la carte '.decryptData($receiver->customer_id, $encrypt_Key).'.',
                            "solde_avant" => $soldeAvant,
                            "solde_apres" => $soldeApres,
                            "status" => 1,
                            "deleted" => 0,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    } catch (BadResponseException $e) {
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return $this->sendError($error, [], 500);
                    }
                
                // Envoie de notification a l'expediteur 
                    $message = 'Transfert de '.$request->montant.' vers la carte '.$receiver->customer_id.' de l\'utilisateur '.$request->receveur_telephone.'.';   
                    if($sender->sms == 1){
                        $this->sendSms($sender->username,$message);
                    }else{
                        $email = $sender->kycClient->email;
                        $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$email,])->send(new MailAlerte($arr));
                    }
                return $this->sendResponse($transfert, 'Transfert effectué avec succes.');
            }else{
                $frais = 0;
                // Retrait de la carte
                    $requestId = GtpRequest::create([
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
        
                    $client = new Client();
                    $url = $base_url."accounts/".decryptData($sender_card->customer_id, $encrypt_Key)."/transactions";
                    
                    $body = [
                        "transferType" => "CardToWallet",
                        "transferAmount" => $request->montant,
                        "currencyCode" => "XOF",
                        "last4Digits" => decryptData($sender_card->last_digits, $encrypt_Key),
                        "referenceMemo" => 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.'
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
        
                        $resultat = json_decode($response->getBody());                    
        
                        $referenceGtp = $resultat->transactionId;
                        $soldeApres = $soldeAvant - $request->montant - $frais;
                        $referenceBcb = 'TRF-'.time();
                    } catch (BadResponseException $e) {
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return $this->sendError($error, [], 500);
                    }

                // Envoie vers le numero du client

                    if($request->type == 'bmo'){                        
                        try{             
                            $partner_reference = substr($request->receveur_telephone, -4).time();
                            $base_url_bmo = env('BASE_BMO');
                
                            // Realisation de la transaction
                
                            $client = new Client();
                            $url = $base_url_bmo."/operations/credit";
                            
                            $body = [
                                "amount" => $request->montant,
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
                            
                            $transfert = TransfertOut::create([
                                'id' => Uuid::uuid4()->toString(),
                                "type" => $request->type,
                                "user_client_id" => $sender->id,
                                "user_card_id" => $sender_card->id,
                                "receveur_telephone" => $request->receveur_telephone,
                                "reference_operateur" => $resultat_debit_bmo->reference,
                                "montant" => $request->montant,
                                "reference_gtp_debit" => $referenceGtp,
                                "reference_bcb" => $referenceBcb,
                                "frais_bcb" => $frais,
                                "libelle" => 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.',
                                "solde_avant" => $soldeAvant,
                                "solde_apres" => $soldeApres,
                                "status" => 1,
                                "deleted" => 0,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);
                
                        } catch (BadResponseException $e) {
                            return $this->sendError($e->getMessage(), [], 401);
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
                                $externalTransaction = $this->resultat_check_status_kkp($resultat->transactionId);
                                if ($externalTransaction->status == "SUCCESS"){
                                    $reference_operateur = $externalTransaction->externalTransactionId;
                                    $transfert = TransfertOut::create([
                                        'id' => Uuid::uuid4()->toString(),
                                        "type" => $request->type,
                                        "user_client_id" => $sender->id,
                                        "user_card_id" => $sender_card->id,
                                        "receveur_telephone" => $request->receveur_telephone,
                                        "reference_operateur" => $reference_operateur,
                                        "montant" => $request->montant,
                                        "reference_gtp" => $referenceGtp,
                                        "reference_bcb" => $referenceBcb,
                                        "frais_bcb" => 0,
                                        "libelle" => 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.',
                                        "solde_avant" => $soldeAvant,
                                        "solde_apres" => $soldeApres,
                                        "status" => 1,
                                        "deleted" => 0,
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now()
                                    ]);
                                    $status = "SUCCESS";
                                }else if($externalTransaction->status == "FAILED") {
                                    $status = "FAILED";
                                    return $this->sendError('Echec lors du paiement du transfert. Contacter notre service clientèle', [], 500);
                                }else{
                                    $now = time()-$starttime;
                                    if ($now > 125) {
                                        return $this->sendError('Echec de confirmation du transfert. Contacter notre service clientèle', [], 500);
                                    }
                                    $status = $externalTransaction->status;
                                }
                            }
                        } catch (BadResponseException $e) {
                            return json_encode(['message' => $e->getMessage() , 'data' => []]);
                        }
                    }
                
                // Envoie de notification a l'expediteur 
                    $message = 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.';   
                    if($sender->sms == 1){
                        $this->sendSms($sender->username,$message);
                    }else{
                        $email = $sender->kycClient->email;
                        $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$email,])->send(new MailAlerte($arr));
                    }   
                return $this->sendResponse($transfert, 'Transfert effectué avec succes.');         
            }

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addNewTransfertClient(Request $request){
        try {
            // Recuperation variable et verification faisabilité

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
                    return  $this->sendError($validator->errors()->first(), [],422);
                }

                $sender =  UserClient::where('deleted',0)->where('id',$request->user_id)->first();
                $sender_card =  UserCard::where('deleted',0)->where('id',$request->user_card_id)->first();
                
                $isRestrictByAdmin = isRestrictByAdmin($request->montant,$sender->id,null,'transfert');
                if($isRestrictByAdmin != 'ok'){
                    return $this->sendError($isRestrictByAdmin, [], 401);
                }

            // Fin recuperation variable et verification faisabilité
            
            // Recuperation solde avant et calcul des frais

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

            // Fin recuperation solde avant et calcul des frais

            // Retrait de la carte

                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
    
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
    
                    $resultat = json_decode($response->getBody());                    
    
                    $referenceGtpDebit = $resultat->transactionId;
                    $soldeApres = $soldeAvant - $montantWithFee;

                    
                    // Initiation transaction
                        
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
                                "type" => $request->type,
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
                                    "type" => $request->type,
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
                                    "type" => $request->type,
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
                                    "type" => $request->type,
                                    "is_paid" => 1,
                                    "libelle" => 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.',
                                    "status" => 'pending',
                                    "deleted" => 0,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]); 
                            }
                        }

                    // Fin initiation transaction
                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return $this->sendError($error, [], 500);
                }

            //Fin retrait de la carte
            
            if($request->type == 'card'){
                

                // Transfert vers la carte de l'utilisateur

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
                        $transfert->save();
                        
                        // Envoie de notification a l'expediteur 
                            $message = 'Transfert de '.$request->montant.' vers la carte '.$request->customer_id.'.';   
                            if($sender->sms == 1){
                                $this->sendSms($sender->username,$message);
                            }else{
                                $email = $sender->kycClient->email;
                                $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                                Mail::to([$email,])->send(new MailAlerte($arr));
                            }
                        // Fin envoie de notification a l'expediteur 
                    } catch (BadResponseException $e) {
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return $this->sendError($error, [], 500);
                    }

                // Fin transfert vers la carte de l'utilisateur
                
                // Envoie de notification a l'expediteur 
                    $message = 'Transfert de '.$request->montant.' vers la carte '.$request->customer_id.'.';   
                    if($sender->sms == 1){
                        $this->sendSms($sender->username,$message);
                    }else{
                        $email = $sender->kycClient->email;
                        $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$email,])->send(new MailAlerte($arr));
                    }
                // Fin envoie de notification a l'expediteur
                return $this->sendResponse($transfert, 'Transfert effectué avec succes.');
            }else {            
                if($request->type == 'bcv'){


                    // Transfert vers la carte de l'utilisateur

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
                            
                            // Envoie de notification a l'expediteur 
                                $message = 'Transfert de '.$montant.' XOF vers la carte principale '.decryptData($receiverFirstCard->last_digits, $encrypt_Key).' de '. $receiver->name.' '.$receiver->lastname.'.';   
                                if($sender->sms == 1){
                                    $this->sendSms($sender->username,$message);
                                }else{
                                    $email = $sender->kycClient->email;
                                    $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                                    Mail::to([$email,])->send(new MailAlerte($arr));
                                }
                            // Fin envoie de notification a l'expediteur 
                        } catch (BadResponseException $e) {
                            $json = json_decode($e->getResponse()->getBody()->getContents());
                            $error = $json->title.'.'.$json->detail;
                            return $this->sendError($error, [], 500);
                        }

                    // Fin transfert vers la carte de l'utilisateur

                    return $this->sendResponse($transfert, 'Transfert effectué avec succes.');
                }else if($request->type == 'bmo'){                        
                    try{             
                        $partner_reference = substr($request->receveur_telephone, -4).time();
                        $base_url_bmo = env('BASE_BMO');
            
                        // Realisation de la transaction
            
                        $client = new Client();
                        $url = $base_url_bmo."/operations/credit";
                        
                        $body = [
                            "amount" => $request->montant,
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
                        $transfert->save();
            
                    } catch (BadResponseException $e) {
                        return $this->sendError($e->getMessage(), [], 401);
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
                            $externalTransaction = $this->resultat_check_status_kkp($resultat->transactionId);
                            if ($externalTransaction->status == "SUCCESS"){
                                $reference_operateur = $externalTransaction->externalTransactionId;
                                
                                $transfert->receveur_telephone = $request->receveur_telephone;
                                $transfert->reference_operateur = $reference_operateur;
                                $transfert->solde_avant = $soldeAvant;
                                $transfert->solde_apres = $soldeApres;
                                $transfert->status = 'completed';
                                $transfert->save();
                                $status = "SUCCESS";
                            }else if($externalTransaction->status == "FAILED") {
                                $status = "FAILED";
                                return $this->sendError('Echec lors du paiement du transfert. Contacter notre service clientèle', [], 500);
                            }else{
                                $now = time()-$starttime;
                                if ($now > 125) {
                                    return $this->sendError('Echec de confirmation du transfert. Contacter notre service clientèle', [], 500);
                                }
                                $status = $externalTransaction->status;
                            }
                        }
                    } catch (BadResponseException $e) {
                        return json_encode(['message' => $e->getMessage() , 'data' => []]);
                    }
                }
            
                // Envoie de notification a l'expediteur 

                    $message = 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.';   
                    if($sender->sms == 1){
                        $this->sendSms($sender->username,$message);
                    }else{
                        $email = $sender->kycClient->email;
                        $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$email,])->send(new MailAlerte($arr));
                    }   
                    return $this->sendResponse($transfert, 'Transfert effectué avec succes.'); 

                // Fin envoie de notification a l'expediteur         
            }

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function completeTransfertClient(Request $request){
        try {
            // Recuperation variable et verification faisabilité

                $encrypt_Key = env('ENCRYPT_KEY');
                $base_url = env('BASE_GTP_API');
                $programID = env('PROGRAM_ID');
                $authLogin = env('AUTH_LOGIN');
                $authPass = env('AUTH_PASS');
                $validator = Validator::make($request->all(), [
                    'transaction_id' => ["required" , "string"]
                ]);

                $transfert = TransfertOut::where('id',$request->transaction_id)->first();

                $sender =  UserClient::where('deleted',0)->where('id',$transfert->user_client_id)->first();
                $sender_card =  UserCard::where('deleted',0)->where('id',$transfert->user_card_id)->first();
                
                $isRestrictByAdmin = isRestrictByAdmin($transfert->montant,$sender->id,null,'transfert');
                if($isRestrictByAdmin != 'ok'){
                    return $this->sendError($isRestrictByAdmin, [], 401);
                }

                $soldeAvant = getUserSolde($sender->id); 
                $soldeApres = $soldeAvant - $transfert->montant - $transfert->frais_bcb;
            // Fin recuperation variable et verification faisabilité

            
            if($transfert->type == 'card'){
                

                // Transfert vers la carte de l'utilisateur

                    $client = new Client();
                    $url = $base_url."accounts/".decryptData($transfert->receveur_customer_id, $encrypt_Key)."/transactions";
                    
                    $body = [
                        "transferType" => "WalletToCard",
                        "transferAmount" => round($transfert->montant,2),
                        "currencyCode" => "XOF",
                        "last4Digits" => decryptData($transfert->receveur_last_digits, $encrypt_Key),
                        "referenceMemo" => 'Transfert de '.$transfert->montant.' vers la carte '.decryptData($transfert->receveur_customer_id, $encrypt_Key).'.'
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

                        $transfert->reference_gtp_credit = $referenceGtpCredit;
                        $transfert->libelle = 'Transfert de '.$montant.' vers la carte '.decryptData($transfert->receveur_customer_id, $encrypt_Key).'.';
                        $transfert->solde_avant = $soldeAvant;
                        $transfert->solde_apres = $soldeApres;
                        $transfert->status = 'completed';
                        $transfert->save();
                        
                        // Envoie de notification a l'expediteur 
                            $message = 'Transfert de '.$transfert->montant.' vers la carte '.$transfert->receveur_customer_id.'.';   
                            if($sender->sms == 1){
                                $this->sendSms($sender->username,$message);
                            }else{
                                $email = $sender->kycClient->email;
                                $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                                Mail::to([$email,])->send(new MailAlerte($arr));
                            }
                        // Fin envoie de notification a l'expediteur 
                    } catch (BadResponseException $e) {
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return $this->sendError($error, [], 500);
                    }

                // Fin transfert vers la carte de l'utilisateur
                
                // Envoie de notification a l'expediteur 
                    $message = 'Transfert de '.$transfert->montant.' vers la carte '.$transfert->receveur_customer_id.'.';   
                    if($sender->sms == 1){
                        $this->sendSms($sender->username,$message);
                    }else{
                        $email = $sender->kycClient->email;
                        $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$email,])->send(new MailAlerte($arr));
                    }
                // Fin envoie de notification a l'expediteur
                return $this->sendResponse($transfert, 'Transfert effectué avec succes.');
            }else {            
                if($transfert->type == 'bcv'){

                    $receiver =  UserClient::where('deleted',0)->where('username',$transfert->receveur_telephone)->first();

                    // Transfert vers la carte de l'utilisateur

                        $client = new Client();
                        $url = $base_url."accounts/".decryptData($transfert->receveur_customer_id, $encrypt_Key)."/transactions";
                        
                        $body = [
                            "transferType" => "WalletToCard",
                            "transferAmount" => round($transfert->montant,2),
                            "currencyCode" => "XOF",
                            "last4Digits" => decryptData($transfert->receveur_last_digits, $encrypt_Key),
                            "referenceMemo" => 'Vous avez reçu de '.$transfert->montant.' sur votre carte.'
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

                            $transfert->reference_gtp_credit = $referenceGtpCredit;
                            $transfert->libelle = 'Transfert de '.$transfert->montant.' vers la carte '.decryptData($transfert->receveur_customer_id, $encrypt_Key).'.';
                            $transfert->solde_avant = $soldeAvant;
                            $transfert->solde_apres = $soldeApres;
                            $transfert->status = 'completed';
                            $transfert->save();
                            
                            // Envoie de notification a l'expediteur 
                                $message = 'Transfert de '.$transfert->montant.' vers la carte '.$transfert->receveur_customer_id.' de l\'utilisateur '.$transfert->receveur_telephone.'.';   
                                if($sender->sms == 1){
                                    $this->sendSms($sender->username,$message);
                                }else{
                                    $email = $sender->kycClient->email;
                                    $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                                    Mail::to([$email,])->send(new MailAlerte($arr));
                                }
                            // Fin envoie de notification a l'expediteur 
                        } catch (BadResponseException $e) {
                            $json = json_decode($e->getResponse()->getBody()->getContents());
                            $error = $json->title.'.'.$json->detail;
                            return $this->sendError($error, [], 500);
                        }

                    // Fin transfert vers la carte de l'utilisateur

                    return $this->sendResponse($transfert, 'Transfert effectué avec succes.');
                }else if($transfert->type == 'bmo'){                        
                    try{             
                        $partner_reference = substr($transfert->receveur_telephone, -4).time();
                        $base_url_bmo = env('BASE_BMO');
            
                        // Realisation de la transaction
            
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

                        $transfert->reference_operateur = $resultat_debit_bmo->reference;
                        $transfert->libelle = 'Transfert de '.$transfert->montant.' vers le numero '.$transfert->receveur_telephone.'.';
                        $transfert->solde_avant = $soldeAvant;
                        $transfert->solde_apres = $soldeApres;
                        $transfert->status = 'completed';
                        $transfert->save();
            
                    } catch (BadResponseException $e) {
                        return $this->sendError($e->getMessage(), [], 401);
                    }
                }else{
                    try { 
                        $base_url_kkp = env('BASE_KKIAPAY');
            
                        $client = new Client();
                        $url = $base_url_kkp."/api/v1/payments/deposit";
                        
                        $partner_reference = substr($transfert->receveur_telephone, -4).time();
                        $body = [
                            "phoneNumber" => $transfert->receveur_telephone,
                            "amount" => $transfert->montant,
                            "reason" => 'Transfert de '.$transfert->montant.' provenant de '.$sender->username.' effectué depuis son compte BCB Virtuelle. ID de la carte : '.$sender->customer_id.'.',
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
                                
                                $transfert->reference_operateur = $reference_operateur;
                                $transfert->libelle = 'Transfert de '.$transfert->montant.' vers le numero '.$transfert->receveur_telephone.'.';
                                $transfert->solde_avant = $soldeAvant;
                                $transfert->solde_apres = $soldeApres;
                                $transfert->status = 'completed';
                                $transfert->save();
                                $status = "SUCCESS";
                            }else if($externalTransaction->status == "FAILED") {
                                $status = "FAILED";
                                return $this->sendError('Echec lors du paiement du transfert. Contacter notre service clientèle', [], 500);
                            }else{
                                $now = time()-$starttime;
                                if ($now > 125) {
                                    return $this->sendError('Echec de confirmation du transfert. Contacter notre service clientèle', [], 500);
                                }
                                $status = $externalTransaction->status;
                            }
                        }
                    } catch (BadResponseException $e) {
                        return json_encode(['message' => $e->getMessage() , 'data' => []]);
                    }
                }
            
                // Envoie de notification a l'expediteur 

                    $message = 'Transfert de '.$transfert->montant.' vers le numero '.$transfert->receveur_telephone.'.';   
                    if($sender->sms == 1){
                        $this->sendSms($sender->username,$message);
                    }else{
                        $email = $sender->kycClient->email;
                        $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$email,])->send(new MailAlerte($arr));
                    }   
                    return $this->sendResponse($transfert, 'Transfert effectué avec succes.'); 

                // Fin envoie de notification a l'expediteur         
            }

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
        try {

            $validator = Validator::make($request->all(), [
                'transaction_id' => ["required" , "string"]
            ]);

            if($request->type == 'card'){
                // Initiation transaction
                    $transfert = TransfertOut::where('id',$request->transaction_id)->first();
    
                // Fin initiation transaction

                // Transfert carte a carte directe        
                    $client = new Client();
                    $url = $base_url."accounts/fund-transfer";
                    
                    $body = [
                        "paymentType" => "C2C",
                        "fromAccountId" => decryptData($sender_card->customer_id, $encrypt_Key),
                        "transferAmount" => $transfert->montant,
                        "currencyCode" => "XOF",
                        "toAccountId" => decryptData($transfert->receveur_customer_id, $encrypt_Key),
                        "last4Digits" => decryptData($transfert->receveur_last_digits, $encrypt_Key),
                        "referenceMemo" => 'Transfert de '.$transfert->montant.' vers la carte '.decryptData($transfert->receveur_customer_id, $encrypt_Key).'.'
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
        
                        $referenceGtp = $resultat->transactionId;
                        $soldeApres = $soldeAvant - $transfert->montant;

                        
                        $transfert->receveur_customer_id = $request->customer_id;
                        $transfert->receveur_last_digits = $request->last_digits;
                        $transfert->reference_gtp_debit = $referenceGtp;
                        $transfert->libelle = 'Transfert de '.$request->montant.' vers la carte '.decryptData($request->customer_id, $encrypt_Key).'.';
                        $transfert->solde_avant = $soldeAvant;
                        $transfert->solde_apres = $soldeApres;
                        $transfert->status = 'completed';
                        $transfert->save();

                    } catch (BadResponseException $e) {
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return $this->sendError($error, [], 500);
                    }
                // Fin transfert carte a carte directe
                
                // Envoie de notification a l'expediteur 
                    $message = 'Transfert de '.$request->montant.' vers la carte '.$request->customer_id.'.';   
                    if($sender->sms == 1){
                        $this->sendSms($sender->username,$message);
                    }else{
                        $email = $sender->kycClient->email;
                        $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$email,])->send(new MailAlerte($arr));
                    }
                // Fin envoie de notification a l'expediteur
                return $this->sendResponse($transfert, 'Transfert effectué avec succes.');
            }else {

                // Retrait de la carte

                    $requestId = GtpRequest::create([
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
        
                    $client = new Client();
                    $url = $base_url."accounts/".decryptData($sender_card->customer_id, $encrypt_Key)."/transactions";
                    
                    $body = [
                        "transferType" => "CardToWallet",
                        "transferAmount" => $montantWithFee,
                        "currencyCode" => "XOF",
                        "last4Digits" => decryptData($sender_card->last_digits, $encrypt_Key),
                        "referenceMemo" => 'Transfert de '.$montant.' vers le numero '.$request->receveur_telephone.'. Frais de transaction : '.$frais
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
        
                        $resultat = json_decode($response->getBody());                    
        
                        $referenceGtpDebit = $resultat->transactionId;
                        $soldeApres = $soldeAvant - $montantWithFee;

                        
                        // Initiation transaction
                            
                            $referenceBcb = 'trf-'.Uuid::uuid4()->toString();
                            $transfert = TransfertOut::create([
                                'id' => Uuid::uuid4()->toString(),
                                "user_client_id" => $sender->id,
                                "user_card_id" => $sender->id,
                                "reference_bcb" => $referenceBcb,
                                "montant" => $montant,
                                "frais_bcb" => $frais,
                                "type" => $request->type,
                                "is_paid" => 1,
                                "status" => 'pending',
                                "deleted" => 0,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);

                        // Fin initiation transaction
                    } catch (BadResponseException $e) {
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return $this->sendError($error, [], 500);
                    }

                //Fin retrait de la carte
            
                if($request->type == 'bcv'){

                    $receiver =  UserClient::where('deleted',0)->where('username',$request->receveur_telephone)->first();
            
                    $receiverFirstCard =  $receiver->userCard->first();

                    // Transfert vers la carte de l'utilisateur

                        $client = new Client();
                        $url = $base_url."accounts/".decryptData($receiverFirstCard->customer_id, $encrypt_Key)."/transactions";
                        
                        $body = [
                            "transferType" => "WalletToCard",
                            "transferAmount" => $montant,
                            "currencyCode" => "XOF",
                            "last4Digits" => decryptData($receiverFirstCard->last_digits, $encrypt_Key),
                            "referenceMemo" => 'Transfert de '.$montant.' vers la carte principale '.decryptData($receiverFirstCard->customer_id, $encrypt_Key).' de '. $receiver->name.' '.$receiver->lastname.'.'
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

                            $transfert->receveur_last_digits = $receiverFirstCard->last_digits;
                            $transfert->reference_gtp_debit = $referenceGtpDebit;
                            $transfert->reference_gtp_credit = $referenceGtpCredit;
                            $transfert->libelle = 'Transfert de '.$montant.' vers la carte '.decryptData($receiverFirstCard->customer_id, $encrypt_Key).'.';
                            $transfert->solde_avant = $soldeAvant;
                            $transfert->solde_apres = $soldeApres;
                            $transfert->status = 'completed';
                            $transfert->save();
                            
                            // Envoie de notification a l'expediteur 
                                $message = 'Transfert de '.$request->montant.' vers la carte '.$receiver->customer_id.' de l\'utilisateur '.$request->receveur_telephone.'.';   
                                if($sender->sms == 1){
                                    $this->sendSms($sender->username,$message);
                                }else{
                                    $email = $sender->kycClient->email;
                                    $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                                    Mail::to([$email,])->send(new MailAlerte($arr));
                                }
                            // Fin envoie de notification a l'expediteur 
                        } catch (BadResponseException $e) {
                            $json = json_decode($e->getResponse()->getBody()->getContents());
                            $error = $json->title.'.'.$json->detail;
                            return $this->sendError($error, [], 500);
                        }

                    // Fin transfert vers la carte de l'utilisateur

                    return $this->sendResponse($transfert, 'Transfert effectué avec succes.');
                }else if($request->type == 'bmo'){                        
                    try{             
                        $partner_reference = substr($request->receveur_telephone, -4).time();
                        $base_url_bmo = env('BASE_BMO');
            
                        // Realisation de la transaction
            
                        $client = new Client();
                        $url = $base_url_bmo."/operations/credit";
                        
                        $body = [
                            "amount" => $request->montant,
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
                        $transfert->reference_gtp_debit = $referenceGtp;
                        $transfert->libelle = 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.';
                        $transfert->solde_avant = $soldeAvant;
                        $transfert->solde_apres = $soldeApres;
                        $transfert->status = 'completed';
                        $transfert->save();
            
                    } catch (BadResponseException $e) {
                        return $this->sendError($e->getMessage(), [], 401);
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
                            $externalTransaction = $this->resultat_check_status_kkp($resultat->transactionId);
                            if ($externalTransaction->status == "SUCCESS"){
                                $reference_operateur = $externalTransaction->externalTransactionId;
                                
                                $transfert->receveur_telephone = $request->receveur_telephone;
                                $transfert->reference_operateur = $reference_operateur;
                                $transfert->reference_gtp_debit = $referenceGtp;
                                $transfert->libelle = 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.';
                                $transfert->solde_avant = $soldeAvant;
                                $transfert->solde_apres = $soldeApres;
                                $transfert->status = 'completed';
                                $transfert->save();
                                $status = "SUCCESS";
                            }else if($externalTransaction->status == "FAILED") {
                                $status = "FAILED";
                                return $this->sendError('Echec lors du paiement du transfert. Contacter notre service clientèle', [], 500);
                            }else{
                                $now = time()-$starttime;
                                if ($now > 125) {
                                    return $this->sendError('Echec de confirmation du transfert. Contacter notre service clientèle', [], 500);
                                }
                                $status = $externalTransaction->status;
                            }
                        }
                    } catch (BadResponseException $e) {
                        return json_encode(['message' => $e->getMessage() , 'data' => []]);
                    }
                }
            
                // Envoie de notification a l'expediteur 

                    $message = 'Transfert de '.$request->montant.' vers le numero '.$request->receveur_telephone.'.';   
                    if($sender->sms == 1){
                        $this->sendSms($sender->username,$message);
                    }else{
                        $email = $sender->kycClient->email;
                        $arr = ['messages'=> $message,'objet'=>'Alerte transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$email,])->send(new MailAlerte($arr));
                    }   
                    return $this->sendResponse($transfert, 'Transfert effectué avec succes.'); 

                // Fin envoie de notification a l'expediteur         
            }

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }
    
    public function getTransfertsClient(Request $request){
        try {
            $transfertOuts = TransfertOut::where('user_client_id',$request->id)->where('deleted',0)->orderBy('created_at','desc')->get();
            $transfertIns = TransfertIn::where('user_client_id',$request->id)->where('deleted',0)->where('status',1)->orderBy('created_at','desc')->get();
            $transfertPendings = TransfertIn::where('user_client_id',$request->id)->where('deleted',0)->where('status',0)->orderBy('created_at','desc')->get();

            $data = $dataAttentes = $dataOuts= $dataIns = [];

            $i = 0;
            $lastKey = '';
            foreach ($transfertOuts as $key => $value) {
                $date = $this->get_day_name($value->created_at->timestamp);
                if($lastKey !== $date){
                    $lastKey = $date;
                    $i++;
                }
                $dataOuts[$i]['id'] = $i;
                $dataOuts[$i]['title'] = $date;
                $dataOuts[$i]['transactions'][$value->id] = $value;
            }  

            $i = 0;
            $lastKey = '';
            foreach ($transfertIns as $key => $value) {
                $date = $this->get_day_name($value->created_at->timestamp);
                if($lastKey !== $date){
                    $lastKey = $date;
                    $i++;
                }
                $value->transfertEntrant = $value->transfertOut;
                $value->emmeteur = $value->transfertOut->userClient;
                $dataIns[$i]['id'] = $i;
                $dataIns[$i]['title'] = $date;
                $dataIns[$i]['transactions'][$value->id] = $value;
            }  

            $i = 0;
            $lastKey = '';
            foreach ($transfertPendings as $key => $value) {
                $date = $this->get_day_name($value->created_at->timestamp);
                if($lastKey !== $date){
                    $lastKey = $date;
                    $i++;
                }
                $value->transfertEntrant = $value->transfertOut;
                $value->emmeteur = $value->transfertOut->userClient;
                $dataAttentes[$i]['id'] = $i;
                $dataAttentes[$i]['title'] = $date;
                $dataAttentes[$i]['transactions'][$value->id] = $value;
            } 

            
            $data['ins'] = $dataIns;
            $data['outs'] = $dataOuts;
            $data['pendings'] = $dataAttentes;

            return $this->sendResponse(collect($data), 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function getTransfertOutDetailClient(Request $request){
        try {
            $transfert = TransfertOut::where('id',$request->id)->first();
            $transfert->carte_customer_id = $transfert->userCard->customer_id;
            $transfert->carte_last_digits = $transfert->userCard->last_digits;
            $transfert->receveur = $transfert->userClient;
            
            return $this->sendResponse($transfert, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function getTransfertInDetailClient(Request $request){
        try {
            $transfert = TransfertIn::where('id',$request->id)->first();
            $transfert->carte_customer_id = $transfert->userCard->customer_id;
            $transfert->carte_last_digits = $transfert->userCard->last_digits;
            $transfert->out = $transfert->transfertOut;            
            $transfert->emmeteur = $transfert->transfertOut->userClient;
            return $this->sendResponse($transfert, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function validationTransfertAttenteClient(Request $request){
        try {
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            /*$validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            } */         
            
            $transfert = TransfertIn::where('id',$request->id)->where('deleted',0)->first();
            if(!$transfert){
                return $this->sendError("Cette transaction n'existe pas pour vous", [], 500);
            }else{
                //recuperation solde avant  
                    $soldeAvant = getUserSolde($transfert->userClient->id);
                
                // Realisation transfert    
                    $requestId = GtpRequest::create([
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
        
                    $client = new Client();
                    $url = $base_url."accounts/".$transfert->userCard->customer_id."/transactions";
                    
                    $body = [
                        "transferType" => "WalletToCard",
                        "transferAmount" => $transfert->montant_recu,
                        "currencyCode" => "XOF",
                        "last4Digits" => $transfert->userCard->last_digits,
                        "referenceMemo" => 'Transfert de '.$transfert->montant_recu.' de la carte '.$transfert->transfertOut->userCard->customer_id.'.'
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
        
                        $resultat = json_decode($response->getBody());
                        
                        $transfert->solde_avant = $soldeAvant;
                        $transfert->solde_apres = $soldeAvant + $transfert->montant_recu;
                        $transfert->status = 1;
                        $transfert->transfertOut->status = 1;
                        $transfert->validate_time = Carbon::now();
                        $transfert->save();
                    } catch (BadResponseException $e) {
                        $json = json_decode($e->getResponse()->getBody()->getContents());
                        $error = $json->title.'.'.$json->detail;
                        return $this->sendError($error, [], 500);
                    }
                    
                    $message = 'Vous avez confirmé le transfert de '.$transfert->transfertOut->userClient->name.' '.$transfert->transfertOut->userClient->lastname.' d\'un montant de '.$transfert->transfertOut->montant.' XOF.';
                    
                    if($transfert->userClient->sms == 1){
                        $this->sendSms($transfert->userClient->username,$message);
                    }else{
                        $arr = ['messages'=> $message,'objet'=>'Confirmation du transfert','from'=>'noreply-bcv@bestcash.me'];
                        Mail::to([$transfert->userClient->kycClient->email,])->send(new MailAlerte($arr));
                    }

                

                $message = $transfert->userClient->name.' '.$transfert->userClient->lastname.' a confirmé votre transfert de '.$transfert->transfertOut->montant.' XOF.';
                if($transfert->userClient->sms == 1){
                    $this->sendSms($transfert->transfertOut->userClient->username,$message);
                }else{
                    $arr = ['messages'=> $message,'objet'=>'Confirmation du transfert','from'=>'noreply-bcv@bestcash.me'];
                    Mail::to([$transfert->transfertOut->userClient->kycClient->email,])->send(new MailAlerte($arr));
                }
    
                return $this->sendResponse($transfert, 'Transfert confirmé avec succes.');
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function annulationTransfertAttenteClient(Request $request){
        try {
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }          
            
            $transfert = TransfertOut::where('id',$request->id)->where('deleted',0)->first();
            if(!$transfert){
                return $this->sendError("Cette transaction n'existe pas pour vous", [], 500);
            }else{
                //Retour des fonds
                    
                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
    
                $client = new Client();
                $url = $base_url."accounts/".$transfert->userCard->customer_id."/transactions";
                
                $body = [
                    "transferType" => "WalletToCard",
                    "transferAmount" => $transfert->montant+$transfert->frais_bcb,
                    "currencyCode" => "XOF",
                    "last4Digits" => $transfert->userCard->last_digits,
                    "referenceMemo" => 'Annulation de transfert'
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
    
                    $resultat = json_decode($response->getBody()); 
                    
                    $transfert->deleted = 1;
                    $transfert->save();

                    foreach ($transfert->transferIns as $key => $value) {
                        $value->deleted = 1;
                        $value->save();
                    }
                

                } catch (BadResponseException $e) {
                    $json = json_decode($e->getResponse()->getBody()->getContents());
                    $error = $json->title.'.'.$json->detail;
                    return $this->sendError($error, [], 500);
                }
                
                $message = $transfert->userClient->name.' '.$transfert->userClient->lastname.'a annulé son transfert d\'un montant de '.$transfert->montant.' XOF.';
                if($transfert->receveur->sms == 1){
                    $this->sendSms($transfert->receveur->username,$message);
                }else{
                    $arr = ['messages'=> $message,'objet'=>'Annulation de transfert','from'=>'noreply-bcv@bestcash.me'];
                    Mail::to([$transfert->receveur->kycClient->email,])->send(new MailAlerte($arr));
                }

                $message = 'Vous avez annulé votre transfert de '.$transfert->montant.' XOF à '.$transfert->receveur->name.' '.$transfert->receveur->lastname;
                if($transfert->userClient->sms == 1){
                    $this->sendSms($transfert->userClient->username,$message);
                }else{
                    $arr = ['messages'=> $message,'objet'=>'Annulation de transfert','from'=>'noreply-bcv@bestcash.me'];
                    Mail::to([$transfert->userClient->kycClient->email,])->send(new MailAlerte($arr));
                }
    
                return $this->sendResponse($transfert, 'Transfert annulé avec succes.');
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }    

    public function listeDepotClient(Request $request){
        try {
            $depots = Depot::where('user_client_id',$request->id)->where('deleted',0)->where('status',1)->orderBy('created_at','desc')->get();
            $depotAttentes = Depot::where('user_client_id',$request->id)->where('deleted',0)->where('status',0)->orderBy('created_at','desc')->get();
            
            $data['depots'] = $depots;
            $data['depotAttentes'] = $depotAttentes;
            return $this->sendResponse($data, 'Success.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function listeRetraitClient(Request $request){
        try {
            $retraits = Retrait::where('user_client_id',$request->id)->where('deleted',0)->where('status',1)->orderBy('created_at','desc')->get();
            $retraitAttentes = Retrait::where('user_client_id',$request->id)->where('deleted',0)->where('status',0)->orderBy('created_at','desc')->get();

            $data['retraits'] = $retraits;
            $data['retraitAttentes'] = $retraitAttentes;
            return $this->sendResponse($data, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
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
                return  $this->sendError($validator->errors()->first(), [],422);
            }   

            $requestId = GtpRequest::create([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

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
                'requestId' => $requestId->id,
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
                return $this->sendError($error, [], 500);
            }
            if($request->status == "Active"){
                $message = "Déverouillage effectué avec succes";
            }else{
                $message = "Verouillage effectué avec succes";
            }

            return $this->sendResponse([], $message);            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function statistiquesInfos(Request $request){
        try {
            if(strlen($request->mois) > 1){
                $mois = $request->mois;
            }else{
                $mois = '0'.$request->mois;
            }
            $debut = date('Y-'.$mois.'-01');
            $debut = $debut.' 00:00:00';
            $fin = date('Y-'.$mois.'-t');
            $fin = $fin.' 23:59:59';
            $arr = [];

            $depots = Depot::where('deleted',0)->where('status',1)->where('user_client_id',$request->user_id)->whereBetween('created_at',[$debut,$fin])->orderBy('created_at','desc')->get();
            $retraits = Retrait::where('deleted',0)->where('status',1)->where('user_client_id',$request->user_id)->whereBetween('created_at',[$debut,$fin])->orderBy('created_at','desc')->get();
            $transferts = DB::select(DB::raw("SELECT *
            FROM
            (
                select * , 'recu' as 'sens'
                From transferts
                Where receveur_id = $request->user_id
                and created_at between '$debut' and '$fin'
                and status = 1
            Union
                select * , 'envoie' as 'sens'
                From transferts
                Where user_client_id = $request->user_id
                and created_at between '$debut' and '$fin'
                and status = 1
            ) 
            transferts order by created_at desc"));

            $totalDepots = Depot::where('deleted',0)->where('status',1)->where('user_client_id',$request->user_id)->whereBetween('created_at',[$debut,$fin])->sum('montant');
            $totalTransfertVersMoi = DB::select(DB::raw("SELECT SUM(montant) as tot 
            FROM transferts
            Where receveur_id = $request->user_id
            and created_at between '$debut' and '$fin'
            and status = 1"));  
            $totalDepots += (int)$totalTransfertVersMoi[0]->tot;


            $totalRetraits = Retrait::where('deleted',0)->where('status',1)->where('user_client_id',$request->user_id)->whereBetween('created_at',[$debut,$fin])->sum('montant');
            $totalTransfertDeMoi = DB::select(DB::raw("SELECT SUM(montant) as tot 
            FROM transferts
            Where user_client_id = $request->user_id
            and created_at between '$debut' and '$fin'
            and status = 1"));  
            $totalRetraits += (int)$totalTransfertDeMoi[0]->tot;
            

            $transactions = DB::select(DB::raw("SELECT libelle , montant , typeOperation , dateOperation , partenaire , solde_avant , solde_apres , solde_avant_receveur , solde_apres_receveur , user_client_id , receveur_id
            FROM
            (
                select libelle , montant , 'depot' as typeOperation , created_at as dateOperation , partenaire_id as partenaire , solde_avant , solde_apres , 'solde_avant_receveur' , 'solde_apres_receveur' , user_client_id , 'receveur_id'
                From depots
                Where user_client_id = $request->user_id
                and created_at between '$debut' and '$fin'
                and status = 1
            Union
                select libelle , montant , 'retrait' as typeOperation , created_at as dateOperation , partenaire_id as partenaire , solde_avant , solde_apres , 'solde_avant_receveur' , 'solde_apres_receveur' , user_client_id , 'receveur_id'
                From retraits
                Where user_client_id = $request->user_id
                and created_at between '$debut' and '$fin'
                and status = 1
            Union
                select libelle , montant , 'transfert' as typeOperation , created_at as dateOperation , receveur_id as partenaire , solde_avant_envoyeur as 'solde_avant' , solde_apres_envoyeur as 'solde_apres' , solde_avant_receveur , solde_apres_receveur , user_client_id , receveur_id
                From transferts
                Where (user_client_id = $request->user_id
                or receveur_id = $request->user_id)
                and created_at between '$debut' and '$fin'
                and status = 1
            ) 
            transactions order by dateOperation desc"));
            //dd($transactions);
            $data = [];
            foreach ($transactions as $key => $value) {
                $value->id = $key + 1;
                $data[] = $value;
            }

            
            if($transactions){
                $soldeInital = $transactions[array_key_last($transactions)]->solde_avant;
                if($transactions[array_key_last($transactions)]->typeOperation == "transfert"){
                    if($transactions[array_key_last($transactions)]->user_client_id == $request->user_id){
                        $soldeInital = $transactions[array_key_last($transactions)]->solde_avant;
                    }
                    if($transactions[array_key_last($transactions)]->receveur_id == $request->user_id){
                        $soldeInital = $transactions[array_key_last($transactions)]->solde_avant_receveur;
                    }
                }
    
                
                $soldeFinal = $transactions[0]->solde_apres;
                if($transactions[0]->typeOperation == "transfert"){
                    if($transactions[0]->user_client_id == $request->user_id){
                        $soldeFinal = $transactions[0]->solde_apres;
                    }
                    if($transactions[0]->receveur_id == $request->user_id){
                        $soldeFinal = $transactions[0]->solde_apres_receveur;
                    }
                }
            }else{
                $soldeInital = 0;
                $soldeFinal = 0;
            }


            $arr['depots'] = $depots;
            $arr['retraits'] = $retraits;
            $arr['transferts'] = $transferts;
            $arr['totalDepots'] = $totalDepots;
            $arr['totalRetraits'] = $totalRetraits;
            $arr['soldeInitial'] = $soldeInital;
            $arr['soldeFinal'] = $soldeFinal;

            return $this->sendResponse($arr, 'Success');
            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
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
                return  $this->sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['user'])->where('deleted',0)->first();
            $user->name = $req['name'];
            $user->lastname = $req['lastname'];
            $user->sms = $req['sms'];
            $user->double_authentification = $req['double_authentification'];
            $user->updated_at = carbon::now();
            $user->save();
            return $this->sendResponse($retraits, 'Success.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function changeNameUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['id'])->where('deleted',0)->first();
            $user->name = $req['name'];
            $user->updated_at = carbon::now();
            $user->save();
            return $this->sendResponse($user, 'Success.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function changeLastnameUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'lastname' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['id'])->where('deleted',0)->first();
            $user->lastname = $req['lastname'];
            $user->updated_at = carbon::now();
            $user->save();
            return $this->sendResponse($user, 'Success.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function changeEmailUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['id'])->where('deleted',0)->first();
            $user->email = $req['email'];
            $user->updated_at = carbon::now();
            $user->save();
            return $this->sendResponse($user, 'Success.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function changeTelephoneUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'telephone' => 'required',
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['id'])->where('deleted',0)->first();
            $user->username = $req['telephone'];
            $user->updated_at = carbon::now();
            $user->save();
            return $this->sendResponse($retraits, 'Success.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function changePasswordUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['id'])->where('deleted',0)->first();
            $user->password = Hash::make($req['password']);
            $user->updated_at = carbon::now();
            $user->save();
            return $this->sendResponse($user, 'Mot de passe changé avec succès.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function changePinUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'pin' => 'required',
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['id'])->where('deleted',0)->first();
            $user->pin = $req['pin'];
            $user->updated_at = carbon::now();
            $user->save();
            return $this->sendResponse($user, 'Code PIN changé avec succès.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function changeSmsUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'sms' => 'required'
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['id'])->where('deleted',0)->first();
            $user->sms = $req['sms'];
            $user->updated_at = carbon::now();
            $user->save();
            if($req['sms'] == 1){
                $state = 'activée';
            }else{
                $state = 'désactivée';
            }
            return $this->sendResponse($user, 'Notifications par Sms '.$state.'.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function changeDoubleUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'double_authentification' => 'required',
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['id'])->where('deleted',0)->first();
            $user->double_authentification = $req['double_authentification'];
            $user->updated_at = carbon::now();
            $user->save();
            return $this->sendResponse($user, 'Success.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function changeAdresseUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'adresse' => 'required',
            ]);
            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }
            $req = $request->all();
            $user = UserClient::where('id',$req['id'])->where('deleted',0)->first();
            $user->adresse = $req['adresse'];
            $user->updated_at = carbon::now();
            $user->save();
            return $this->sendResponse($user, 'Success.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function listeDepartement(Request $request){
        try {
            $departements = Departement::where('deleted',0)->get();
            return $this->sendResponse($departements, 'Success');
            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function listePays(Request $request){
        try {
            $countries = countriesListes();
            return $this->sendResponse($countries, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function listeGamme(Request $request){
        try {
            $gammes = Gamme::where('deleted',0)->get();
            return $this->sendResponse($gammes, 'Success');
            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function getVirtuellePrice(Request $request){
        try {
            $price = ['price' => 50];
            return $this->sendResponse($price, 'Success');
            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function searchClientUpdate(Request $request){
        try {
            $user = UserClient::where('id',$request->id)->where('deleted',0)->first();
            $kyc = $user->kycClient;

            $data = [];
            $data['user'] = $user;
            $data['kyc'] = $kyc;
            return $this->sendResponse($data, 'Success.');
            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function buyCarte(Request $request){
        try{
            $encrypt_Key = env('ENCRYPT_KEY');
            
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'transaction_id' => 'required'
            ]);

            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }

            $user = UserClient::where('id',$request->user_id)->first();


            if($user->verification == 0){
                return response()->json([
                    'message' => 'Ce compte n\'est pas encore validé',
                ], 401);
            }


            $reference = '';
            
            if ($request->moyen != 'directe'){
                $reference = $request->reference;
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
            
            $requestId = GtpRequest::create([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
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
            
            //return $requestId;
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


            //if($request->type == 'virtuelle'){    
            /*}else{
   
                $programID = env('PROGRAM_ID_PHYSIQUE');
                $authLogin = env('AUTH_LOGIN_PHYSIQUE');
                $authPass = env('AUTH_PASS_PHYSIQUE');

                $carteVendu = CartePhysique::where('status',1)->where('deleted',0)->where('gamme_id',$request->gamme)->first();
                if(!$carteVendu){
                    return  $this->sendError('Cette gamme de carte est indisponible', [],404);
                }
                $carteVendu->status = 0;
    
                $client = new Client();
                $url = $base_url."accounts/instant";
                
                $name = $user->kycClient->name.' '.$user->kycClient->lastname;
                if (strlen($name) > 19){
                    $name = substr($name, 0, 19);
                }
                
                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                
                $body = [
                    "accountId"=> $carteVendu->code,
                    "firstName" => $user->kycClient->name,
                    "lastName" => $user->kycClient->lastname,
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
    
            }*/

            $user->save();

            //$oldCard = UserCard::where('deleted',0)->where('user_client_id',$user->id)->get();
            $firstly = 0;
            /*if(count($oldCard) == 0){
                $firstly = 1;
            }*/

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
            Mail::to([$user->kycClient->email,])->send(new MailVenteVirtuelle(['registrationAccountId' => $responseBody->registrationAccountId,'registrationLast4Digits' => $responseBody->registrationLast4Digits,'registrationPassCode' => $responseBody->registrationPassCode,'type' => $request->type])); 
            return $this->sendResponse($card, 'Achat terminé avec succes');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

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
                return  $this->sendError($validator->errors()->first(), [],422);
            }

            $user = UserClient::where('id',$request->user_id)->first();


            if($user->verification == 0){
                return response()->json([
                    'message' => 'Ce compte n\'est pas encore validé',
                ], 401);
            }

            // Creation de la transaction
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
            // Fin creation de la trnsaction
            
            // Verification paiement
                if(checkPayment($request->type, $request->transaction_id, $request->montant) == 'bad_amount'){
                    $reason = date('Y-m-d h:i:s : Montant incorrecte');
                    $userCardBuy->reasons = $reason;
                    $userCardBuy->status = 'failed';
                    $userCardBuy->save();
                    return $this->sendError('Le montant ne correspond pas au montant de la transaction', [], 500);
                }else if(checkPayment($request->type, $request->transaction_id, $request->montant) == 'not_success'){
                    $reason = date('Y-m-d h:i:s : Echec du paiement');
                    $userCardBuy->reasons = $reason;
                    $userCardBuy->status = 'failed';
                    $userCardBuy->save();
                    return $this->sendError('Le paiement du montant n\'a pas aboutit', [], 500);
                }

                $userCardBuy->is_paid = 1;
                $userCardBuy->save();

                $reference = $request->transaction_id;
            // Fin verification paiement
            
            return $this->sendError('rr', [], 500);

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
            
            $requestId = GtpRequest::create([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
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
            return $this->sendResponse($card, 'Achat terminé avec succes');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function completeBuyCard(Request $request){
        try{
            $encrypt_Key = env('ENCRYPT_KEY');
            
            $validator = Validator::make($request->all(), [
                'transaction_id' => ["required" , "string"],
            ]);

            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
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
                        return $this->sendError('Le montant ne correspond pas au montant de la transaction', [], 500);
                    }else if(checkPayment($userCardBuy->moyen_paiement, $userCardBuy->reference_paiement, $userCardBuy->montant)){
                        $reason = date('Y-m-d h:i:s : Echec du paiement');
                        $userCardBuy->reasons = $reason;
                        $userCardBuy->status = 'failed';
                        $userCardBuy->save();
                        return $this->sendError('Le paiement du montant n\'a pas aboutit', [], 500);
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
            
            $requestId = GtpRequest::create([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
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
            
            return $this->sendResponse($card, 'Achat terminé avec succes');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function setDefaultCard(Request $request){
        try{
            $encrypt_Key = env('ENCRYPT_KEY');
            
            $validator = Validator::make($request->all(), [
                'card_id' => ["required" , "string"]
            ]);

            if ($validator->fails()) {
                return  $this->sendError($validator->errors()->first(), [],422);
            }
            
            $newDefaultCard = UserCard::where('id',$request->card_id)->first();
            $user = UserClient::where('id',$newDefaultCard->user_client_id)->where('deleted',0)->first();

            foreach($user->userCards as $item){
                $item->is_first = 0;
                $item->save();
            }
            $newDefaultCard->is_first = 1;
            $newDefaultCard->save();

            return $this->sendResponse($newDefaultCard, 'Carte defini avec success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
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
                return  $this->sendError($validator->errors()->first(), [],422);
            }

            $encrypt_Key = env('ENCRYPT_KEY');
            
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $data = [];
            try {
                /*$requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);*/

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
                    return $this->sendError('Cette carte est pour le moment bloqué. Veuillez contacter le service clientèle', [], 403);
                }
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return $this->sendError($error, [], 500);
            }

            
            try {
                $client = new Client();
                $url = $base_url."accounts/phone-number";
        
                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
    
                $headers = [
                    'programId' => $programID,
                    'requestId' => $requestId->id
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
                
                $accountInfo = null;
                $accountInfoLists = json_decode($response->getBody())->accountInfoList;
                //return $response->getBody();
                foreach ($accountInfoLists as $value) {
                    if($value->accountId == $request->customer_id){
                        $accountInfo = $value;
                        break;
                    }
                }

                if($request->last_digits != $accountInfo->lastFourDigits){
                    return $this->sendError('Les 4 dernier chiffres ne correspondent pas a l\'ID', [], 403);
                }
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());   
                $error = $json->title.'.'.$json->detail;
                return $this->sendError($error, [], 500);
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
            
            return $this->sendResponse($card, 'Liaison effectuée avec succes');
            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    //fonction interne

    private function getCarteTransaction($code){
        try {
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $data = [];
            try {
                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $client = new Client();
                $url = $base_url."accounts/".$code;
            
                $headers = [
                    'programId' => $programID,
                    'requestId' => $requestId->id
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
                    return $this->sendError('Cette carte est pour le moment bloqué. Veuillez contacter le service clientèle', [], 403);
                }
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return $this->sendError($error, [], 500);
            }
        
            try {
                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
    
                $client = new Client();
                $url = $base_url."accounts/".$code."/balance";
        
                $headers = [
                    'programId' => $programID,
                    'requestId' => $requestId->id
                ];
        
                $auth = [
                    $authLogin,
                    $authPass
                ];

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

            $user = UserClient::where('code',$code)->first();
            $transactions = DB::select(DB::raw("SELECT id_op, libelle , montant , typeOperation , dateOperation , partenaire , sens, emetteur
            FROM
            (
                select depots.id as id_op, libelle , montant , 'depot' as typeOperation , created_at as dateOperation , partenaire_id as partenaire, 'recu' as sens, 'neant' as emetteur
                From depots
                Where user_client_id = "."'$user->id'"."
                and status = 1
            Union
                select retraits.id as id_op, libelle , montant , 'retrait' as typeOperation , created_at as dateOperation , partenaire_id as partenaire, 'envoie' as sens, 'neant' as emetteur
                From retraits
                Where user_client_id = "."'$user->id'"."
                and status = 1
            Union
                SELECT *
                    FROM
                    (
                        select transferts.id as id_op, libelle , montant , 'transfert' as typeOperation , transferts.created_at as dateOperation , user_client_id as partenaire , 'recu' as 'sens', CONCAT(user_clients.name,' ',user_clients.lastname) as emetteur 
                        From transferts, user_clients
                        Where receveur_id = "."'$user->id'"."
                        and user_clients.id = transferts.user_client_id
                        and transferts.status = 1
                    Union
                        select transferts.id as id_op, libelle , montant , 'transfert' as typeOperation , created_at as dateOperation , receveur_id as partenaire , 'envoie' as 'sens', 'neant' as emetteur
                        From transferts
                        Where user_client_id = "."'$user->id'"."
                        and status = 1
                    ) 
                transferts
            ) 
            transactions order by dateOperation desc"));

            $trans = [];
            foreach ($transactions as $key => $value) {
                $value->id = $key + 1;
                $trans[] = $value;
            }

            try {
                $client = new Client();
                $url = $base_url."accounts/phone-number";
        
                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
    
                $headers = [
                    'programId' => $programID,
                    'requestId' => $requestId->id
                ];
        
                $query = [
                    'phoneNumber' => $clientInfo->mobilePhoneNumber
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
                
                $accountInfo = null;
                $accountInfoLists = json_decode($response->getBody())->accountInfoList;
                foreach ($accountInfoLists as $value) {
                    if($value->accountId == $code){
                        $accountInfo = $value;
                        break;
                    }
                }
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());   
                $error = $json->title.'.'.$json->detail;
                return $this->sendError($error, [], 500);
            }
            

            $data['clientInfo'] = $clientInfo;
            $data['balance'] = $balance;
            $data['transactions'] = $trans;
            $data['accountInfo'] = $accountInfo;
            return $data;
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    //fonction interne

    public function carteTransaction(Request $request){
        try {
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $data = [];
            $code = $request->code;

            try { 
                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                
                $debut = $request->debut;
                $fin = $request->fin;
    
                $client = new Client();
                $url = $base_url."accounts/".$code."/transactions";
        
                $query = [
                    'startDate' => $debut,
                    'endDate' => $fin
                ];
        
                $headers = [
                    'programId' => $programID,
                    'requestId' => $requestId->id
                ];
        
                $auth = [
                    $authLogin,
                    $authPass
                ];
            
                $response = $client->request('GET', $url, [
                    'auth' => $auth,
                    'headers' => $headers,
                    'query' => $query,
                ]);
        
                $cardTransactions = json_decode($response->getBody());
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return $this->sendError($error, [], 500);
            }

            return $this->sendResponse($cardTransactions, 'Liste reçu avec succes.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    private function sendResponse($data, $message){
    	$response = [
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }
    
    private function sendError($message, $data = [], $code = 404){
    	$response = [
            'success' => false,
            'errors' => $message,
        ];


        if(!empty($data)){
            $response['data'] = $data;
        }
        return response()->json($response, $code);
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

        $endpoint = "https://api.wirepick.com/httpsms/send?client=ubabmo&password=udQ31DEzAXoC8Dyyhbut&phone=".$receiver."&text=".$message."&from=BCV";
        
        //$endpoint = "https://smsapi.moov.bj:8443/sendmsg/api?username=ELG&password=elg@&apikey=7073295b7bbc55100c479e3d941518ec&src=UBA+Bmo&dst=".$receiver."&text=".$message."&refnumber=6334353dc8fb7&type=web";  
            
        $client = new \GuzzleHttp\Client([                                                                                                                                                                   
            'verify' => false                                                                                                                                                                               
        ]);                                                                                                                                                                                               
                                                                                                                                                                                                            
        $response = $client->request('GET', $endpoint);                                                                                                                                   
                                                                                                                                                                                                            
        $statusCode = $response->getStatusCode();  
    }
    
    protected function createNewToken($token){
        return $token;
    }
    
    public $countries = array(
        'AD'=>array('code'=> 'AD', 'name'=>'ANDORRA','code'=>'376'),
        'AE'=>array('code'=> 'AE', 'name'=>'UNITED ARAB EMIRATES','code'=>'971'),
        'AF'=>array('code'=> 'AF', 'name'=>'AFGHANISTAN','code'=>'93'),
        'AG'=>array('code'=> 'AG', 'name'=>'ANTIGUA AND BARBUDA','code'=>'1268'),
        'AI'=>array('code'=> 'AI', 'name'=>'ANGUILLA','code'=>'1264'),
        'AL'=>array('code'=> 'AL', 'name'=>'ALBANIA','code'=>'355'),
        'AM'=>array('code'=> 'AM', 'name'=>'ARMENIA','code'=>'374'),
        'AN'=>array('code'=> 'AN', 'name'=>'NETHERLANDS ANTILLES','code'=>'599'),
        'AO'=>array('code'=> 'AO', 'name'=>'ANGOLA','code'=>'244'),
        'AQ'=>array('code'=> 'AQ', 'name'=>'ANTARCTICA','code'=>'672'),
        'AR'=>array('code'=> 'AR', 'name'=>'ARGENTINA','code'=>'54'),
        'AS'=>array('code'=> 'AS', 'name'=>'AMERICAN SAMOA','code'=>'1684'),
        'AT'=>array('code'=> 'AT', 'name'=>'AUSTRIA','code'=>'43'),
        'AU'=>array('code'=> 'AU', 'name'=>'AUSTRALIA','code'=>'61'),
        'AW'=>array('code'=> 'AW', 'name'=>'ARUBA','code'=>'297'),
        'AZ'=>array('code'=> 'AZ', 'name'=>'AZERBAIJAN','code'=>'994'),
        'BA'=>array('code'=> 'BA', 'name'=>'BOSNIA AND HERZEGOVINA','code'=>'387'),
        'BB'=>array('code'=> 'BB', 'name'=>'BARBADOS','code'=>'1246'),
        'BD'=>array('code'=> 'BD', 'name'=>'BANGLADESH','code'=>'880'),
        'BE'=>array('code'=> 'BE', 'name'=>'BELGIUM','code'=>'32'),
        'BF'=>array('code'=> 'BF', 'name'=>'BURKINA FASO','code'=>'226'),
        'BG'=>array('code'=> 'BG', 'name'=>'BULGARIA','code'=>'359'),
        'BH'=>array('code'=> 'BH', 'name'=>'BAHRAIN','code'=>'973'),
        'BI'=>array('code'=> 'BI', 'name'=>'BURUNDI','code'=>'257'),
        'BJ'=>array('code'=> 'BJ', 'name'=>'BENIN','code'=>'229'),
        'BL'=>array('code'=> 'BL', 'name'=>'SAINT BARTHELEMY','code'=>'590'),
        'BM'=>array('code'=> 'BM', 'name'=>'BERMUDA','code'=>'1441'),
        'BN'=>array('code'=> 'BN', 'name'=>'BRUNEI DARUSSALAM','code'=>'673'),
        'BO'=>array('code'=> 'BO', 'name'=>'BOLIVIA','code'=>'591'),
        'BR'=>array('code'=> 'BR', 'name'=>'BRAZIL','code'=>'55'),
        'BS'=>array('code'=> 'BS', 'name'=>'BAHAMAS','code'=>'1242'),
        'BT'=>array('code'=> 'BT', 'name'=>'BHUTAN','code'=>'975'),
        'BW'=>array('code'=> 'BW', 'name'=>'BOTSWANA','code'=>'267'),
        'BY'=>array('code'=> 'BY', 'name'=>'BELARUS','code'=>'375'),
        'BZ'=>array('code'=> 'BZ', 'name'=>'BELIZE','code'=>'501'),
        'CA'=>array('code'=> 'CA', 'name'=>'CANADA','code'=>'1'),
        'CC'=>array('code'=> 'CC', 'name'=>'COCOS (KEELING) ISLANDS','code'=>'61'),
        'CD'=>array('code'=> 'CD', 'name'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE','code'=>'243'),
        'CF'=>array('code'=> 'CF', 'name'=>'CENTRAL AFRICAN REPUBLIC','code'=>'236'),
        'CG'=>array('code'=> 'CG', 'name'=>'CONGO','code'=>'242'),
        'CH'=>array('code'=> 'CH', 'name'=>'SWITZERLAND','code'=>'41'),
        'CI'=>array('code'=> 'CI', 'name'=>'COTE D IVOIRE','code'=>'225'),
        'CK'=>array('code'=> 'CK', 'name'=>'COOK ISLANDS','code'=>'682'),
        'CL'=>array('code'=> 'CL', 'name'=>'CHILE','code'=>'56'),
        'CM'=>array('code'=> 'CM', 'name'=>'CAMEROON','code'=>'237'),
        'CN'=>array('code'=> 'CN', 'name'=>'CHINA','code'=>'86'),
        'CO'=>array('code'=> 'CO', 'name'=>'COLOMBIA','code'=>'57'),
        'CR'=>array('code'=> 'CR', 'name'=>'COSTA RICA','code'=>'506'),
        'CU'=>array('code'=> 'CU', 'name'=>'CUBA','code'=>'53'),
        'CV'=>array('code'=> 'CV', 'name'=>'CAPE VERDE','code'=>'238'),
        'CX'=>array('code'=> 'CX', 'name'=>'CHRISTMAS ISLAND','code'=>'61'),
        'CY'=>array('code'=> 'CY', 'name'=>'CYPRUS','code'=>'357'),
        'CZ'=>array('code'=> 'CZ', 'name'=>'CZECH REPUBLIC','code'=>'420'),
        'DE'=>array('code'=> 'DE', 'name'=>'GERMANY','code'=>'49'),
        'DJ'=>array('code'=> 'DJ', 'name'=>'DJIBOUTI','code'=>'253'),
        'DK'=>array('code'=> 'DK', 'name'=>'DENMARK','code'=>'45'),
        'DM'=>array('code'=> 'DM', 'name'=>'DOMINICA','code'=>'1767'),
        'DO'=>array('code'=> 'DO', 'name'=>'DOMINICAN REPUBLIC','code'=>'1809'),
        'DZ'=>array('code'=> 'DZ', 'name'=>'ALGERIA','code'=>'213'),
        'EC'=>array('code'=> 'EC', 'name'=>'ECUADOR','code'=>'593'),
        'EE'=>array('code'=> 'EE', 'name'=>'ESTONIA','code'=>'372'),
        'EG'=>array('code'=> 'EG', 'name'=>'EGYPT','code'=>'20'),
        'ER'=>array('code'=> 'ER', 'name'=>'ERITREA','code'=>'291'),
        'ES'=>array('code'=> 'ES', 'name'=>'SPAIN','code'=>'34'),
        'ET'=>array('code'=> 'ET', 'name'=>'ETHIOPIA','code'=>'251'),
        'FI'=>array('code'=> 'FI', 'name'=>'FINLAND','code'=>'358'),
        'FJ'=>array('code'=> 'FJ', 'name'=>'FIJI','code'=>'679'),
        'FK'=>array('code'=> 'FK', 'name'=>'FALKLAND ISLANDS (MALVINAS)','code'=>'500'),
        'FM'=>array('code'=> 'FM', 'name'=>'MICRONESIA, FEDERATED STATES OF','code'=>'691'),
        'FO'=>array('code'=> 'FO', 'name'=>'FAROE ISLANDS','code'=>'298'),
        'FR'=>array('code'=> 'FR', 'name'=>'FRANCE','code'=>'33'),
        'GA'=>array('code'=> 'GA', 'name'=>'GABON','code'=>'241'),
        'GB'=>array('code'=> 'GB', 'name'=>'UNITED KINGDOM','code'=>'44'),
        'GD'=>array('code'=> 'GD', 'name'=>'GRENADA','code'=>'1473'),
        'GE'=>array('code'=> 'GE', 'name'=>'GEORGIA','code'=>'995'),
        'GH'=>array('code'=> 'GH', 'name'=>'GHANA','code'=>'233'),
        'GI'=>array('code'=> 'GI', 'name'=>'GIBRALTAR','code'=>'350'),
        'GL'=>array('code'=> 'GL', 'name'=>'GREENLAND','code'=>'299'),
        'GM'=>array('code'=> 'GM', 'name'=>'GAMBIA','code'=>'220'),
        'GN'=>array('code'=> 'GN', 'name'=>'GUINEA','code'=>'224'),
        'GQ'=>array('code'=> 'GQ', 'name'=>'EQUATORIAL GUINEA','code'=>'240'),
        'GR'=>array('code'=> 'GR', 'name'=>'GREECE','code'=>'30'),
        'GT'=>array('code'=> 'GT', 'name'=>'GUATEMALA','code'=>'502'),
        'GU'=>array('code'=> 'GU', 'name'=>'GUAM','code'=>'1671'),
        'GW'=>array('code'=> 'GW', 'name'=>'GUINEA-BISSAU','code'=>'245'),
        'GY'=>array('code'=> 'GY', 'name'=>'GUYANA','code'=>'592'),
        'HK'=>array('code'=> 'HK', 'name'=>'HONG KONG','code'=>'852'),
        'HN'=>array('code'=> 'HN', 'name'=>'HONDURAS','code'=>'504'),
        'HR'=>array('code'=> 'HR', 'name'=>'CROATIA','code'=>'385'),
        'HT'=>array('code'=> 'HT', 'name'=>'HAITI','code'=>'509'),
        'HU'=>array('code'=> 'HU', 'name'=>'HUNGARY','code'=>'36'),
        'ID'=>array('code'=> 'ID', 'name'=>'INDONESIA','code'=>'62'),
        'IE'=>array('code'=> 'IE', 'name'=>'IRELAND','code'=>'353'),
        'IL'=>array('code'=> 'IL', 'name'=>'ISRAEL','code'=>'972'),
        'IM'=>array('code'=> 'IM', 'name'=>'ISLE OF MAN','code'=>'44'),
        'IN'=>array('code'=> 'IN', 'name'=>'INDIA','code'=>'91'),
        'IQ'=>array('code'=> 'IQ', 'name'=>'IRAQ','code'=>'964'),
        'IR'=>array('code'=> 'IR', 'name'=>'IRAN, ISLAMIC REPUBLIC OF','code'=>'98'),
        'IS'=>array('code'=> 'IS', 'name'=>'ICELAND','code'=>'354'),
        'IT'=>array('code'=> 'IT', 'name'=>'ITALY','code'=>'39'),
        'JM'=>array('code'=> 'JM', 'name'=>'JAMAICA','code'=>'1876'),
        'JO'=>array('code'=> 'JO', 'name'=>'JORDAN','code'=>'962'),
        'JP'=>array('code'=> 'JP', 'name'=>'JAPAN','code'=>'81'),
        'KE'=>array('code'=> 'KE', 'name'=>'KENYA','code'=>'254'),
        'KG'=>array('code'=> 'KG', 'name'=>'KYRGYZSTAN','code'=>'996'),
        'KH'=>array('code'=> 'KH', 'name'=>'CAMBODIA','code'=>'855'),
        'KI'=>array('code'=> 'KI', 'name'=>'KIRIBATI','code'=>'686'),
        'KM'=>array('code'=> 'KM', 'name'=>'COMOROS','code'=>'269'),
        'KN'=>array('code'=> 'KN', 'name'=>'SAINT KITTS AND NEVIS','code'=>'1869'),
        'KP'=>array('code'=> 'KP', 'name'=>'KOREA DEMOCRATIC PEOPLES REPUBLIC OF','code'=>'850'),
        'KR'=>array('code'=> 'KR', 'name'=>'KOREA REPUBLIC OF','code'=>'82'),
        'KW'=>array('code'=> 'KW', 'name'=>'KUWAIT','code'=>'965'),
        'KY'=>array('code'=> 'KY', 'name'=>'CAYMAN ISLANDS','code'=>'1345'),
        'KZ'=>array('code'=> 'KZ', 'name'=>'KAZAKSTAN','code'=>'7'),
        'LA'=>array('code'=> 'LA', 'name'=>'LAO PEOPLES DEMOCRATIC REPUBLIC','code'=>'856'),
        'LB'=>array('code'=> 'LB', 'name'=>'LEBANON','code'=>'961'),
        'LC'=>array('code'=> 'LC', 'name'=>'SAINT LUCIA','code'=>'1758'),
        'LI'=>array('code'=> 'LI', 'name'=>'LIECHTENSTEIN','code'=>'423'),
        'LK'=>array('code'=> 'LK', 'name'=>'SRI LANKA','code'=>'94'),
        'LR'=>array('code'=> 'LR', 'name'=>'LIBERIA','code'=>'231'),
        'LS'=>array('code'=> 'LS', 'name'=>'LESOTHO','code'=>'266'),
        'LT'=>array('code'=> 'LT', 'name'=>'LITHUANIA','code'=>'370'),
        'LU'=>array('code'=> 'LU', 'name'=>'LUXEMBOURG','code'=>'352'),
        'LV'=>array('code'=> 'LV', 'name'=>'LATVIA','code'=>'371'),
        'LY'=>array('code'=> 'LY', 'name'=>'LIBYAN ARAB JAMAHIRIYA','code'=>'218'),
        'MA'=>array('code'=> 'MA', 'name'=>'MOROCCO','code'=>'212'),
        'MC'=>array('code'=> 'MC', 'name'=>'MONACO','code'=>'377'),
        'MD'=>array('code'=> 'MD', 'name'=>'MOLDOVA, REPUBLIC OF','code'=>'373'),
        'ME'=>array('code'=> 'ME', 'name'=>'MONTENEGRO','code'=>'382'),
        'MF'=>array('code'=> 'MF', 'name'=>'SAINT MARTIN','code'=>'1599'),
        'MG'=>array('code'=> 'MG', 'name'=>'MADAGASCAR','code'=>'261'),
        'MH'=>array('code'=> 'MH', 'name'=>'MARSHALL ISLANDS','code'=>'692'),
        'MK'=>array('code'=> 'MK', 'name'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF','code'=>'389'),
        'ML'=>array('code'=> 'ML', 'name'=>'MALI','code'=>'223'),
        'MM'=>array('code'=> 'MM', 'name'=>'MYANMAR','code'=>'95'),
        'MN'=>array('code'=> 'MN', 'name'=>'MONGOLIA','code'=>'976'),
        'MO'=>array('code'=> 'MO', 'name'=>'MACAU','code'=>'853'),
        'MP'=>array('code'=> 'MP', 'name'=>'NORTHERN MARIANA ISLANDS','code'=>'1670'),
        'MR'=>array('code'=> 'MR', 'name'=>'MAURITANIA','code'=>'222'),
        'MS'=>array('code'=> 'MS', 'name'=>'MONTSERRAT','code'=>'1664'),
        'MT'=>array('code'=> 'MT', 'name'=>'MALTA','code'=>'356'),
        'MU'=>array('code'=> 'MU', 'name'=>'MAURITIUS','code'=>'230'),
        'MV'=>array('code'=> 'MV', 'name'=>'MALDIVES','code'=>'960'),
        'MW'=>array('code'=> 'MW', 'name'=>'MALAWI','code'=>'265'),
        'MX'=>array('code'=> 'MX', 'name'=>'MEXICO','code'=>'52'),
        'MY'=>array('code'=> 'MY', 'name'=>'MALAYSIA','code'=>'60'),
        'MZ'=>array('code'=> 'MZ', 'name'=>'MOZAMBIQUE','code'=>'258'),
        'NA'=>array('code'=> 'NA', 'name'=>'NAMIBIA','code'=>'264'),
        'NC'=>array('code'=> 'NC', 'name'=>'NEW CALEDONIA','code'=>'687'),
        'NE'=>array('code'=> 'NE', 'name'=>'NIGER','code'=>'227'),
        'NG'=>array('code'=> 'NG', 'name'=>'NIGERIA','code'=>'234'),
        'NI'=>array('code'=> 'NI', 'name'=>'NICARAGUA','code'=>'505'),
        'NL'=>array('code'=> 'NL', 'name'=>'NETHERLANDS','code'=>'31'),
        'NO'=>array('code'=> 'NO', 'name'=>'NORWAY','code'=>'47'),
        'NP'=>array('code'=> 'NP', 'name'=>'NEPAL','code'=>'977'),
        'NR'=>array('code'=> 'NR', 'name'=>'NAURU','code'=>'674'),
        'NU'=>array('code'=> 'NU', 'name'=>'NIUE','code'=>'683'),
        'NZ'=>array('code'=> 'NZ', 'name'=>'NEW ZEALAND','code'=>'64'),
        'OM'=>array('code'=> 'OM', 'name'=>'OMAN','code'=>'968'),
        'PA'=>array('code'=> 'PA', 'name'=>'PANAMA','code'=>'507'),
        'PE'=>array('code'=> 'PE', 'name'=>'PERU','code'=>'51'),
        'PF'=>array('code'=> 'PF', 'name'=>'FRENCH POLYNESIA','code'=>'689'),
        'PG'=>array('code'=> 'PG', 'name'=>'PAPUA NEW GUINEA','code'=>'675'),
        'PH'=>array('code'=> 'PH', 'name'=>'PHILIPPINES','code'=>'63'),
        'PK'=>array('code'=> 'PK', 'name'=>'PAKISTAN','code'=>'92'),
        'PL'=>array('code'=> 'PL', 'name'=>'POLAND','code'=>'48'),
        'PM'=>array('code'=> 'PM', 'name'=>'SAINT PIERRE AND MIQUELON','code'=>'508'),
        'PN'=>array('code'=> 'PN', 'name'=>'PITCAIRN','code'=>'870'),
        'PR'=>array('code'=> 'PR', 'name'=>'PUERTO RICO','code'=>'1'),
        'PT'=>array('code'=> 'PT', 'name'=>'PORTUGAL','code'=>'351'),
        'PW'=>array('code'=> 'PW', 'name'=>'PALAU','code'=>'680'),
        'PY'=>array('code'=> 'PY', 'name'=>'PARAGUAY','code'=>'595'),
        'QA'=>array('code'=> 'QA', 'name'=>'QATAR','code'=>'974'),
        'RO'=>array('code'=> 'RO', 'name'=>'ROMANIA','code'=>'40'),
        'RS'=>array('code'=> 'RS', 'name'=>'SERBIA','code'=>'381'),
        'RU'=>array('code'=> 'RU', 'name'=>'RUSSIAN FEDERATION','code'=>'7'),
        'RW'=>array('code'=> 'RW', 'name'=>'RWANDA','code'=>'250'),
        'SA'=>array('code'=> 'SA', 'name'=>'SAUDI ARABIA','code'=>'966'),
        'SB'=>array('code'=> 'SB', 'name'=>'SOLOMON ISLANDS','code'=>'677'),
        'SC'=>array('code'=> 'SC', 'name'=>'SEYCHELLES','code'=>'248'),
        'SD'=>array('code'=> 'SD', 'name'=>'SUDAN','code'=>'249'),
        'SE'=>array('code'=> 'SE', 'name'=>'SWEDEN','code'=>'46'),
        'SG'=>array('code'=> 'SG', 'name'=>'SINGAPORE','code'=>'65'),
        'SH'=>array('code'=> 'SH', 'name'=>'SAINT HELENA','code'=>'290'),
        'SI'=>array('code'=> 'SI', 'name'=>'SLOVENIA','code'=>'386'),
        'SK'=>array('code'=> 'SK', 'name'=>'SLOVAKIA','code'=>'421'),
        'SL'=>array('code'=> 'SL', 'name'=>'SIERRA LEONE','code'=>'232'),
        'SM'=>array('code'=> 'SM', 'name'=>'SAN MARINO','code'=>'378'),
        'SN'=>array('code'=> 'SN', 'name'=>'SENEGAL','code'=>'221'),
        'SO'=>array('code'=> 'SO', 'name'=>'SOMALIA','code'=>'252'),
        'SR'=>array('code'=> 'SR', 'name'=>'SURINAME','code'=>'597'),
        'ST'=>array('code'=> 'ST', 'name'=>'SAO TOME AND PRINCIPE','code'=>'239'),
        'SV'=>array('code'=> 'SV', 'name'=>'EL SALVADOR','code'=>'503'),
        'SY'=>array('code'=> 'SY', 'name'=>'SYRIAN ARAB REPUBLIC','code'=>'963'),
        'SZ'=>array('code'=> 'SZ', 'name'=>'SWAZILAND','code'=>'268'),
        'TC'=>array('code'=> 'TC', 'name'=>'TURKS AND CAICOS ISLANDS','code'=>'1649'),
        'TD'=>array('code'=> 'TD', 'name'=>'CHAD','code'=>'235'),
        'TG'=>array('code'=> 'TG', 'name'=>'TOGO','code'=>'228'),
        'TH'=>array('code'=> 'TH', 'name'=>'THAILAND','code'=>'66'),
        'TJ'=>array('code'=> 'TJ', 'name'=>'TAJIKISTAN','code'=>'992'),
        'TK'=>array('code'=> 'TK', 'name'=>'TOKELAU','code'=>'690'),
        'TL'=>array('code'=> 'TL', 'name'=>'TIMOR-LESTE','code'=>'670'),
        'TM'=>array('code'=> 'TM', 'name'=>'TURKMENISTAN','code'=>'993'),
        'TN'=>array('code'=> 'TN', 'name'=>'TUNISIA','code'=>'216'),
        'TO'=>array('code'=> 'TO', 'name'=>'TONGA','code'=>'676'),
        'TR'=>array('code'=> 'TR', 'name'=>'TURKEY','code'=>'90'),
        'TT'=>array('code'=> 'TT', 'name'=>'TRINIDAD AND TOBAGO','code'=>'1868'),
        'TV'=>array('code'=> 'TV', 'name'=>'TUVALU','code'=>'688'),
        'TW'=>array('code'=> 'TW', 'name'=>'TAIWAN, PROVINCE OF CHINA','code'=>'886'),
        'TZ'=>array('code'=> 'TZ', 'name'=>'TANZANIA, UNITED REPUBLIC OF','code'=>'255'),
        'UA'=>array('code'=> 'UA', 'name'=>'UKRAINE','code'=>'380'),
        'UG'=>array('code'=> 'UG', 'name'=>'UGANDA','code'=>'256'),
        'US'=>array('code'=> 'US', 'name'=>'UNITED STATES','code'=>'1'),
        'UY'=>array('code'=> 'UY', 'name'=>'URUGUAY','code'=>'598'),
        'UZ'=>array('code'=> 'UZ', 'name'=>'UZBEKISTAN','code'=>'998'),
        'VA'=>array('code'=> 'VA', 'name'=>'HOLY SEE (VATICAN CITY STATE)','code'=>'39'),
        'VC'=>array('code'=> 'VC', 'name'=>'SAINT VINCENT AND THE GRENADINES','code'=>'1784'),
        'VE'=>array('code'=> 'VE', 'name'=>'VENEZUELA','code'=>'58'),
        'VG'=>array('code'=> 'VG', 'name'=>'VIRGIN ISLANDS, BRITISH','code'=>'1284'),
        'VI'=>array('code'=> 'VI', 'name'=>'VIRGIN ISLANDS, U.S.','code'=>'1340'),
        'VN'=>array('code'=> 'VN', 'name'=>'VIET NAM','code'=>'84'),
        'VU'=>array('code'=> 'VU', 'name'=>'VANUATU','code'=>'678'),
        'WF'=>array('code'=> 'WF', 'name'=>'WALLIS AND FUTUNA','code'=>'681'),
        'WS'=>array('code'=> 'WS', 'name'=>'SAMOA','code'=>'685'),
        'XK'=>array('code'=> 'XK', 'name'=>'KOSOVO','code'=>'381'),
        'YE'=>array('code'=> 'YE', 'name'=>'YEMEN','code'=>'967'),
        'YT'=>array('code'=> 'YT', 'name'=>'MAYOTTE','code'=>'262'),
        'ZA'=>array('code'=> 'ZA', 'name'=>'SOUTH AFRICA','code'=>'27'),
        'ZM'=>array('code'=> 'ZM', 'name'=>'ZAMBIA','code'=>'260'),
        'ZW'=>array('code'=> 'ZW', 'name'=>'ZIMBABWE','code'=>'263')
    );
    
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

    public function retraits(){
        $base_url = env('BASE_GTP_API');
        $programID = env('PROGRAM_ID');
        $authLogin = env('AUTH_LOGIN');
        $authPass = env('AUTH_PASS');
        $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');

        $client = new Client();
        $url =  $base_url."accounts/11931825/transactions";
        
        $body = [
            "transferType" => "CardToWallet",
            "transferAmount" => 1500,
            "currencyCode" => "XOF",
            "referenceMemo" => "Retrait de 1500 XOF de votre carte ",
            "last4Digits" => 6753
        ];

        $body = json_encode($body);
        
        $requestId = GtpRequest::create([
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        
        $headers = [
            'programId' => $programID,
            'requestId' => time().'111',
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
        } catch (BadResponseException $e) {
            $json = json_decode($e->getResponse()->getBody()->getContents());
            $error = $json->title.'.'.$json->detail;
            return $this->sendError($error, [], 500);
        }
    }
}