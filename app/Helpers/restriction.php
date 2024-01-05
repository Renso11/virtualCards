<?php
use App\Models\Permission;
use App\Models\Restriction;
use App\Models\Depot;
use App\Models\Frai;
use App\Models\Retrait;
use App\Models\RestrictionAgence;
use App\Models\TransfertOut;
use App\Models\UserClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth as Auth;

function hasPermission($permission){
    if(Auth::user()->role == null){
        return false;
    }
    $permissionIds = Auth::user()->role->rolePermissions->pluck('permission_id')->all();
    $permissions = Permission::where('deleted',0)->whereIn('id',$permissionIds)->pluck('route')->all();
    return in_array($permission,$permissions);
}

function isRestrictByAdmin($montant,$clientId,$partenaireId = null,$typeOperation){
    $restrictions = Restriction::where('type_operation', $typeOperation)->where('etat', 1)->where('deleted', 0)->get();
    
    if(count($restrictions) !== 0){
        
        foreach ($restrictions as $item) {
            $libellePeriode = '.';
            if($item->periode == 'day'){
                $debut = date('Y-m-d 00:00:00');
                $fin = date('Y-m-d 23:59:59');
                $libellePeriode = 'de la journee.'; 
            }else if($item->periode == 'week'){
                $debut = date ('Y-m-d 00:00:00' , strtotime ("monday this week"));
                $fin = date('Y-m-d 23:59:59');
                $libellePeriode = 'de la semaine.';             
            }else if($item->periode == 'month'){
                $debut = date('Y-m-01 00:00:00');
                $fin = date('Y-m-t 23:59:59');
                $libellePeriode = 'du mois.'; 
            }
            
            if($typeOperation == 'depot'){
                if($item->type_acteur == 'client'){
                    $operation = Depot::where('user_client_id',$clientId)->where('deleted',0)->where('status',1)->where('validate',1);
                }else{
                    $operation = Depot::where('partenaire_id',$partenaireId)->where('deleted',0)->where('status',1)->where('validate',1);
                }
            }else if($typeOperation == 'retrait'){
                if($item->type_acteur == 'client'){
                    $operation = Retrait::where('user_client_id',$clientId)->where('deleted',0)->where('status',1)->where('validate',1);
                }else{
                    $operation = Retrait::where('partenaire_id',$partenaireId)->where('deleted',0)->where('status',1)->where('validate',1);
                }
            }else{
                $operation = TransfertOut::where('user_client_id',$clientId)->where('deleted',0)->where('status',1);
            }

            if($item->type_restriction == 'nombre'){
                if($item->periode == 'definitif'){
                    $value = count($operation->get()->all());
                }else{
                    $value = count($operation->whereBetween('created_at', [$debut, $fin])->get()->all());
                }
                if(($value + 1) > $item->valeur){   
                    if($typeOperation == 'transfert'){
                        return 'Vous avez atteint votre nombre de transfert '.$libellePeriode;
                    }else{             
                        if($item->type_acteur == 'client'){
                            return 'Le client a atteint son nombre de '. $typeOperation. ' ' .$libellePeriode;
                        }else{
                            return 'Vous avez atteint votre nombre de '. $typeOperation. ' ' .$libellePeriode;
                        }
                    }    
                }
            }else{
                if($item->periode == 'definitif'){
                    $value = $operation->sum('montant');
                }else{
                    $value = $operation->whereBetween('created_at', [$debut, $fin])->sum('montant');
                }
                if(($value + $montant) > $item->valeur){
                    if($typeOperation == 'transfert'){
                        return 'Vous avez atteint votre montant de transfert '.$libellePeriode;
                    }else{      
                        if($item->type_acteur == 'client'){
                            return 'Le client a atteint son montant de '. $typeOperation. ' ' .$libellePeriode;
                        }else{
                            return 'Vous avez atteint votre montant de '. $typeOperation. ' ' .$libellePeriode;
                        }
                    }   
                }
            }
        }
    }
    return 'ok';
}

function isRestrictByPartenaire($montant,$partenaireId,$userPartenaireId,$typeOperation){
    $restrictions = RestrictionAgence::where('type_operation', $typeOperation)->where(function ($query) use ($userPartenaireId) {
        $query->where('user_partenaire_id',$userPartenaireId)->orWhere('user_partenaire_id',0);
    })->where('etat', 1)->where('deleted', 0)->get();
    
    if(count($restrictions) !== 0){        
        foreach ($restrictions as $item) {
            $libellePeriode = '.';
            if($item->periode == 'day'){
                $debut = date('Y-m-d 00:00:00');
                $fin = date('Y-m-d 23:59:59');
                $libellePeriode = 'de la journee.'; 
            }else if($item->periode == 'week'){
                $debut = date ('Y-m-d 00:00:00' , strtotime ("monday this week"));
                $fin = date('Y-m-d 23:59:59');
                $libellePeriode = 'de la semaine.';             
            }else if($item->periode == 'month'){
                $debut = date('Y-m-01 00:00:00');
                $fin = date('Y-m-t 23:59:59');
                $libellePeriode = 'du mois.'; 
            }

            if($typeOperation == 'depot'){
                $operation = Depot::where('partenaire_id',$partenaireId)->where('user_partenaire_id',$userPartenaireId)->where('deleted',0)->where('status',1)->where('validate',1);
            }else{
                $operation = Retrait::where('partenaire_id',$partenaireId)->where('user_partenaire_id',$userPartenaireId)->where('deleted',0)->where('status',1)->where('validate',1);
            }

            if($item->type_restriction == 'nombre'){
                if($item->periode == 'definitif'){
                    $value = Count($operation->get()->all());
                }else{
                    $value = Count($operation->whereBetween('created_at', [$debut, $fin])->get()->all());
                }
                if(($value + 1) > $item->valeur){
                    return 'Vous avez atteint votre nombre de '. $typeOperation. ' ' .$libellePeriode;
                }
            }else{
                if($item->periode == 'definitif'){
                    $value = $operation->sum('montant');
                }else{
                    $value = $operation->whereBetween('created_at', [$debut, $fin])->sum('montant');
                }
                if(($value + $montant) > $item->valeur){
                    return 'Vous avez atteint votre montant de '. $typeOperation. ' ' .$libellePeriode;
                }
            }
        }
    }
    return 'ok';
}

