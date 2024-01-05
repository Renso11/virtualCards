<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Frai;
use App\Models\CompteCommission;
use App\Models\FraiCompteCommission;
use App\Models\Restriction;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Ramsey\Uuid\Uuid;

class ParametreController extends Controller
{  

    public function frais(Request $request){
        try {
            $frais = Frai::where('deleted',0)->get();
            $compteCommissions = CompteCommission::where('deleted',0)->get();

            return view('frais.index',compact('frais','compteCommissions'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        };
    }

    public function fraisAdd(Request $request){
        try {        
            $frais = Frai::create([
                'id' => Uuid::uuid4()->toString(),
                'type' => $request->type,
                'type_operation' => $request->type_operation,
                'start' => $request->debut,
                'end' => $request->fin,
                'value' => $request->value,
                'type_commission_partenaire' => $request->type_partenaire,
                'value_commission_partenaire' => $request->value_partenaire,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $compteCommissions = CompteCommission::where('deleted',0)->get();

            foreach($compteCommissions as $item){
                if(array_key_exists('id_'.strtolower($item->libelle),$request->all())){
                    FraiCompteCommission::create([
                        'id' => Uuid::uuid4()->toString(),
                        'frai_id' => $frais->id,
                        'compte_commission_id' => $item->id,
                        'type' => $request->all()['type_'.strtolower($item->libelle)],
                        'value' => $request->all()['value_'.strtolower($item->libelle)],
                        'deleted' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
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

    
    public function roles(Request $request){
        try {
            $roles = Role::where('deleted',0)->get();     
            $permissions = Permission::where('deleted',0)->get();      
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
    
    public function permissions(Request $request){
        try {
            $permissions = Permission::where('deleted',0)->get();    
            $free_routes = $permissions->pluck('route')->all();
            $routes = [];

            foreach (Route::getRoutes()->getRoutes() as $route) {
                $action = $route->getAction();
                if (array_key_exists('as', $action) && str_contains($action['as'], 'admin') && !in_array($action['as'], $free_routes)) {
                    $routes[] = $action['as'];
                }
            }  
            return view('permissions.index',compact('routes','permissions'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        };
    }

    public function permissionsAdd(Request $request){
        try {
            Permission::create([
                'id' => Uuid::uuid4()->toString(),
                'libelle' => $request->libelle,
                'route' => $request->route,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return back()->withSuccess('Permission enregistrée avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function permissionsEdit(Request $request){
        try {
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

    public function permissionsDelete(Request $request){
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
