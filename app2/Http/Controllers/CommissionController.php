<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EntityAccountCommission;
use App\Models\EntityAccountCommissionOperation;

class CommissionController extends Controller
{

    public function commissionsElg(Request $request)
    {
        try{
            $compteElg = EntityAccountCommission::where('libelle','ELG')->where('deleted',0)->first();
            $compteElgOperations = EntityAccountCommissionOperation::where('entity_account_commission_id',$compteElg->id)->where('deleted',0)->get();
            return view('commissions.elg',compact('compteElg','compteElgOperations'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function commissionsUba(Request $request)
    {
        try{
            $compteUba = EntityAccountCommission::where('libelle','UBA')->where('deleted',0)->first();
            $compteUbaOperations = EntityAccountCommissionOperation::where('entity_account_commission_id',$compteUba->id)->where('deleted',0)->get();
            return view('commissions.uba',compact('compteUba','compteUbaOperations'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function commissionsPartenaire(Request $request)
    {
        try{
            $userClients = UserClient::where('deleted',0)->where('verification',1)->orderBy('id','desc')->get();
            $countries = $this->countries;
            return view('clients.index',compact('userClients','countries'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
