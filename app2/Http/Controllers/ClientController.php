<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserClient;
use App\Models\Depot;
use App\Models\Retrait;
use App\Models\KycClient;
use App\Models\TransfertOut;
use App\Models\CartePerso;
use App\Mail\AlerteRejet;
use App\Mail\AlerteValidation;
use App\Mail\MailAlerte;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\GtpRequest;
use App\Models\Recharge;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ClientController extends Controller
{

    public function searchClient(Request $request)
    {
        $base_url = env('BASE_GTP_API');
        $programID = env('PROGRAM_ID');
        $authLogin = env('AUTH_LOGIN');
        $authPass = env('AUTH_PASS');

        $code = $request->code;
        $data = [];

        try {   
            $client = new Client();
            $url = $base_url."accounts/".$code;
        
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
            $data['firstName'] = $clientInfo->firstName;
            $data['lastname'] = $clientInfo->lastName;

            if($clientInfo->cardStatus == 'LC'){
                return back()->withWarning('Cette carte est pour le moment bloqué.');
            }
        } catch (BadResponseException $e) {
            return back()->withWarning('');
        }
    
        try {
            $client = new Client();
            $url = $base_url."accounts/phone-number";
    
            $headers = [
                'programId' => $programID,
                'requestId' => Uuid::uuid4()->toString(),
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
    
            $accountInfoLists = json_decode($response->getBody())->accountInfoList;
            foreach ($accountInfoLists as $value) {
                if($value->accountId == $code){
                    $accountInfo = $value;
                    $data['lastFourDigits'] = $accountInfo->lastFourDigits;
                    break;
                }
            }
        } catch (BadResponseException $e) {
            $json = json_decode($e->getResponse()->getBody()->getContents());   
            $error = $json->title.'.'.$json->detail;
            return sendError($error, [], 500);
        }
        

        return json_encode($data);
    }


    public function clientAdd(Request $request)
    {
        try{
            $exist = UserClient::where(function ($query) use ($request) {
                $query->where('username',$request->codePays.$request->telephone)
                      ->orWhere('code', '=', $request->code);
            })->where('deleted',0)->first();
            
            if($exist){
                return back()->withWarning("Un compte existe deja avec ce numero de telephone ou cette carte");
            }
            UserClient::create([
                        'id' => Uuid::uuid4()->toString(),
                'name' => $request->name,
                'lastname' => $request->lastname,
                'code' => $request->code,
                'last' => $request->last,
                'username' => $request->codePays.$request->telephone,
                'password' => Hash::make(12345678),
                'status' => 0,
                'double_authentification' => 0,
                'sms' => 0,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return back()->withSuccess("Compte client crée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function clients(Request $request)
    {
        try{
            $userClients = UserClient::where('deleted',0)->where('verification',1)->orderBy('id','desc')->get();
            $countries = $this->countries;
            return view('clients.index',compact('userClients','countries'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function clientsAttentes(Request $request)
    {
        try{
            $userClients = UserClient::where('deleted',0)->where('verification',0)->orderBy('id','desc')->get();
            $countries = $this->countries;
            return view('clients.attentes',compact('userClients','countries'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }


    public function clientEdit(Request $request)
    {   
        try{
            $userClient = UserClient::where('id',$request->id)->where('deleted',0)->first();

            $userClient->name = $request->name;
            $userClient->lastname = $request->lastname;
            $userClient->code = $request->code;
            $userClient->last = $request->last;
            $userClient->updated_at = Carbon::now();
            $userClient->save();
            return back()->withSuccess("Modification effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function clientDelete(Request $request)
    {
        try{
            $userClient = UserClient::where('id',$request->id)->where('deleted',0)->first();

            $userClient->deleted = 1;
            $userClient->updated_at = Carbon::now();
            $userClient->save();
            return back()->withSuccess("Suppression effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function clientResetPassword(Request $request)
    {
        try{
            $userClient = UserClient::where('id',$request->id)->where('deleted',0)->first();

            $userClient->password = Hash::make(12345678);
            $userClient->updated_at = Carbon::now();
            $userClient->save();
            return back()->withSuccess("Reinitialisation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function clientActivation(Request $request)
    {
        try{
            $userClient = UserClient::where('id',$request->id)->where('deleted',0)->first();

            $userClient->status = 1;
            $userClient->updated_at = Carbon::now();
            $userClient->save();
            return back()->withSuccess("Activation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function clientDesactivation(Request $request)
    {
        try{
            $userClient = UserClient::where('id',$request->id)->where('deleted',0)->first();

            $userClient->status = 0;
            $userClient->updated_at = Carbon::now();
            $userClient->save();
            return back()->withSuccess("Desactivation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function clientValidation(Request $request)
    {
        try{
            $userClient = UserClient::where('id',$request->id)->where('deleted',0)->first();

            $userClient->status = 1;
            $userClient->verification = 1;
            $userClient->updated_at = Carbon::now();
            $userClient->save();

            if($userClient->kycClient->email){
                Mail::to([$userClient->kycClient->email,])->send(new AlerteValidation());
            }
            return back()->withSuccess("Validation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function clientRejet(Request $request)
    {
        try{
            $userClient = UserClient::where('id',$request->id)->where('deleted',0)->first();

            $userClient->verification = 0;
            if($request->niveau == 2){
                $motif = 'Information incorrectes';
                $userClient->verification_step_two = 0;
            }else if($request->niveau == 3){
                $motif = 'Pieces ou photo non valide';
                $userClient->verification_step_three = 0;
            }

            $description = $request->description;

            $userClient->updated_at = Carbon::now();
            $userClient->save();

            if($userClient->kycClient->email){
                Mail::to([$userClient->kycClient->email,])->send(new AlerteRejet(['motif' => $motif,'description' => $description]));
            }


            return back()->withSuccess("Rejet effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function clientDetails(Request $request)
    {
        try{
            $userClient = UserClient::where('id',$request->id)->where('deleted',0)->first();
            $depots = Depot::where('user_client_id',$request->id)->where('partenaire_id','<>',null)->where('deleted',0)->get();
            $retraits = Retrait::where('user_client_id',$request->id)->where('deleted',0)->get();
            $transferts = TransfertOut::where('user_client_id',$request->id)->where('deleted',0)->get();
            $countries = $this->countries;
            return view('clients.detail',compact('userClient','depots','retraits','transferts','countries'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function clientOperationsAttentes(Request $request)
    {
        try{
            
            $depots = Recharge::where('status','pending')->where('deleted',0)->get();
            foreach($depots as $depot){
                $depot->date = $depot->created_at->format('d-m-Y');
                $depot->type = 'Recharge';
                $depot->partenaire = $depot->partenaire;
                $depot->userClient = $depot->userClient;
            }

            $transferts = TransfertOut::where('status','pending')->where('deleted',0)->get();
            foreach($transferts as $transfert){
                $transfert->date = $transfert->created_at->format('d-m-Y');
                $transfert->type = 'Transfert';
                $transfert->partenaire = $transfert->partenaire;
                $transfert->userClient = $transfert->userClient;
            }
            
            $transactions = array_merge($depots->toArray(), $transferts->toArray());
            
        
            array_multisort(
                array_map(
                    static function ($element) {
                        return $element['created_at'];
                    },
                    $transactions
                ),
                SORT_DESC,
                $transactions
            );
            return view('clients.operations.attentes',compact('transactions'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function clientOperationsFinalises(Request $request)
    {
        try{
            
            $depots = Recharge::where('status','completed')->where('deleted',0)->get();
            foreach($depots as $depot){
                $depot->date = $depot->created_at->format('d-m-Y');
                $depot->type = 'Recharge';
                $depot->partenaire = $depot->partenaire;
                $depot->userClient = $depot->userClient;
            }

            $transferts = TransfertOut::where('status','completed')->where('deleted',0)->get();
            foreach($transferts as $transfert){
                $transfert->date = $transfert->created_at->format('d-m-Y');
                $transfert->type = 'Transfert';
                $transfert->partenaire = $transfert->partenaire;
                $transfert->userClient = $transfert->userClient;
            }
            
            $transactions = array_merge($depots->toArray(), $transferts->toArray());
            
        
            array_multisort(
                array_map(
                    static function ($element) {
                        return $element['created_at'];
                    },
                    $transactions
                ),
                SORT_DESC,
                $transactions
            );
            return view('clients.operations.finalises',compact('transactions'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function kycEdit(Request $request)
    { 
        //dd($request);
        try {
            $url = null;
            if($request->piece_file){
                $filename = time().'.'.$request->piece_file->getClientOriginalExtension();
                $request->piece_file->move('storage/clients/pieces/', $filename);
                $url = '/storage/clients/pieces/'.$filename;
            }

            $kyc = KycClient::where('id',$request->id)->where('deleted',0)->first();
            //dd($kyc,strtoupper(date('d-M-Y', strtotime($request->birthday))));
            $kyc->name = $request->name;
            $kyc->lastname = $request->lastname;
            $kyc->telephone = $request->code_pays.' '.$request->telephone;
            $kyc->email = $request->email;
            $kyc->birthday = strtoupper(date('d-M-Y', strtotime($request->birthday)));
            $kyc->departement = $request->departement;
            $kyc->city = $request->city;
            $kyc->address = $request->address;
            $kyc->profession = $request->profession;
            $kyc->revenu = $request->revenu;
            $kyc->piece_type = $request->piece_type;
            $kyc->piece_id = $request->piece_id;
            $kyc->piece_file = $url;
            $kyc->updated_at = Carbon::now();
            $kyc->save();
            return back()->withSuccess("Modification effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        } 
    }

    public function cartePerso(Request $request)
    {
        try{
            $cartes = CartePerso::where('deleted',0)->get();
            $countries = $this->countries;
            return view('clients.cartePerso',compact('cartes','countries'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function addCartePersoUnique(Request $request)
    {
        try{

            $data = $request->all();
            $birthday = explode('-',$data['naissance']);
            $birthday = $birthday[2].'-'.$this->dateLibelle[$birthday[1]].'-'.$birthday[0];


            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');


            $client = new Client();
            $url = $base_url."accounts/personalized";
            
            $name = $data['nom'].' '.$data['prenom'];
            if (strlen($name) > 19){
                $name = substr($name, 0, 19);
            }

            $body = [
                "firstName" => $data['nom'],
                "lastName" => $data['prenom'],
                "preferredName" => $name,
                "address1" => $data['adresse'],
                "city" => $data['ville'],
                "country" => $data['pays'],
                "stateRegion" => $data['dep'],
                "birthDate" =>  $birthday,
                "idType" => $data['piece_type'],
                "idValue" => $data['numpiece'],
                "mobilePhoneNumber" => [
                "countryCode" => $data['code'],
                "number" =>  $data['tel'],
                ],
                "emailAddress" => $data['email'],
                "accountSource" => "OTHER",
                "referredBy" => 11874629,
                "subCompany" => 11874629,
                //"return" => "RETURNPASSCODE"
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
                return back()->withWarning($error);
            }
            // Enregistrer la carte perso

            $user = Auth::user();
            $filename = time().'.'.$request->piece->getClientOriginalExtension();
            $request->piece->move('storage/client/', $filename);
            $url_piece = 'storage/client/'.$filename;

            $kyc = KycClient::create([
                        'id' => Uuid::uuid4()->toString(),
                'name' => $data['nom'],
                'lastname' => $data['prenom'],
                'email' => $data['email'],
                'telephone' => $data['code'].' '.$data['tel'],
                'birthday' => $birthday,
                'departement' => $data['dep'],
                'city' => $data['ville'],
                'country' => $data['pays'],
                'address' => $data['adresse'],
                'profession' => $data['prof'],
                'revenu' => $data['revenu'],
                'piece_type' => $data['piece_type'],
                'piece_id' => $data['numpiece'],
                'piece_file' => $url_piece,
                'type' => 'Personalisé',
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);


            CartePerso::create([
                        'id' => Uuid::uuid4()->toString(),
                'kyc_client_id' => $kyc->id,
                'last' => $responseBody->registrationLast4Digits,
                'code' => $responseBody->registrationAccountId,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $message = 'Votre carte est disponible. Les 4 derniers chiffre de votre carte sont : '.$responseBody->registrationLast4Digits.'. et son CustomerID est : '.$responseBody->registrationAccountId;
            $arr = ['messages'=> $message,'objet'=>'Activation de la carte','from'=>'noreply-bcv@bestcash.me'];
            Mail::to([$kyc->email,])->send(new MailAlerte($arr));
            
            return back()->withSuccess('Vente effectuée avec succes');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
    

    public $countries = array(
        'AD'=>array('name'=>'ANDORRA','code'=>'376'),
        'AE'=>array('name'=>'UNITED ARAB EMIRATES','code'=>'971'),
        'AF'=>array('name'=>'AFGHANISTAN','code'=>'93'),
        'AG'=>array('name'=>'ANTIGUA AND BARBUDA','code'=>'1268'),
        'AI'=>array('name'=>'ANGUILLA','code'=>'1264'),
        'AL'=>array('name'=>'ALBANIA','code'=>'355'),
        'AM'=>array('name'=>'ARMENIA','code'=>'374'),
        'AN'=>array('name'=>'NETHERLANDS ANTILLES','code'=>'599'),
        'AO'=>array('name'=>'ANGOLA','code'=>'244'),
        'AQ'=>array('name'=>'ANTARCTICA','code'=>'672'),
        'AR'=>array('name'=>'ARGENTINA','code'=>'54'),
        'AS'=>array('name'=>'AMERICAN SAMOA','code'=>'1684'),
        'AT'=>array('name'=>'AUSTRIA','code'=>'43'),
        'AU'=>array('name'=>'AUSTRALIA','code'=>'61'),
        'AW'=>array('name'=>'ARUBA','code'=>'297'),
        'AZ'=>array('name'=>'AZERBAIJAN','code'=>'994'),
        'BA'=>array('name'=>'BOSNIA AND HERZEGOVINA','code'=>'387'),
        'BB'=>array('name'=>'BARBADOS','code'=>'1246'),
        'BD'=>array('name'=>'BANGLADESH','code'=>'880'),
        'BE'=>array('name'=>'BELGIUM','code'=>'32'),
        'BF'=>array('name'=>'BURKINA FASO','code'=>'226'),
        'BG'=>array('name'=>'BULGARIA','code'=>'359'),
        'BH'=>array('name'=>'BAHRAIN','code'=>'973'),
        'BI'=>array('name'=>'BURUNDI','code'=>'257'),
        'BJ'=>array('name'=>'BENIN','code'=>'229'),
        'BL'=>array('name'=>'SAINT BARTHELEMY','code'=>'590'),
        'BM'=>array('name'=>'BERMUDA','code'=>'1441'),
        'BN'=>array('name'=>'BRUNEI DARUSSALAM','code'=>'673'),
        'BO'=>array('name'=>'BOLIVIA','code'=>'591'),
        'BR'=>array('name'=>'BRAZIL','code'=>'55'),
        'BS'=>array('name'=>'BAHAMAS','code'=>'1242'),
        'BT'=>array('name'=>'BHUTAN','code'=>'975'),
        'BW'=>array('name'=>'BOTSWANA','code'=>'267'),
        'BY'=>array('name'=>'BELARUS','code'=>'375'),
        'BZ'=>array('name'=>'BELIZE','code'=>'501'),
        'CA'=>array('name'=>'CANADA','code'=>'1'),
        'CC'=>array('name'=>'COCOS (KEELING) ISLANDS','code'=>'61'),
        'CD'=>array('name'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE','code'=>'243'),
        'CF'=>array('name'=>'CENTRAL AFRICAN REPUBLIC','code'=>'236'),
        'CG'=>array('name'=>'CONGO','code'=>'242'),
        'CH'=>array('name'=>'SWITZERLAND','code'=>'41'),
        'CI'=>array('name'=>'COTE D IVOIRE','code'=>'225'),
        'CK'=>array('name'=>'COOK ISLANDS','code'=>'682'),
        'CL'=>array('name'=>'CHILE','code'=>'56'),
        'CM'=>array('name'=>'CAMEROON','code'=>'237'),
        'CN'=>array('name'=>'CHINA','code'=>'86'),
        'CO'=>array('name'=>'COLOMBIA','code'=>'57'),
        'CR'=>array('name'=>'COSTA RICA','code'=>'506'),
        'CU'=>array('name'=>'CUBA','code'=>'53'),
        'CV'=>array('name'=>'CAPE VERDE','code'=>'238'),
        'CX'=>array('name'=>'CHRISTMAS ISLAND','code'=>'61'),
        'CY'=>array('name'=>'CYPRUS','code'=>'357'),
        'CZ'=>array('name'=>'CZECH REPUBLIC','code'=>'420'),
        'DE'=>array('name'=>'GERMANY','code'=>'49'),
        'DJ'=>array('name'=>'DJIBOUTI','code'=>'253'),
        'DK'=>array('name'=>'DENMARK','code'=>'45'),
        'DM'=>array('name'=>'DOMINICA','code'=>'1767'),
        'DO'=>array('name'=>'DOMINICAN REPUBLIC','code'=>'1809'),
        'DZ'=>array('name'=>'ALGERIA','code'=>'213'),
        'EC'=>array('name'=>'ECUADOR','code'=>'593'),
        'EE'=>array('name'=>'ESTONIA','code'=>'372'),
        'EG'=>array('name'=>'EGYPT','code'=>'20'),
        'ER'=>array('name'=>'ERITREA','code'=>'291'),
        'ES'=>array('name'=>'SPAIN','code'=>'34'),
        'ET'=>array('name'=>'ETHIOPIA','code'=>'251'),
        'FI'=>array('name'=>'FINLAND','code'=>'358'),
        'FJ'=>array('name'=>'FIJI','code'=>'679'),
        'FK'=>array('name'=>'FALKLAND ISLANDS (MALVINAS)','code'=>'500'),
        'FM'=>array('name'=>'MICRONESIA, FEDERATED STATES OF','code'=>'691'),
        'FO'=>array('name'=>'FAROE ISLANDS','code'=>'298'),
        'FR'=>array('name'=>'FRANCE','code'=>'33'),
        'GA'=>array('name'=>'GABON','code'=>'241'),
        'GB'=>array('name'=>'UNITED KINGDOM','code'=>'44'),
        'GD'=>array('name'=>'GRENADA','code'=>'1473'),
        'GE'=>array('name'=>'GEORGIA','code'=>'995'),
        'GH'=>array('name'=>'GHANA','code'=>'233'),
        'GI'=>array('name'=>'GIBRALTAR','code'=>'350'),
        'GL'=>array('name'=>'GREENLAND','code'=>'299'),
        'GM'=>array('name'=>'GAMBIA','code'=>'220'),
        'GN'=>array('name'=>'GUINEA','code'=>'224'),
        'GQ'=>array('name'=>'EQUATORIAL GUINEA','code'=>'240'),
        'GR'=>array('name'=>'GREECE','code'=>'30'),
        'GT'=>array('name'=>'GUATEMALA','code'=>'502'),
        'GU'=>array('name'=>'GUAM','code'=>'1671'),
        'GW'=>array('name'=>'GUINEA-BISSAU','code'=>'245'),
        'GY'=>array('name'=>'GUYANA','code'=>'592'),
        'HK'=>array('name'=>'HONG KONG','code'=>'852'),
        'HN'=>array('name'=>'HONDURAS','code'=>'504'),
        'HR'=>array('name'=>'CROATIA','code'=>'385'),
        'HT'=>array('name'=>'HAITI','code'=>'509'),
        'HU'=>array('name'=>'HUNGARY','code'=>'36'),
        'ID'=>array('name'=>'INDONESIA','code'=>'62'),
        'IE'=>array('name'=>'IRELAND','code'=>'353'),
        'IL'=>array('name'=>'ISRAEL','code'=>'972'),
        'IM'=>array('name'=>'ISLE OF MAN','code'=>'44'),
        'IN'=>array('name'=>'INDIA','code'=>'91'),
        'IQ'=>array('name'=>'IRAQ','code'=>'964'),
        'IR'=>array('name'=>'IRAN, ISLAMIC REPUBLIC OF','code'=>'98'),
        'IS'=>array('name'=>'ICELAND','code'=>'354'),
        'IT'=>array('name'=>'ITALY','code'=>'39'),
        'JM'=>array('name'=>'JAMAICA','code'=>'1876'),
        'JO'=>array('name'=>'JORDAN','code'=>'962'),
        'JP'=>array('name'=>'JAPAN','code'=>'81'),
        'KE'=>array('name'=>'KENYA','code'=>'254'),
        'KG'=>array('name'=>'KYRGYZSTAN','code'=>'996'),
        'KH'=>array('name'=>'CAMBODIA','code'=>'855'),
        'KI'=>array('name'=>'KIRIBATI','code'=>'686'),
        'KM'=>array('name'=>'COMOROS','code'=>'269'),
        'KN'=>array('name'=>'SAINT KITTS AND NEVIS','code'=>'1869'),
        'KP'=>array('name'=>'KOREA DEMOCRATIC PEOPLES REPUBLIC OF','code'=>'850'),
        'KR'=>array('name'=>'KOREA REPUBLIC OF','code'=>'82'),
        'KW'=>array('name'=>'KUWAIT','code'=>'965'),
        'KY'=>array('name'=>'CAYMAN ISLANDS','code'=>'1345'),
        'KZ'=>array('name'=>'KAZAKSTAN','code'=>'7'),
        'LA'=>array('name'=>'LAO PEOPLES DEMOCRATIC REPUBLIC','code'=>'856'),
        'LB'=>array('name'=>'LEBANON','code'=>'961'),
        'LC'=>array('name'=>'SAINT LUCIA','code'=>'1758'),
        'LI'=>array('name'=>'LIECHTENSTEIN','code'=>'423'),
        'LK'=>array('name'=>'SRI LANKA','code'=>'94'),
        'LR'=>array('name'=>'LIBERIA','code'=>'231'),
        'LS'=>array('name'=>'LESOTHO','code'=>'266'),
        'LT'=>array('name'=>'LITHUANIA','code'=>'370'),
        'LU'=>array('name'=>'LUXEMBOURG','code'=>'352'),
        'LV'=>array('name'=>'LATVIA','code'=>'371'),
        'LY'=>array('name'=>'LIBYAN ARAB JAMAHIRIYA','code'=>'218'),
        'MA'=>array('name'=>'MOROCCO','code'=>'212'),
        'MC'=>array('name'=>'MONACO','code'=>'377'),
        'MD'=>array('name'=>'MOLDOVA, REPUBLIC OF','code'=>'373'),
        'ME'=>array('name'=>'MONTENEGRO','code'=>'382'),
        'MF'=>array('name'=>'SAINT MARTIN','code'=>'1599'),
        'MG'=>array('name'=>'MADAGASCAR','code'=>'261'),
        'MH'=>array('name'=>'MARSHALL ISLANDS','code'=>'692'),
        'MK'=>array('name'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF','code'=>'389'),
        'ML'=>array('name'=>'MALI','code'=>'223'),
        'MM'=>array('name'=>'MYANMAR','code'=>'95'),
        'MN'=>array('name'=>'MONGOLIA','code'=>'976'),
        'MO'=>array('name'=>'MACAU','code'=>'853'),
        'MP'=>array('name'=>'NORTHERN MARIANA ISLANDS','code'=>'1670'),
        'MR'=>array('name'=>'MAURITANIA','code'=>'222'),
        'MS'=>array('name'=>'MONTSERRAT','code'=>'1664'),
        'MT'=>array('name'=>'MALTA','code'=>'356'),
        'MU'=>array('name'=>'MAURITIUS','code'=>'230'),
        'MV'=>array('name'=>'MALDIVES','code'=>'960'),
        'MW'=>array('name'=>'MALAWI','code'=>'265'),
        'MX'=>array('name'=>'MEXICO','code'=>'52'),
        'MY'=>array('name'=>'MALAYSIA','code'=>'60'),
        'MZ'=>array('name'=>'MOZAMBIQUE','code'=>'258'),
        'NA'=>array('name'=>'NAMIBIA','code'=>'264'),
        'NC'=>array('name'=>'NEW CALEDONIA','code'=>'687'),
        'NE'=>array('name'=>'NIGER','code'=>'227'),
        'NG'=>array('name'=>'NIGERIA','code'=>'234'),
        'NI'=>array('name'=>'NICARAGUA','code'=>'505'),
        'NL'=>array('name'=>'NETHERLANDS','code'=>'31'),
        'NO'=>array('name'=>'NORWAY','code'=>'47'),
        'NP'=>array('name'=>'NEPAL','code'=>'977'),
        'NR'=>array('name'=>'NAURU','code'=>'674'),
        'NU'=>array('name'=>'NIUE','code'=>'683'),
        'NZ'=>array('name'=>'NEW ZEALAND','code'=>'64'),
        'OM'=>array('name'=>'OMAN','code'=>'968'),
        'PA'=>array('name'=>'PANAMA','code'=>'507'),
        'PE'=>array('name'=>'PERU','code'=>'51'),
        'PF'=>array('name'=>'FRENCH POLYNESIA','code'=>'689'),
        'PG'=>array('name'=>'PAPUA NEW GUINEA','code'=>'675'),
        'PH'=>array('name'=>'PHILIPPINES','code'=>'63'),
        'PK'=>array('name'=>'PAKISTAN','code'=>'92'),
        'PL'=>array('name'=>'POLAND','code'=>'48'),
        'PM'=>array('name'=>'SAINT PIERRE AND MIQUELON','code'=>'508'),
        'PN'=>array('name'=>'PITCAIRN','code'=>'870'),
        'PR'=>array('name'=>'PUERTO RICO','code'=>'1'),
        'PT'=>array('name'=>'PORTUGAL','code'=>'351'),
        'PW'=>array('name'=>'PALAU','code'=>'680'),
        'PY'=>array('name'=>'PARAGUAY','code'=>'595'),
        'QA'=>array('name'=>'QATAR','code'=>'974'),
        'RO'=>array('name'=>'ROMANIA','code'=>'40'),
        'RS'=>array('name'=>'SERBIA','code'=>'381'),
        'RU'=>array('name'=>'RUSSIAN FEDERATION','code'=>'7'),
        'RW'=>array('name'=>'RWANDA','code'=>'250'),
        'SA'=>array('name'=>'SAUDI ARABIA','code'=>'966'),
        'SB'=>array('name'=>'SOLOMON ISLANDS','code'=>'677'),
        'SC'=>array('name'=>'SEYCHELLES','code'=>'248'),
        'SD'=>array('name'=>'SUDAN','code'=>'249'),
        'SE'=>array('name'=>'SWEDEN','code'=>'46'),
        'SG'=>array('name'=>'SINGAPORE','code'=>'65'),
        'SH'=>array('name'=>'SAINT HELENA','code'=>'290'),
        'SI'=>array('name'=>'SLOVENIA','code'=>'386'),
        'SK'=>array('name'=>'SLOVAKIA','code'=>'421'),
        'SL'=>array('name'=>'SIERRA LEONE','code'=>'232'),
        'SM'=>array('name'=>'SAN MARINO','code'=>'378'),
        'SN'=>array('name'=>'SENEGAL','code'=>'221'),
        'SO'=>array('name'=>'SOMALIA','code'=>'252'),
        'SR'=>array('name'=>'SURINAME','code'=>'597'),
        'ST'=>array('name'=>'SAO TOME AND PRINCIPE','code'=>'239'),
        'SV'=>array('name'=>'EL SALVADOR','code'=>'503'),
        'SY'=>array('name'=>'SYRIAN ARAB REPUBLIC','code'=>'963'),
        'SZ'=>array('name'=>'SWAZILAND','code'=>'268'),
        'TC'=>array('name'=>'TURKS AND CAICOS ISLANDS','code'=>'1649'),
        'TD'=>array('name'=>'CHAD','code'=>'235'),
        'TG'=>array('name'=>'TOGO','code'=>'228'),
        'TH'=>array('name'=>'THAILAND','code'=>'66'),
        'TJ'=>array('name'=>'TAJIKISTAN','code'=>'992'),
        'TK'=>array('name'=>'TOKELAU','code'=>'690'),
        'TL'=>array('name'=>'TIMOR-LESTE','code'=>'670'),
        'TM'=>array('name'=>'TURKMENISTAN','code'=>'993'),
        'TN'=>array('name'=>'TUNISIA','code'=>'216'),
        'TO'=>array('name'=>'TONGA','code'=>'676'),
        'TR'=>array('name'=>'TURKEY','code'=>'90'),
        'TT'=>array('name'=>'TRINIDAD AND TOBAGO','code'=>'1868'),
        'TV'=>array('name'=>'TUVALU','code'=>'688'),
        'TW'=>array('name'=>'TAIWAN, PROVINCE OF CHINA','code'=>'886'),
        'TZ'=>array('name'=>'TANZANIA, UNITED REPUBLIC OF','code'=>'255'),
        'UA'=>array('name'=>'UKRAINE','code'=>'380'),
        'UG'=>array('name'=>'UGANDA','code'=>'256'),
        'US'=>array('name'=>'UNITED STATES','code'=>'1'),
        'UY'=>array('name'=>'URUGUAY','code'=>'598'),
        'UZ'=>array('name'=>'UZBEKISTAN','code'=>'998'),
        'VA'=>array('name'=>'HOLY SEE (VATICAN CITY STATE)','code'=>'39'),
        'VC'=>array('name'=>'SAINT VINCENT AND THE GRENADINES','code'=>'1784'),
        'VE'=>array('name'=>'VENEZUELA','code'=>'58'),
        'VG'=>array('name'=>'VIRGIN ISLANDS, BRITISH','code'=>'1284'),
        'VI'=>array('name'=>'VIRGIN ISLANDS, U.S.','code'=>'1340'),
        'VN'=>array('name'=>'VIET NAM','code'=>'84'),
        'VU'=>array('name'=>'VANUATU','code'=>'678'),
        'WF'=>array('name'=>'WALLIS AND FUTUNA','code'=>'681'),
        'WS'=>array('name'=>'SAMOA','code'=>'685'),
        'XK'=>array('name'=>'KOSOVO','code'=>'381'),
        'YE'=>array('name'=>'YEMEN','code'=>'967'),
        'YT'=>array('name'=>'MAYOTTE','code'=>'262'),
        'ZA'=>array('name'=>'SOUTH AFRICA','code'=>'27'),
        'ZM'=>array('name'=>'ZAMBIA','code'=>'260'),
        'ZW'=>array('name'=>'ZIMBABWE','code'=>'263')
    );

    public $dateLibelle = array(
        '01'=> 'JAN',
        '02'=> 'FEB',
        '03'=> 'MAR',
        '04'=> 'APR',
        '05'=> 'MAY',
        '06'=> 'JUN',
        '07'=> 'JUL',
        '08'=> 'AUG',
        '09'=> 'SEP',
        '10'=> 'OCT',
        '11'=> 'NOV',
        '12'=> 'DEC'
    );

    function unaccent( $str )
    {
        $transliteration = array(
        'Ĳ' => 'I', 'Ö' => 'O','Œ' => 'O','Ü' => 'U','ä' => 'a','æ' => 'a',
        'ĳ' => 'i','ö' => 'o','œ' => 'o','ü' => 'u','ß' => 's','ſ' => 's',
        'À' => 'A','Á' => 'A','Â' => 'A','Ã' => 'A','Ä' => 'A','Å' => 'A',
        'Æ' => 'A','Ā' => 'A','Ą' => 'A','Ă' => 'A','Ç' => 'C','Ć' => 'C',
        'Č' => 'C','Ĉ' => 'C','Ċ' => 'C','Ď' => 'D','Đ' => 'D','È' => 'E',
        'É' => 'E','Ê' => 'E','Ë' => 'E','Ē' => 'E','Ę' => 'E','Ě' => 'E',
        'Ĕ' => 'E','Ė' => 'E','Ĝ' => 'G','Ğ' => 'G','Ġ' => 'G','Ģ' => 'G',
        'Ĥ' => 'H','Ħ' => 'H','Ì' => 'I','Í' => 'I','Î' => 'I','Ï' => 'I',
        'Ī' => 'I','Ĩ' => 'I','Ĭ' => 'I','Į' => 'I','İ' => 'I','Ĵ' => 'J',
        'Ķ' => 'K','Ľ' => 'K','Ĺ' => 'K','Ļ' => 'K','Ŀ' => 'K','Ł' => 'L',
        'Ñ' => 'N','Ń' => 'N','Ň' => 'N','Ņ' => 'N','Ŋ' => 'N','Ò' => 'O',
        'Ó' => 'O','Ô' => 'O','Õ' => 'O','Ø' => 'O','Ō' => 'O','Ő' => 'O',
        'Ŏ' => 'O','Ŕ' => 'R','Ř' => 'R','Ŗ' => 'R','Ś' => 'S','Ş' => 'S',
        'Ŝ' => 'S','Ș' => 'S','Š' => 'S','Ť' => 'T','Ţ' => 'T','Ŧ' => 'T',
        'Ț' => 'T','Ù' => 'U','Ú' => 'U','Û' => 'U','Ū' => 'U','Ů' => 'U',
        'Ű' => 'U','Ŭ' => 'U','Ũ' => 'U','Ų' => 'U','Ŵ' => 'W','Ŷ' => 'Y',
        'Ÿ' => 'Y','Ý' => 'Y','Ź' => 'Z','Ż' => 'Z','Ž' => 'Z','à' => 'a',
        'á' => 'a','â' => 'a','ã' => 'a','ā' => 'a','ą' => 'a','ă' => 'a',
        'å' => 'a','ç' => 'c','ć' => 'c','č' => 'c','ĉ' => 'c','ċ' => 'c',
        'ď' => 'd','đ' => 'd','è' => 'e','é' => 'e','ê' => 'e','ë' => 'e',
        'ē' => 'e','ę' => 'e','ě' => 'e','ĕ' => 'e','ė' => 'e','ƒ' => 'f',
        'ĝ' => 'g','ğ' => 'g','ġ' => 'g','ģ' => 'g','ĥ' => 'h','ħ' => 'h',
        'ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','ī' => 'i','ĩ' => 'i',
        'ĭ' => 'i','į' => 'i','ı' => 'i','ĵ' => 'j','ķ' => 'k','ĸ' => 'k',
        'ł' => 'l','ľ' => 'l','ĺ' => 'l','ļ' => 'l','ŀ' => 'l','ñ' => 'n',
        'ń' => 'n','ň' => 'n','ņ' => 'n','ŉ' => 'n','ŋ' => 'n','ò' => 'o',
        'ó' => 'o','ô' => 'o','õ' => 'o','ø' => 'o','ō' => 'o','ő' => 'o',
        'ŏ' => 'o','ŕ' => 'r','ř' => 'r','ŗ' => 'r','ś' => 's','š' => 's',
        'ť' => 't','ù' => 'u','ú' => 'u','û' => 'u','ū' => 'u','ů' => 'u',
        'ű' => 'u','ŭ' => 'u','ũ' => 'u','ų' => 'u','ŵ' => 'w','ÿ' => 'y',
        'ý' => 'y','ŷ' => 'y','ż' => 'z','ź' => 'z','ž' => 'z','Α' => 'A',
        'Ά' => 'A','Ἀ' => 'A','Ἁ' => 'A','Ἂ' => 'A','Ἃ' => 'A','Ἄ' => 'A',
        'Ἅ' => 'A','Ἆ' => 'A','Ἇ' => 'A','ᾈ' => 'A','ᾉ' => 'A','ᾊ' => 'A',
        'ᾋ' => 'A','ᾌ' => 'A','ᾍ' => 'A','ᾎ' => 'A','ᾏ' => 'A','Ᾰ' => 'A',
        'Ᾱ' => 'A','Ὰ' => 'A','ᾼ' => 'A','Β' => 'B','Γ' => 'G','Δ' => 'D',
        'Ε' => 'E','Έ' => 'E','Ἐ' => 'E','Ἑ' => 'E','Ἒ' => 'E','Ἓ' => 'E',
        'Ἔ' => 'E','Ἕ' => 'E','Ὲ' => 'E','Ζ' => 'Z','Η' => 'I','Ή' => 'I',
        'Ἠ' => 'I','Ἡ' => 'I','Ἢ' => 'I','Ἣ' => 'I','Ἤ' => 'I','Ἥ' => 'I',
        'Ἦ' => 'I','Ἧ' => 'I','ᾘ' => 'I','ᾙ' => 'I','ᾚ' => 'I','ᾛ' => 'I',
        'ᾜ' => 'I','ᾝ' => 'I','ᾞ' => 'I','ᾟ' => 'I','Ὴ' => 'I','ῌ' => 'I',
        'Θ' => 'T','Ι' => 'I','Ί' => 'I','Ϊ' => 'I','Ἰ' => 'I','Ἱ' => 'I',
        'Ἲ' => 'I','Ἳ' => 'I','Ἴ' => 'I','Ἵ' => 'I','Ἶ' => 'I','Ἷ' => 'I',
        'Ῐ' => 'I','Ῑ' => 'I','Ὶ' => 'I','Κ' => 'K','Λ' => 'L','Μ' => 'M',
        'Ν' => 'N','Ξ' => 'K','Ο' => 'O','Ό' => 'O','Ὀ' => 'O','Ὁ' => 'O',
        'Ὂ' => 'O','Ὃ' => 'O','Ὄ' => 'O','Ὅ' => 'O','Ὸ' => 'O','Π' => 'P',
        'Ρ' => 'R','Ῥ' => 'R','Σ' => 'S','Τ' => 'T','Υ' => 'Y','Ύ' => 'Y',
        'Ϋ' => 'Y','Ὑ' => 'Y','Ὓ' => 'Y','Ὕ' => 'Y','Ὗ' => 'Y','Ῠ' => 'Y',
        'Ῡ' => 'Y','Ὺ' => 'Y','Φ' => 'F','Χ' => 'X','Ψ' => 'P','Ω' => 'O',
        'Ώ' => 'O','Ὠ' => 'O','Ὡ' => 'O','Ὢ' => 'O','Ὣ' => 'O','Ὤ' => 'O',
        'Ὥ' => 'O','Ὦ' => 'O','Ὧ' => 'O','ᾨ' => 'O','ᾩ' => 'O','ᾪ' => 'O',
        'ᾫ' => 'O','ᾬ' => 'O','ᾭ' => 'O','ᾮ' => 'O','ᾯ' => 'O','Ὼ' => 'O',
        'ῼ' => 'O','α' => 'a','ά' => 'a','ἀ' => 'a','ἁ' => 'a','ἂ' => 'a',
        'ἃ' => 'a','ἄ' => 'a','ἅ' => 'a','ἆ' => 'a','ἇ' => 'a','ᾀ' => 'a',
        'ᾁ' => 'a','ᾂ' => 'a','ᾃ' => 'a','ᾄ' => 'a','ᾅ' => 'a','ᾆ' => 'a',
        'ᾇ' => 'a','ὰ' => 'a','ᾰ' => 'a','ᾱ' => 'a','ᾲ' => 'a','ᾳ' => 'a',
        'ᾴ' => 'a','ᾶ' => 'a','ᾷ' => 'a','β' => 'b','γ' => 'g','δ' => 'd',
        'ε' => 'e','έ' => 'e','ἐ' => 'e','ἑ' => 'e','ἒ' => 'e','ἓ' => 'e',
        'ἔ' => 'e','ἕ' => 'e','ὲ' => 'e','ζ' => 'z','η' => 'i','ή' => 'i',
        'ἠ' => 'i','ἡ' => 'i','ἢ' => 'i','ἣ' => 'i','ἤ' => 'i','ἥ' => 'i',
        'ἦ' => 'i','ἧ' => 'i','ᾐ' => 'i','ᾑ' => 'i','ᾒ' => 'i','ᾓ' => 'i',
        'ᾔ' => 'i','ᾕ' => 'i','ᾖ' => 'i','ᾗ' => 'i','ὴ' => 'i','ῂ' => 'i',
        'ῃ' => 'i','ῄ' => 'i','ῆ' => 'i','ῇ' => 'i','θ' => 't','ι' => 'i',
        'ί' => 'i','ϊ' => 'i','ΐ' => 'i','ἰ' => 'i','ἱ' => 'i','ἲ' => 'i',
        'ἳ' => 'i','ἴ' => 'i','ἵ' => 'i','ἶ' => 'i','ἷ' => 'i','ὶ' => 'i',
        'ῐ' => 'i','ῑ' => 'i','ῒ' => 'i','ῖ' => 'i','ῗ' => 'i','κ' => 'k',
        'λ' => 'l','μ' => 'm','ν' => 'n','ξ' => 'k','ο' => 'o','ό' => 'o',
        'ὀ' => 'o','ὁ' => 'o','ὂ' => 'o','ὃ' => 'o','ὄ' => 'o','ὅ' => 'o',
        'ὸ' => 'o','π' => 'p','ρ' => 'r','ῤ' => 'r','ῥ' => 'r','σ' => 's',
        'ς' => 's','τ' => 't','υ' => 'y','ύ' => 'y','ϋ' => 'y','ΰ' => 'y',
        'ὐ' => 'y','ὑ' => 'y','ὒ' => 'y','ὓ' => 'y','ὔ' => 'y','ὕ' => 'y',
        'ὖ' => 'y','ὗ' => 'y','ὺ' => 'y','ῠ' => 'y','ῡ' => 'y','ῢ' => 'y',
        'ῦ' => 'y','ῧ' => 'y','φ' => 'f','χ' => 'x','ψ' => 'p','ω' => 'o',
        'ώ' => 'o','ὠ' => 'o','ὡ' => 'o','ὢ' => 'o','ὣ' => 'o','ὤ' => 'o',
        'ὥ' => 'o','ὦ' => 'o','ὧ' => 'o','ᾠ' => 'o','ᾡ' => 'o','ᾢ' => 'o',
        'ᾣ' => 'o','ᾤ' => 'o','ᾥ' => 'o','ᾦ' => 'o','ᾧ' => 'o','ὼ' => 'o',
        'ῲ' => 'o','ῳ' => 'o','ῴ' => 'o','ῶ' => 'o','ῷ' => 'o','А' => 'A',
        'Б' => 'B','В' => 'V','Г' => 'G','Д' => 'D','Е' => 'E','Ё' => 'E',
        'Ж' => 'Z','З' => 'Z','И' => 'I','Й' => 'I','К' => 'K','Л' => 'L',
        'М' => 'M','Н' => 'N','О' => 'O','П' => 'P','Р' => 'R','С' => 'S',
        'Т' => 'T','У' => 'U','Ф' => 'F','Х' => 'K','Ц' => 'T','Ч' => 'C',
        'Ш' => 'S','Щ' => 'S','Ы' => 'Y','Э' => 'E','Ю' => 'Y','Я' => 'Y',
        'а' => 'A','б' => 'B','в' => 'V','г' => 'G','д' => 'D','е' => 'E',
        'ё' => 'E','ж' => 'Z','з' => 'Z','и' => 'I','й' => 'I','к' => 'K',
        'л' => 'L','м' => 'M','н' => 'N','о' => 'O','п' => 'P','р' => 'R',
        'с' => 'S','т' => 'T','у' => 'U','ф' => 'F','х' => 'K','ц' => 'T',
        'ч' => 'C','ш' => 'S','щ' => 'S','ы' => 'Y','э' => 'E','ю' => 'Y',
        'я' => 'Y','ð' => 'd','Ð' => 'D','þ' => 't','Þ' => 'T','ა' => 'a',
        'ბ' => 'b','გ' => 'g','დ' => 'd','ე' => 'e','ვ' => 'v','ზ' => 'z',
        'თ' => 't','ი' => 'i','კ' => 'k','ლ' => 'l','მ' => 'm','ნ' => 'n',
        'ო' => 'o','პ' => 'p','ჟ' => 'z','რ' => 'r','ს' => 's','ტ' => 't',
        'უ' => 'u','ფ' => 'p','ქ' => 'k','ღ' => 'g','ყ' => 'q','შ' => 's',
        'ჩ' => 'c','ც' => 't','ძ' => 'd','წ' => 't','ჭ' => 'c','ხ' => 'k',
        'ჯ' => 'j','ჰ' => 'h' 
        );
        $str = str_replace( array_keys( $transliteration ),
                            array_values( $transliteration ),
                            $str);
        return $str;
    }
}
