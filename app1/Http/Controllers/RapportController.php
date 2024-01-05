<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Depot;
use App\Models\Retrait;
use App\Models\UserClient;
use App\Models\Partenaire;
use App\Models\Recharge;
use App\Models\Transfert;
use App\Models\TransfertOut;
use Illuminate\Support\Carbon;
use PDF;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    public function rapportOperationClient(Request $request)
    {   
        return view('rapport.operations.client');
    }

    public function searchRapportOperationClient(Request $request)
    {   
        $debut = explode('T',$request->debut)[0].' '.explode('T',$request->debut)[1].':00';
        $fin = explode('T',$request->fin)[0].' '.explode('T',$request->fin)[1].':00';

        if($request->telephone){
            $clients = UserClient::where('username','LIKE',"%{$request->telephone}%")->where('deleted',0)->where('verification',1)->get()->pluck('id');
        }

        //dump($request);die();
        $transactions = [];

        $nbDepots = $sumDepots = $sumFraisDepots = 0;
        if(($request->type_operations && in_array('depot',$request->type_operations)) || !$request->type_operations){
            $depots = Depot::where('status','completed')->where('deleted',0)->whereBetween('created_at',[$debut,$fin]);
            $request->telephone ? $depots = $depots->whereIn('user_client_id',$clients) : $depots = $depots;
            $sumDepots = $depots->sum('montant');
            $sumFraisDepots = $depots->sum('frais_bcb');
            $depots = $depots->get();
            $nbDepots = count($depots);
            foreach($depots as $depot){
                $depot->date = $depot->created_at->format('d-m-Y');
                $depot->type = 'Depot';
                $depot->partenaire = $depot->partenaire;
                $depot->userClient = $depot->userClient;
            }
            $transactions = array_merge($transactions, $depots->toArray());
        }

        $nbRetraits = $sumRetraits = $sumFraisRetraits = 0;
        if(($request->type_operations && in_array('retrait',$request->type_operations)) || !$request->type_operations){
            $retraits = Retrait::where('status','completed')->where('deleted',0)->whereBetween('created_at',[$debut,$fin]);
            $request->telephone ? $retraits = $retraits->whereIn('user_client_id',$clients) : $retraits = $retraits;
            $sumRetraits = $retraits->sum('montant');
            $sumFraisRetraits = $retraits->sum('frais_bcb');
            $retraits = $retraits->get();
            $nbRetraits = count($retraits);
            foreach($retraits as $retrait){
                $retrait->date = $retrait->created_at->format('d-m-Y');
                $retrait->type = 'Retrait';
                $retrait->partenaire = $retrait->partenaire;
                $retrait->userClient = $retrait->userClient;
            }
            $transactions = array_merge($transactions, $retraits->toArray());
        }

        $nbRecharges = $sumRecharges = $sumFraisRecharges = 0;
        if(($request->type_operations && in_array('rechargement',$request->type_operations)) || !$request->type_operations){
            $recharges = Recharge::where('status','completed')->where('deleted',0)->whereBetween('created_at',[$debut,$fin]);
            $request->telephone ? $recharges = $recharges->whereIn('user_client_id',$clients) : $recharges = $recharges;
            $sumRecharges = $recharges->sum('montant');
            $sumFraisRecharges = $recharges->sum('frais_bcb');
            $recharges = $recharges->get();
            $nbRecharges = count($recharges);
            foreach($recharges as $recharge){
                $recharge->date = $recharge->created_at->format('d-m-Y');
                $recharge->type = 'Recharge';
                $recharge->partenaire = $recharge->partenaire;
                $recharge->userClient = $recharge->userClient;
            }
            $transactions = array_merge($transactions, $recharges->toArray());
        }

        $nbTransferts = $sumTransferts = $sumFraisTransferts = 0;
        if(($request->type_operations && in_array('transfert',$request->type_operations)) || !$request->type_operations){
            $transferts = TransfertOut::where('status','completed')->where('deleted',0)->whereBetween('created_at',[$debut,$fin]);
            $request->telephone ? $transferts = $transferts->whereIn('user_client_id',$clients) : $transferts = $transferts;
            $sumTransferts = $transferts->sum('montant');
            $sumFraisTransferts = $transferts->sum('frais_bcb');
            $transferts = $transferts->get();
            $nbTransferts = count($transferts);
            foreach($transferts as $transfert){
                $transfert->date = $transfert->created_at->format('d-m-Y');
                $transfert->type = 'Transfert';
                $transfert->partenaire = $transfert->partenaire;
                $transfert->userClient = $transfert->userClient;
            }
            $transactions = array_merge($transactions, $transferts->toArray());
        }
        
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

        $statNb = $statSum = $statFrais = [];

        $statNb['retrait'] = $nbRetraits;
        $statNb['transfert'] = $nbTransferts;
        $statNb['depot'] = $nbDepots;
        $statNb['recharge'] = $nbRecharges;
        
        $statSum['retrait'] = $sumRetraits;
        $statSum['transfert'] = $sumTransferts;
        $statSum['depot'] = $sumDepots;
        $statSum['recharge'] = $sumRecharges;
        
        $statFrais['retrait'] = $sumFraisRetraits;
        $statFrais['transfert'] = $sumFraisTransferts;
        $statFrais['depot'] = $sumFraisDepots;
        $statFrais['recharge'] = $sumFraisRecharges;
        return view('rapport.operations.etatClient',compact('transactions','statNb','statSum','statFrais',));
    }

    public function rapportOperationPartenaire(Request $request)
    {   
        $partenaires = Partenaire::where('deleted',0)->get();
        return view('rapport.operations.partenaire',compact('partenaires'));
    }

    public function searchRapportOperationPartenaire(Request $request)
    {   
        $debut = explode('T',$request->debut)[0].' '.explode('T',$request->debut)[1].':00';
        $fin = explode('T',$request->fin)[0].' '.explode('T',$request->fin)[1].':00';

        if($request->partenaires){
            $partenaires = Partenaire::whereIn('id',$request->partenaires)->where('deleted',0)->get()->pluck('id');
        }else{
            $partenaires = Partenaire::where('deleted',0)->get()->pluck('id');
        }

        //dump($request);die();
        $transactions = [];

        $nbDepots = $sumDepots = $sumFraisDepots = 0;
        if(($request->type_operations && in_array('depot',$request->type_operations)) || !$request->type_operations){
            $depots = Depot::where('status','completed')->where('deleted',0)->whereBetween('created_at',[$debut,$fin]);
            $request->telephone ? $depots = $depots->whereIn('partenaire_id',$partenaires) : $depots = $depots;
            $sumDepots = $depots->sum('montant');
            $sumFraisDepots = $depots->sum('frais_bcb');
            $depots = $depots->get();
            $nbDepots = count($depots);
            foreach($depots as $depot){
                $depot->date = $depot->created_at->format('d-m-Y');
                $depot->type = 'Depot';
                $depot->partenaire = $depot->partenaire;
                $depot->userClient = $depot->userClient;
            }
            $transactions = array_merge($transactions, $depots->toArray());
        }

        $nbRetraits = $sumRetraits = $sumFraisRetraits = 0;
        if(($request->type_operations && in_array('retrait',$request->type_operations)) || !$request->type_operations){
            $retraits = Retrait::where('status','completed')->where('deleted',0)->whereBetween('created_at',[$debut,$fin]);
            $request->telephone ? $retraits = $retraits->whereIn('partenaire_id',$partenaires) : $retraits = $retraits;
            $sumRetraits = $retraits->sum('montant');
            $sumFraisRetraits = $retraits->sum('frais_bcb');
            $retraits = $retraits->get();
            $nbRetraits = count($retraits);
            foreach($retraits as $retrait){
                $retrait->date = $retrait->created_at->format('d-m-Y');
                $retrait->type = 'Retrait';
                $retrait->partenaire = $retrait->partenaire;
                $retrait->userClient = $retrait->userClient;
            }
            $transactions = array_merge($transactions, $retraits->toArray());
        }
        
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

        $statNb = $statSum = $statFrais = [];

        $statNb['retrait'] = $nbRetraits;
        $statNb['depot'] = $nbDepots;
        
        $statSum['retrait'] = $sumRetraits;
        $statSum['depot'] = $sumDepots;
        
        $statFrais['retrait'] = $sumFraisRetraits;
        $statFrais['depot'] = $sumFraisDepots;
        
        return view('rapport.operations.etatPartenaire',compact('transactions','statNb','statSum','statFrais',));
    }

    public function rapportDepots(Request $request)
    {   
        $partenaires = Partenaire::where('deleted',0)->get();
        return view('rapport.depots',compact('partenaires'));
    }

    public function searchDepots(Request $request)
    {   
        $debut =  $newDate = date("Y-m-d", strtotime(str_replace(' ','',explode('-',$request->periode)[0])));
        $debut = $debut.' 00:00:00';
        $fin =  $newDate = date("Y-m-d", strtotime(str_replace(' ','',explode('-',$request->periode)[1])));
        $fin = $fin.' 23:59:59';
        $partenaires = $request->partenaires;
        if(is_array($request->partenaires)){
            $depots = Depot::where('status',1)->where('validate',1)->where('deleted',0)->whereIn('partenaire_id',$partenaires)->whereBetween('created_at', [$debut, $fin])->get()->all();
        }else{
            $depots = Depot::where('status',1)->where('validate',1)->where('deleted',0)->where('partenaire_id','<>',null)->whereBetween('created_at', [$debut, $fin])->get()->all();
        }


        $etat['depots'] = $depots;
        $etat['debut'] = $debut;
        $etat['fin'] = $fin;
        session()->push('depots',$etat);
        return view('rapport.resultats.depots',compact('depots'));
    }

    public function downloadRapportDepots(Request $request)
    {   
        $lastKey = array_key_last(session()->get('depots'));
        $depots = session()->get('depots')[$lastKey]['depots'];
        $debut = session()->get('depots')[$lastKey]['debut'];
        $fin = session()->get('depots')[$lastKey]['fin'];
        
        $pdf = PDF::loadView ('rapport.etats.depots',compact('depots','debut','fin'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download ('Rapport des depots du '. $debut .' au '. $fin .'.pdf');
    }

    public function rapportRetraits(Request $request)
    {   
        $partenaires = Partenaire::where('deleted',0)->get();
        return view('rapport.retraits',compact('partenaires'));
    }

    public function searchRetraits(Request $request)
    {   
        $debut =  $newDate = date("Y-m-d", strtotime(str_replace(' ','',explode('-',$request->periode)[0])));
        $debut = $debut.' 00:00:00';
        $fin =  $newDate = date("Y-m-d", strtotime(str_replace(' ','',explode('-',$request->periode)[1])));
        $fin = $fin.' 23:59:59';
        $partenaires = $request->partenaires;
        if(is_array($request->partenaires)){
            $retraits = Retrait::where('status',1)->where('validate',1)->where('deleted',0)->whereIn('partenaire_id',$partenaires)->whereBetween('created_at', [$debut, $fin])->get()->all();
        }else{
            $retraits = Retrait::where('status',1)->where('validate',1)->where('deleted',0)->whereBetween('created_at', [$debut, $fin])->get()->all();
        }

        $etat['retraits'] = $retraits;
        $etat['debut'] = $debut;
        $etat['fin'] = $fin;
        session()->push('retraits',$etat);
        return view('rapport.resultats.retraits',compact('retraits'));
    }

    public function downloadRapportRetraits(Request $request)
    {   
        $lastKey = array_key_last(session()->get('retraits'));
        $retraits = session()->get('retraits')[$lastKey]['retraits'];
        $debut = session()->get('retraits')[$lastKey]['debut'];
        $fin = session()->get('retraits')[$lastKey]['fin'];
        
        $pdf = PDF::loadView ('rapport.etats.retraits',compact('retraits','debut','fin'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download ('Rapport des retraits du '. $debut .' au '. $fin .'.pdf');
    }

    public function rapportTransferts(Request $request)
    {   
        $users = UserClient::where('deleted',0)->get();
        return view('rapport.transferts',compact('users'));
    }

    public function searchTransferts(Request $request)
    {   
        $debut =  $newDate = date("Y-m-d", strtotime(str_replace(' ','',explode('-',$request->periode)[0])));
        $debut = $debut.' 00:00:00';
        $fin =  $newDate = date("Y-m-d", strtotime(str_replace(' ','',explode('-',$request->periode)[1])));
        $fin = $fin.' 23:59:59';
        $clients = $request->clients;
        if(is_array($request->partenaires)){
            $transferts = Transfert::where('status',1)->where('deleted',0)->whereIn('user_client_id',$clients)->whereBetween('created_at', [$debut, $fin])->get()->all();
        }else{
            $transferts = Transfert::where('status',1)->where('deleted',0)->whereBetween('created_at', [$debut, $fin])->get()->all();
        }
        
        $etat['transferts'] = $transferts;
        $etat['debut'] = $debut;
        $etat['fin'] = $fin;
        session()->push('transferts',$etat);
        return view('rapport.resultats.transferts',compact('transferts'));
    }

    public function downloadRapportTransferts(Request $request)
    {   
        $lastKey = array_key_last(session()->get('transferts'));
        $transferts = session()->get('transferts')[$lastKey]['transferts'];
        $debut = session()->get('transferts')[$lastKey]['debut'];
        $fin = session()->get('transferts')[$lastKey]['fin'];
        
        $pdf = PDF::loadView ('rapport.etats.transferts',compact('transferts','debut','fin'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download ('Rapport des transferts du '. $debut .' au '. $fin .'.pdf');
    }
}
