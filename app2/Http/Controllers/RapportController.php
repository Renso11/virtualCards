<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Depot;
use App\Models\Retrait;
use App\Models\UserClient;
use App\Models\Partenaire;
use App\Models\Transfert;
use Illuminate\Support\Carbon;
use PDF;

class RapportController extends Controller
{
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
