<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use App\Models\CompteCommission;
use App\Models\CompteCommissionOperation;
use App\Models\CompteMouvement;
use App\Models\CompteMouvementOperation;
use Illuminate\Support\Carbon;

class CompteController extends Controller
{

    public function compteCommission(Request $request)
    {
        try{
            $compteCommissions = CompteCommission::where('deleted',0)->get();
            return view('compte.commission',compact('compteCommissions'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function compteCommissionAdd(Request $request)
    {
        try{
            CompteCommission::create([
                'id' => Uuid::uuid4()->toString(),
                'libelle' => $request->libelle,
                'solde' => 0,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return back()->withSuccess("Type de compte enregistré avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function compteCommissionEdit(Request $request)
    {   
        try{
            $compteCommission = CompteCommission::where('id',$request->id)->where('deleted',0)->first();
            $compteCommission->libelle = $request->libelle;
            $compteCommission->save();
            return back()->withSuccess("Modification effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function compteCommissionDelete(Request $request)
    {   
        try{
            $compteCommission = CompteCommission::where('id',$request->id)->where('deleted',0)->first();
            $compteCommission->deleted = 1;
            $compteCommission->save();
            return back()->withSuccess("Suppression effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function compteCommissionDetail(Request $request)
    {
        try{
            $compteCommission = CompteCommission::where('id',$request->id)->where('deleted',0)->first();
            $compteCommissionOperations = CompteCommissionOperation::where('compte_commission_id',$compteCommission->id)->get();
            return view('compte.detail_commission',compact('compteCommission','compteCommissionOperations'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function compteMouvement(Request $request)
    {
        try{
            $compteMvts = CompteMouvement::where('deleted',0)->get();
            return view('compte.mouvement',compact('compteMvts'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function compteMouvementAdd(Request $request)
    {
        try{
            CompteMouvement::create([
                'id' => Uuid::uuid4()->toString(),
                'libelle' => $request->libelle,
                'solde' => 0,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return back()->withSuccess("Type de compte enregistré avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function compteMouvementEdit(Request $request)
    {   
        try{
            $compteMvt = CompteMouvement::where('id',$request->id)->where('deleted',0)->first();
            $compteMvt->libelle = $request->libelle;
            $compteMvt->save();
            return back()->withSuccess("Modification effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function compteMouvementDelete(Request $request)
    {   
        try{
            $compteMvt = CompteMouvement::where('id',$request->id)->where('deleted',0)->first();
            $compteMvt->deleted = 1;
            $compteMvt->save();
            return back()->withSuccess("Suppression effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}