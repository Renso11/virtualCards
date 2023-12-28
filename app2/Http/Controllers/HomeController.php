<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Auth;
use App\Models\UserClient;
use App\Models\UserPartenaire;
use App\Models\Depot;
use App\Models\Retrait;
use App\Models\GtpRequest;
use App\Models\Permission;
use App\Models\AccountDistribution;
use App\Models\TransfertOut;
use Session;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');    
    }
    public function index()
    {
        return redirect(route('welcome'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function welcome()
    {
        //dd(Session::get('first'));
        if(Session::get('first') == null){
            Auth::user()->last_connexion = date('d-M-Y H:i:s');
            Auth::user()->save();
            Session::put('first', date('d-M-Y H:i:s'));
        }
        $debut = date("Y-m-01 00:00:00");
        $fin = date("Y-m-d H:i:s");
        
        $totalDepots = count(Depot::where('deleted',0)->where('status','completed')->get());
        $totalRetraits = count(Retrait::where('deleted',0)->where('status','completed')->get());

        $nbClients = count(UserClient::where('deleted',0)->where('status',1)->get());
        $nbPartenaires = count(UserPartenaire::where('deleted',0)->where('status',1)->get());

        $lastDepots = Depot::where('deleted',0)->where('status','completed')->limit(5)->get();
        $lastRetraits = Retrait::where('deleted',0)->where('status','completed')->limit(5)->get();

        $balance = 0;

        $base_url = env('BASE_GTP_API');
        $programID = env('PROGRAM_ID');
        $authLogin = env('AUTH_LOGIN');
        $authPass = env('AUTH_PASS');
        $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');
        //try {
            $requestId = GtpRequest::create([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $client = new Client();
            $url = $base_url."accounts/".$accountId."/balance";
    
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
    
            $balance = json_decode($response->getBody())->balance;
        /*} catch (BadResponseException $e) {
            $json = json_decode($e->getResponse()->getBody()->getContents());
            $error = $json->title.'.'.$json->detail;
            return back()->withError($error);
        } */
        $sommeDistribution = AccountDistribution::where('deleted',0)->sum('solde');

        $months = $depots = $retraits = $transferts = [];
        
        for ($i = 0; $i < 6; $i++) {
            $debut = date("Y-m-01 00:00:00", strtotime("-$i month"));
            $fin = date("Y-m-t 23:59:59", strtotime("-$i month"));
            $nbrDepots = count(Depot::where('deleted',0)->where('status','completed')->whereBetween('created_at', [$debut, $fin])->get());
            $nbrRetraits = count(Retrait::where('deleted',0)->where('status','completed')->whereBetween('created_at', [$debut, $fin])->get());
            $nbrTransferts = count(TransfertOut::where('deleted',0)->where('status','completed')->whereBetween('created_at', [$debut, $fin])->get());
            $months[$i] = date('M', strtotime("-$i month"));
            $depots[$i] = $nbrDepots;
            $retraits[$i] = $nbrRetraits;
            $transferts[$i] = $nbrTransferts;
        }
        return view('welcome',compact('balance','sommeDistribution','totalDepots','totalRetraits','nbClients','nbPartenaires','lastDepots','lastRetraits','months','depots','retraits','transferts'));
    }

    public function searchData(Request $request)
    {
        $debut =  $newDate = date("Y-m-d", strtotime(str_replace(' ','',explode('-',$request->periode)[0])));
        $debut = $debut.' 00:00:00';
        $fin =  $newDate = date("Y-m-d", strtotime(str_replace(' ','',explode('-',$request->periode)[1])));
        $fin = $fin.' 23:59:59';
        
        $data = [];
        $data['nbDepots'] = count(Depot::where('deleted',0)->where('status',1)->where('validate',1)->whereBetween('created_at', [$debut, $fin])->get());
        $data['nbRetraits'] = count(Retrait::where('deleted',0)->where('status',1)->where('validate',1)->whereBetween('created_at', [$debut, $fin])->get());
        $data['nbTransferts'] = count(TransfertOut::where('deleted',0)->where('status',1)->whereBetween('created_at', [$debut, $fin])->get());
        return json_encode($data);
    }

    public function test(){$client = new Client();
        $base_url = env('BASE_GTP_API');
        $programID = env('PROGRAM_ID');
        $authLogin = env('AUTH_LOGIN');
        $authPass = env('AUTH_PASS');
        $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');
        $url = $base_url."accounts/13173334/transactions";
        
        $body = [
            "transferType" => "WalletToCard",
            "transferAmount" => 150000,
            "currencyCode" => "XOF",
            "referenceMemo" => "Depot de 200000 XOF sur votre carte avec des frais de 0",
            "last4Digits" => 3229
        ];

        $body = json_encode($body);
        
        $requestId = GtpRequest::create([
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        
        $headers = [
            'programId' => 66,
            'requestId' => time(),
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
            dd('ok');
        } catch (BadResponseException $e) {
            dd($e);
        }
    }
}