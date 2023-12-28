<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\UserClient;
use App\Models\UserPartenaire;
use App\Models\Depot;
use App\Models\Retrait;
use App\Models\AccountDistribution;
use App\Models\Recharge;
use App\Models\TransfertOut;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Uuid;

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
        try{
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');
            $accountId = env('AUTH_DISTRIBUTION_ACCOUNT');
    
            $comptesClientsEnAttentes = UserClient::where('deleted',0)->where('verification',0)->orderBy('id','desc')->get();
    
            $recharges = Recharge::where('status','pending')->where('deleted',0)->get();
            foreach($recharges as $recharge){
                $recharge->date = $recharge->created_at->format('d-m-Y');
                $recharge->type = 'Recharge';
                $recharge->partenaire = $recharge->partenaire;
                $recharge->userClient = $recharge->userClient;
            }
            $transferts = TransfertOut::where('status','pending')->where('deleted',0)->get();
            foreach($transferts as $transfert){
                $transfert->date = $transfert->created_at->format('d-m-Y');
                $transfert->type = 'Transfert';
                $transfert->partenaire = $transfert->partenaire;
                $transfert->userClient = $transfert->userClient;
            }
            $operationsClientsEnAttentes = array_merge($recharges->toArray(), $transferts->toArray());
            array_multisort(
                array_map(
                    static function ($element) {
                        return $element['created_at'];
                    },
                    $operationsClientsEnAttentes
                ),
                SORT_DESC,
                $operationsClientsEnAttentes
            );
                
            $depots = Depot::where('status','pending')->where('deleted',0)->get();
            foreach($depots as $depot){
                $depot->date = $depot->created_at->format('d-m-Y');
                $depot->type = 'Depot';
                $depot->partenaire = $depot->partenaire;
                $depot->userClient = $depot->userClient;
            }
            $retraits = Retrait::where('status','pending')->where('deleted',0)->get();
            foreach($retraits as $retrait){
                $retrait->date = $retrait->created_at->format('d-m-Y');
                $retrait->type = 'Retrait';
                $retrait->partenaire = $retrait->partenaire;
                $retrait->userClient = $retrait->userClient;
            }
            $operationsPartenairesEnAttentes = array_merge($depots->toArray(), $retraits->toArray());
            array_multisort(
                array_map(
                    static function ($element) {
                        return $element['created_at'];
                    },
                    $operationsPartenairesEnAttentes
                ),
                SORT_DESC,
                $operationsPartenairesEnAttentes
            );
    
            $solde = [
                'gtp' => 0,
                'bmo_debit' => 0,
                'bmo_credit' => 0,
                'kkiapay' => 0,
                'compte_partenaire' => 0
            ];
    
            $nbClients = count(UserClient::where('deleted',0)->where('status',1)->get());
            $nbPartenaires = count(UserPartenaire::where('deleted',0)->where('status',1)->get());
    
            try {
    
                $client = new Client();
                $url = $base_url."accounts/".$accountId."/balance";
        
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
        
                $solde['gtp'] = json_decode($response->getBody())->balance;
                $solde['compte_partenaire'] = AccountDistribution::where('deleted',0)->sum('solde');
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return back()->withError($error);
            }
    
            return view('welcome',compact('solde','nbClients','nbPartenaires','comptesClientsEnAttentes','operationsClientsEnAttentes','operationsPartenairesEnAttentes'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function searchData(Request $request)
    {
        $debut =  $newDate = date("Y-m-d", strtotime(str_replace(' ','',explode('-',$request->periode)[0])));
        $debut = $debut.' 00:00:00';
        $fin =  $newDate = date("Y-m-d", strtotime(str_replace(' ','',explode('-',$request->periode)[1])));
        $fin = $fin.' 23:59:59';
        
        $data = [];
        $data['nbDepots'] = count(Depot::where('deleted',0)->where('status',1)->whereBetween('created_at', [$debut, $fin])->get());
        $data['nbRetraits'] = count(Retrait::where('deleted',0)->where('status',1)->whereBetween('created_at', [$debut, $fin])->get());
        $data['nbTransferts'] = count(TransfertOut::where('deleted',0)->where('status',1)->whereBetween('created_at', [$debut, $fin])->get());
        return json_encode($data);
    }

    public function test(){
        $users = UserClient::where('deleted',0)->get();
        
        foreach($users as $user){
            //if($user->user_cards->get()){
                foreach($user->userCards as $item){
                    $item->is_first = 0;
                    $item->save();
                }
            //}
        }
    }
}