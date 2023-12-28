<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recharge;
use App\Models\Depot;
use App\Models\RechargementPartenaire;
use App\Models\AccountDistributionOperation;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use App\Models\GtpRequest;
use GuzzleHttp\Exception\BadResponseException;
use Ramsey\Uuid\Uuid;

class RechargementController extends Controller
{
    public function rechargementClients(Request $request)
    { 
        try {
            $recharges = Depot::where('deleted',0)->where('partenaire_id',null)->orderBy('id','desc')->get();
            return view('recharges.index',compact('recharges'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        } 
    }
    
    public function rechargementAttentes(Request $request)
    { 
        try {
            $recharges = Recharge::where('deleted',0)->where('status',null)->orderBy('id','desc')->get();
            return view('recharges.index',compact('recharges'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        } 
    }
    public function rechargementAttentesDelete(Request $request)
    {   
        try{
            $rechargement = Recharge::where('id',$request->id)->where('deleted',0)->first();
            $rechargement->deleted = 1;
            $rechargement->save();
            return back()->withSuccess("Supression effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function rechargementAttentesValidation(Request $request)
    {   
        try{
            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');

            $rechargement = Recharge::where('id',$request->id)->where('deleted',0)->orderBy('id','desc')->first();

            //recuperation solde avant et apres

            $client = new Client();
            $url = $base_url."accounts/".$rechargement->userClient->code."/balance";

            $headers = [
                'programId' => $programID,
                'requestId' => Uuid::uuid4()->toString(),
            ];

            $auth = [
                $authLogin,
                $authPass
            ];

            try {
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

            $soldeAv = $balance->balance;
            $soldeAp = $soldeAv + $rechargement->montant;
            
            $client = new Client();
            $url =  $base_url."accounts/".$rechargement->userClient->code."/transactions";
            
            $body = [
                "transferType" => "WalletToCard",
                "transferAmount" => $rechargement->montant,
                "currencyCode" => "XOF",
                "referenceMemo" => "Rechargement de ".$rechargement->montant." XOF sur votre carte ",
                "last4Digits" => $rechargement->userClient->last
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

            $rechargement->status = 1;
            $rechargement->validateur_id = 1;
            $rechargement->updated_at = Carbon::now();
            $rechargement->save();

            Depot::create([
                        'id' => Uuid::uuid4()->toString(),
                'user_client_id' => $rechargement->userClient->id,
                'partenaire_id' => null,
                'user_partenaire_id' => null,
                'libelle' => 'Rechargement du compte',
                'solde_avant' => $soldeAv,
                'montant' => $rechargement->montant,
                'solde_apres' => $soldeAp,
                'frais' => 0,
                'status' => 1,
                'deleted' => 0,
                'validate' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return back()->withSuccess("Rechargement validé et effectué avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function rechargementAttentesRejet(Request $request)
    {   
        try{
            $recharges = Recharge::where('deleted',0)->where('status',0)->get();

            $rechargement->status = 0;
            $rechargement->motif_rejet = $request->motif_rejet;
            $rechargement->rejeteur_id = 1;
            $rechargement->updated_at = Carbon::now();
            $rechargement->save();

            return back()->withSuccess("Rejet effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
    
    public function rechargementFinalises(Request $request)
    {   
        try{
            $recharges = Recharge::where('deleted',0)->where('status',1)->orderBy('id','desc')->get();
            return view('recharges.finalises',compact('recharges'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
    
    public function rechargementRejetes(Request $request)
    {   
        try{
            $recharges = Recharge::where('deleted',0)->where('status',0)->orderBy('id','desc')->get();
            return view('recharges.rejetes',compact('recharges'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
