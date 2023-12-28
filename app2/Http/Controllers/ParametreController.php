<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gamme;
use App\Models\Frai;
use App\Models\Info;
use App\Models\Service;
use App\Models\Commission;
use App\Models\RolePartenaire;
use App\Models\RolePartenairePermission;
use App\Models\Restriction;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;

class ParametreController extends Controller
{


    public function generales(Request $request){
        try {
            $info_card = Info::where('deleted',0)->first();
            $services = Service::where('deleted',0)->get();
            return view('params.generales',compact('info_card','services'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        };
    }

    public function cardInfosUpdate(Request $request){
        try {
            $info_card = Info::where('deleted',0)->first();

            if(!$info_card){
                Info::create([
                    'id' => Uuid::uuid4()->toString(),
                    'card_max' => $request->nb_card,
                    'card_price' => $request->pu_card,
                    'deleted' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }else{
                $info_card->card_max = $request->nb_card;
                $info_card->card_price = $request->pu_card;
                $info_card->save();
            }
            return back()->withSuccess('Informations modifiées avec succes');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        };
    }


    public function gammes(Request $request)
    {
        try{
            $gammes = Gamme::where('deleted',0)->get();
            return view('gammes.index',compact('gammes'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function gammeAdd(Request $request)
    {
        try{
            Gamme::create([
                        'id' => Uuid::uuid4()->toString(),
                'libelle' => $request->libelle,
                'prix' => $request->prix,
                'type' => $request->type,
                'description' => $request->description,
                'status' => 1,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return back()->withSuccess("Gamme enregistrée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function gammeEdit(Request $request)
    {   
        try{
            $gamme = Gamme::where('id',$request->id)->where('deleted',0)->first();

            $gamme->libelle = $request->libelle;
            $gamme->prix = $request->prix;
            $gamme->type = $request->type;
            $gamme->description = $request->description;
            $gamme->updated_at = Carbon::now();
            $gamme->save();
            return back()->withSuccess("Modification effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function gammeDelete(Request $request)
    {   
        try{
            $gamme = Gamme::where('id',$request->id)->where('deleted',0)->first();
            $gamme->deleted = 1;
            $gamme->save();
            return back()->withSuccess("Suppression effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function gammeActivation(Request $request)
    {   
        try{
            $gamme = Gamme::where('id',$request->id)->where('deleted',0)->first();
            $gamme->status = 1;
            $gamme->save();
            return back()->withSuccess("Activation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function gammeDesactivation(Request $request)
    {   
        try{
            $gamme = Gamme::where('id',$request->id)->where('deleted',0)->first();
            $gamme->status = 0;
            $gamme->save();
            return back()->withSuccess("Desactivation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }


    

    public function frais(Request $request){
        try {
            $frais = Frai::where('deleted',0)->get();
            return view('frais.index',compact('frais'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        };
    }

    public function fraisAdd(Request $request){
        try {            
            Frai::create([
                'id' => Uuid::uuid4()->toString(),
                'type' => $request->type,
                'type_operation' => $request->type_operation,
                'start' => $request->debut,
                'end' => $request->fin,
                'value' => $request->value,
                'type_commission_elg' => $request->type_elg,
                'value_commission_elg' => $request->value_elg,
                'type_commission_bank' => $request->type_bank,
                'value_commission_bank' => $request->value_bank,
                'type_commission_partenaire' => $request->type_partenaire,
                'value_commission_partenaire' => $request->value_partenaire,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return back()->withSuccess('Frais et commissions enregistré avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function fraisEdit(Request $request){
        try {
            $request->validate([
                'type' => 'required',
                'type_operation' => 'required',
                'debut' => 'required',
                'fin' => 'required',
                'value' => 'required',
            ]);
            $req = $request->all();
            $frai = Frai::where('id',$request->id)->where('deleted',0)->first();
            $frai->type = $request->type;
            $frai->type_operation = $request->type_operation;
            $frai->start = $request->debut;
            $frai->end = $request->fin;
            $frai->value = $request->value;
            $frai->save();
            return back()->withSuccess('Modification effectué avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function fraisDelete(Request $request){
        try {
            $req = $request->all();
            $frai = Frai::where('id',$request->id)->where('deleted',0)->first();
            $frai->deleted = 1;
            $frai->save();
            return back()->withSuccess('Suppression effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }
    

    public function commissions(Request $request){
        try {
            $commissions = Commission::where('deleted',0)->get();
            return view('commissions.index',compact('commissions'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        };
    }

    public function commissionsAdd(Request $request){
        try {
            $request->validate([
                'type_operation' => 'required',
                'type' => 'required',
                'value' => 'required'
            ]);
            /*if($request->type == 'Taux fixe'){
                $frais = Frai::where('type_operation',$request->type_operation)->orderBy('id','DESC')->first();
                if($frais && $frais->type_operation)
            }*/
            Commission::create([
                        'id' => Uuid::uuid4()->toString(),
                'type' => $request->type,
                'type_operation' => $request->type_operation,
                'value' => $request->value,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return back()->withSuccess('Commissions enregistré avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function commissionsEdit(Request $request){
        try {
            $request->validate([
                'type' => 'required',
                'type_operation' => 'required',
                'value' => 'required',
            ]);
            $req = $request->all();
            $frai = Commission::where('id',$request->id)->where('deleted',0)->first();
            $frai->type = $request->type;
            $frai->type_operation = $request->type_operation;
            $frai->value = $request->value;
            $frai->save();
            return back()->withSuccess('Modification effectué avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function commissionsDelete(Request $request){
        try {
            $req = $request->all();
            $frai = Commission::where('id',$request->id)->where('deleted',0)->first();
            $frai->deleted = 1;
            $frai->save();
            return back()->withSuccess('Suppression effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }
    
    public function roles(Request $request){
        try {
            $roles = Role::where('deleted',0)->get();     
            $permissions = Permission::where('deleted',0)->where('type','admin')->get();                   
            return view('roles.index',compact('roles','permissions'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        };
    }

    public function rolesAdd(Request $request){
        try {
            //dd($request);
            $role = Role::create([
                        'id' => Uuid::uuid4()->toString(),
                'libelle' => $request->libelle,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            $permissions = json_decode($request->permissions);
            foreach ($permissions as $value) {
                RolePermission::create([
                        'id' => Uuid::uuid4()->toString(),
                    'role_id' => $role->id,
                    'permission_id' => $value,
                    'deleted' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            return back()->withSuccess('Role enregistré avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function rolesEdit(Request $request){
        try {
            //dd($request);
            $role = Role::where('id',$request->id)->where('deleted',0)->first(); 
            $role->libelle = $request->libelle;
            $role->save();

            $oldPermissions = $role->rolePermissions->pluck('permission_id')->all();
            $newPermissions = json_decode($request->permissions);

            $differences = array_diff($oldPermissions,$newPermissions);

            foreach($differences as $item){
                $existe = RolePermission::where('permission_id',$item)->where('role_id',$request->id)->first();
                $existe->deleted = 1;
                $existe->save();
            }

            foreach($newPermissions as $item){
                $existe = RolePermission::where('permission_id',$item)->where('role_id',$request->id)->first();
                if(!$existe){
                    RolePermission::create([
                        'id' => Uuid::uuid4()->toString(),
                        'role_id' => $request->id,
                        'permission_id' => $item,
                        'deleted' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }else{
                    $existe->deleted = 0;
                    $existe->save();
                }
            }
            return back()->withSuccess('Modification effectué avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function rolesDelete(Request $request){
        try {
            $role = $request->all();
            $role = Role::where('id',$request->id)->where('deleted',0)->first();
            $role->deleted = 1;

            $permissions = RolePermission::where('role_id',$request->id)->get();
            foreach ($permissions as $value) {
                $value->deleted = 1;
                $value->save();
            }
            
            $role->save();
            return back()->withSuccess('Suppression effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function restrictions(Request $request){
        try {
            $restrictions = Restriction::where('deleted',0)->get();
            return view('restrictions.index',compact('restrictions'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        };
    }

    public function restrictionsAdd(Request $request){
        try {
            Restriction::create([
                        'id' => Uuid::uuid4()->toString(),
                'type_operation' => $request->type_operation,
                'type_restriction' => $request->type_restriction,
                'type_acteur' => $request->type_acteur,
                'valeur' => $request->valeur,
                'periode' => $request->periode,
                'etat' => 1,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return back()->withSuccess('Restriction ajouté avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        }
    }

    public function restrictionsEdit(Request $request){
        try {
            $restriction = Restriction::where('id',$request->id)->where('deleted',0)->first();
            $restriction->type_operation = $request->type_operation;
            $restriction->type_restriction = $request->type_restriction;
            $restriction->type_acteur = $request->type_acteur;
            $restriction->valeur = $request->valeur;
            $restriction->periode = $request->periode;
            $restriction->save();
            return back()->withSuccess('Modification effectué avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function restrictionsDelete(Request $request){
        try {
            $restriction = Restriction::where('id',$request->id)->where('deleted',0)->first();
            $restriction->deleted = 1;
            $restriction->save();
            return back()->withSuccess('Suppression effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function restrictionsActivate(Request $request){
        try {
            $restriction = Restriction::where('id',$request->id)->where('deleted',0)->first();
            $restriction->etat = 1;
            $restriction->save();
            return back()->withSuccess('Suppression effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function restrictionsDesactivate(Request $request){
        try {
            $restriction = Restriction::where('id',$request->id)->where('deleted',0)->first();
            $restriction->etat = 0;
            $restriction->save();
            return back()->withSuccess('Suppression effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }
}
