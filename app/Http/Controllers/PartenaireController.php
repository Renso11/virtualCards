<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partenaire;
use App\Models\AccountCommission;
use App\Models\AccountDistribution;
use App\Models\AccountVente;
use App\Models\AccountCommissionOperation;
use App\Models\AccountDistributionOperation;
use App\Models\AccountVenteOperation;
use App\Models\UserPartenaire;
use App\Models\RechargementPartenaire;
use App\Models\VentePartenaire;
use Illuminate\Support\Carbon;
use App\Models\Depot;
use App\Models\GtpRequest;
use GuzzleHttp\Client;
use App\Models\RolePartenaire;
use App\Models\RolePermission;
use App\Models\Permission;
use App\Models\RolePartenairePermission;
use App\Models\Retrait;
use Illuminate\Support\Facades\Hash;
use App\Mail\MailAlerte;
use App\Models\ApiPartenaireAccount;
use App\Models\ApiPartenaireFee;
use App\Models\ApiPartenaireTransaction;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Auth as Auth;
use Ramsey\Uuid\Uuid;

class PartenaireController extends Controller
{
    public function partenaires(Request $request)
    {
        try{
            $partenaires = Partenaire::where('deleted',0)->get();
            return view('partenaires.index',compact('partenaires'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireAdd(Request $request)
    {
        //try{
            if($request->rccm){
                $rccm = Uuid::uuid4()->toString().'.'.$request->rccm->getClientOriginalExtension();
                $request->rccm->move('storage/partenaire/rccm/', $rccm);
                $url_rccm = 'storage/partenaire/rccm/'.$rccm;
            }
            if($request->ifu){
                $ifu = Uuid::uuid4()->toString().'.'.$request->ifu->getClientOriginalExtension();
                $request->ifu->move('storage/partenaire/ifu/', $ifu);
                $url_ifu = 'storage/partenaire/ifu/'.$ifu;
            }
            
            $partenaire = Partenaire::create([
                'id' => Uuid::uuid4()->toString(),
                'type_partenaire' => $request->type_partenaire,
                'libelle' => $request->libelle,
                'code' => $request->code,
                'last' => $request->last,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'rccm' => $url_rccm,
                'ifu' => $url_ifu,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            //dd($partenaire);
            AccountCommission::create([
                'id' => Uuid::uuid4()->toString(),
                'solde' => 0,
                'partenaire_id' => $partenaire->id,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            AccountDistribution::create([
                    'id' => Uuid::uuid4()->toString(),
                'solde' => 0,
                'partenaire_id' => $partenaire->id,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            AccountVente::create([
                'id' => Uuid::uuid4()->toString(),
                'solde' => 0,
                'partenaire_id' => $partenaire->id,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);     

            $role = RolePartenaire::create([
                        'id' => Uuid::uuid4()->toString(),
                'libelle' => 'Administrateur',
                'partenaire_id' => $partenaire->id,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $permissions = Permission::where('deleted',0)->where('type','partenaire')->get();
            
            foreach ($permissions as $key => $value) {
                RolePartenairePermission::create([
                        'id' => Uuid::uuid4()->toString(),
                    'role_partenaire_id' => $role->id,
                    'permission_id' => $value->id,
                    'deleted' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }     

            UserPartenaire::create([
                'id' => Uuid::uuid4()->toString(),
                'name' => $request->name,
                'lastname' => $request->lastname,
                'role_partenaire_id' => $role->id,
                'partenaire_id' => $partenaire->id,
                'username' => strtolower(unaccent($request->name)[0].''.explode(' ',unaccent($request->lastname))[0]),
                'password' => Hash::make(12345678),
                'status' => 1,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            $message = 'Vos informations de connexion bcb virtuelle sont: Username : '. strtolower(unaccent($request->lastname)[0].''.explode(' ',unaccent($request->lastname))[0]).  ', Mot de passe: 12345678. Vous pouvez changer votre mot de passe pour plus de sécurité.';
            $arr = ['messages'=> $message,'objet'=>'Informations de connexion partenaire','from'=>'noreply-bcv@bestcash.me'];
            Mail::to([$request->email,])->send(new MailAlerte($arr));

            return back()->withSuccess("Partenaire enregistré avec succès");
        /*} catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }*/
    }

    public function partenaireEdit(Request $request)
    {   
        try{
            $partenaire = Partenaire::where('id',$request->id)->where('deleted',0)->first();

            $partenaire->libelle = $request->libelle;
            $partenaire->code = $request->code;
            $partenaire->last = $request->last;
            $partenaire->email = $request->email;
            $partenaire->telephone = $request->telephone;

            if($request->rccm){
                $rccm = time().'.'.$request->rccm->getClientOriginalExtension();
                $request->rccm->move('storage/partenaire/rccm/', $rccm);
                $url_rccm = 'storage/partenaire/rccm/'.$rccm;
                $partenaire->rccm = $url_rccm;
            }
            if($request->ifu){
                $ifu = time().'.'.$request->ifu->getClientOriginalExtension();
                $request->ifu->move('storage/partenaire/ifu/', $ifu);
                $url_ifu = 'storage/partenaire/ifu/'.$ifu;
                $partenaire->ifu = $url_ifu;
            }
            
            $partenaire->updated_at = Carbon::now();
            $partenaire->save();
            return back()->withSuccess("Modification effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireRecharge(Request $request)
    {   
        try{
            
            $balance = 0;

            $base_url = env('BASE_GTP_API');
            $programID = env('PROGRAM_ID');
            $authLogin = env('AUTH_LOGIN');
            $authPass = env('AUTH_PASS');
            try {
                $requestId = GtpRequest::create([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $client = new Client();
                $url = $base_url."accounts/17225124/balance";
        
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
        
                $balance = json_decode($response->getBody())->balance/*1000000*/;
            } catch (BadResponseException $e) {
                $json = json_decode($e->getResponse()->getBody()->getContents());
                $error = $json->title.'.'.$json->detail;
                return back()->withError($error);
            } 
            $sommeDistribution = AccountDistribution::where('deleted',0)->sum('solde');

            if(($balance - $sommeDistribution) <= $request->montant){
                return back()->withError('Le solde GTP ne permet pas de faire se rechargement');
            }

            $partenaire = Partenaire::where('id',$request->id)->where('deleted',0)->first();
            

            RechargementPartenaire::create([
                'id' => Uuid::uuid4()->toString(),
                'partenaire_id' => $request->id,
                'montant' => $request->montant,
                'user_id' => Auth::user()->id,
                'deleted' => 0,
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return back()->withSuccess("Rechargement initié avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireRechargeAttentes(Request $request)
    { 
        try {
            $recharges = RechargementPartenaire::where('deleted',0)->where('status',0)->orderBy('id','desc')->get();
            return view('partenaires.rechargeAttentes',compact('recharges'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        } 
    }
    
    public function partenaireValideRecharge(Request $request)
    {  
        try{
            $rechargement = RechargementPartenaire::where('id',$request->id)->where('deleted',0)->first();
            $partenaire = $rechargement->partenaire;
            
            if(!$partenaire->accountDistribution){
                AccountDistribution::create([
                    'id' => Uuid::uuid4()->toString(),
                    'solde' => 0,
                    'partenaire_id' => $partenaire->id,
                    'deleted' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]); 
            }

            AccountDistributionOperation::create([
                'id' => Uuid::uuid4()->toString(),
                'solde_avant' => $partenaire->accountDistribution->solde,
                'montant' => $rechargement->montant,
                'solde_apres' => $partenaire->accountDistribution->solde + $rechargement->montant,
                'libelle' => 'Rechargement du compte de distribution',
                'type' => 'credit',
                'deleted' => 0,
                'rechargement_partenaire_id' =>  $rechargement->id,
                'account_distribution_id' =>  $partenaire->accountDistribution->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $partenaire->accountDistribution->solde += $rechargement->montant;
            $partenaire->accountDistribution->save();
            $rechargement->status = 1;
            $rechargement->save();
            return back()->withSuccess("Rechargement effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
    
    public function partenaireVente(Request $request)
    {   
        try{          
            VentePartenaire::create([
                        'id' => Uuid::uuid4()->toString(),
                'partenaire_id' => $request->id,
                'montant' => $request->montant,
                'nombre' => $request->nombre,
                'user_id' => Auth::user()->id,
                'deleted' => 0,
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return back()->withSuccess("Vente initiée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
    
    public function partenaireVenteAttentes(Request $request)
    { 
        try {
            $ventes = VentePartenaire::where('deleted',0)->where('status',0)->orderBy('id','desc')->get();
            return view('partenaires.venteAttentes',compact('ventes'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        } 
    }
    
    public function partenaireValideVente(Request $request)
    {  
        try{
            $vente = VentePartenaire::where('id',$request->id)->where('deleted',0)->first();
            $partenaire = $vente->partenaire;  

            if(!$partenaire->accountVente){
                AccountVente::create([
                        'id' => Uuid::uuid4()->toString(),
                    'solde' => 0,
                    'partenaire_id' => $partenaire->id,
                    'deleted' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]); 
            }          

            AccountVenteOperation::create([
                        'id' => Uuid::uuid4()->toString(),
                'solde_avant' => $partenaire->accountVente->solde,
                'nombre' => $vente->nombre,
                'solde_apres' => $partenaire->accountVente->solde + $vente->nombre,
                'deleted' => 0,
                'libelle' => 'Achat de carte',
                'user_id' => Auth::user()->id,
                'account_vente_id' =>  $partenaire->accountVente->id,
                'vente_partenaire_id' =>  $vente->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $partenaire->accountVente->solde += $vente->nombre;
            $partenaire->accountVente->save();
            $vente->status = 1;
            $vente->save();
            return back()->withSuccess("Vente effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }



    public function partenaireDelete(Request $request)
    {
        try{
            $partenaire = Partenaire::where('id',$request->id)->where('deleted',0)->first();

            $partenaire->deleted = 1;
            $partenaire->updated_at = Carbon::now();
            $partenaire->save();
            return back()->withSuccess("Suppression effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireDetails(Request $request)
    {
        try{
            $partenaire = Partenaire::where('id',$request->id)->first();
            $roles = RolePartenaire::where('deleted',0)->where('partenaire_id',$partenaire->id)->get();
            $depots = Depot::where('partenaire_id',$request->id)->where('deleted',0)->get();
            $retraits = Retrait::where('partenaire_id',$request->id)->where('deleted',0)->get();
            $users = UserPartenaire::where('partenaire_id',$request->id)->where('deleted',0)->get();
            return view('partenaires.detail',compact('partenaire','depots','retraits','users','roles'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireCancelRetrait(Request $request)
    {
        try{
            $req = $request->all();

            $retrait = Retrait::where('id',$request['id'])->first();
            $retrait->status = null;
            $retrait->motif_rejet = $request['motif'];
            $retrait->save();
            return back()->withSuccess("Retrait annuler avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
    
    public function partenaireUserEdit(Request $request)
    {   
        try{
            $user = UserPartenaire::where('id',$request->id)->where('deleted',0)->first();

            $user->name = $request->name;
            $user->lastname = $request->lastname;
            $user->role_partenaire_id = $request->role;
            $user->updated_at = Carbon::now();
            $user->save();
            return back()->withSuccess("Modification effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireUserDelete(Request $request)
    {
        try{
            $user = UserPartenaire::where('id',$request->id)->where('deleted',0)->first();
            $user->deleted = 1;
            $user->updated_at = Carbon::now();
            $user->save();
            return back()->withSuccess("Supression effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireUserResetPassword(Request $request)
    {
        try{
            $userClient = UserPartenaire::where('id',$request->id)->where('deleted',0)->first();

            $userClient->password = Hash::make(12345678);
            $userClient->updated_at = Carbon::now();
            $userClient->save();
            return back()->withSuccess("Reinitialisation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireUserActivation(Request $request)
    {
        try{
            $user = UserPartenaire::where('id',$request->id)->where('deleted',0)->first();

            $user->status = 1;
            $user->updated_at = Carbon::now();
            $user->save();
            return back()->withSuccess("Activation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireUserDesactivation(Request $request)
    {
        try{
            $user = UserPartenaire::where('id',$request->id)->where('deleted',0)->first();

            $user->status = 0;
            $user->updated_at = Carbon::now();
            $user->save();
            return back()->withSuccess("Desactivation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireCompte(Request $request)
    {
        try{
            $partenaire = Partenaire::where('id',$request->id)->first();

            $compteCommission = $partenaire->accountCommission;
            $operationsCompteCommission = AccountCommissionOperation::where('deleted',0)->where('account_commission_id',$partenaire->accountCommission->id)->orderBy('id','desc')->get()->all(); 
            
            $compteDistribution = $partenaire->accountDistribution;
            $operationsCompteDistribution = AccountDistributionOperation::where('deleted',0)->where('account_distribution_id',$partenaire->accountDistribution->id)->orderBy('id','desc')->get()->all();   
            
            return view('partenaires.compte',compact('compteCommission','operationsCompteCommission','compteDistribution','operationsCompteDistribution'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }





    
    public function partenairesApi(Request $request)
    {
        try{
            $partenaires = ApiPartenaireAccount::where('deleted',0)->get();
            return view('partenairesApi.index',compact('partenaires'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireApiAdd(Request $request)
    {
        try{            
            $apiKey = bin2hex(random_bytes(24));
            $secretApiKey = 'sk_'.bin2hex(random_bytes(32));
            $privateApiKey = 'pk_'.bin2hex(random_bytes(32));

            ApiPartenaireAccount::create([
                        'id' => Uuid::uuid4()->toString(),
                'libelle' => $request->libelle,
                'name' => $request->name,
                'lastname' => $request->lastname,
                'address' => $request->address,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'balance' => 0,
                'api_key' => $apiKey,
                'secret_api_key' => $secretApiKey,
                'public_api_key' => $privateApiKey,
                'status' => 1,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return back()->withSuccess("Partenaire enregistré avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireApiEdit(Request $request)
    {   
        try{
            $partenaire = Partenaire::where('id',$request->id)->where('deleted',0)->first();

            $partenaire->libelle = $request->libelle;
            $partenaire->code = $request->code;
            $partenaire->last = $request->last;
            $partenaire->email = $request->email;
            $partenaire->telephone = $request->telephone;

            if($request->rccm){
                $rccm = time().'.'.$request->rccm->getClientOriginalExtension();
                $request->rccm->move('storage/partenaire/rccm/', $rccm);
                $url_rccm = 'storage/partenaire/rccm/'.$rccm;
                $partenaire->rccm = $url_rccm;
            }
            if($request->ifu){
                $ifu = time().'.'.$request->ifu->getClientOriginalExtension();
                $request->ifu->move('storage/partenaire/ifu/', $ifu);
                $url_ifu = 'storage/partenaire/ifu/'.$ifu;
                $partenaire->ifu = $url_ifu;
            }
            
            $partenaire->updated_at = Carbon::now();
            $partenaire->save();
            return back()->withSuccess("Modification effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireApiRecharge(Request $request)
    {   
        try{            
            ApiPartenaireTransaction::create([
                'id' => Uuid::uuid4()->toString(),
                'api_partenaire_account_id' => $request->id,
                'type' => 'Appro',
                'reference' => 'APP-'.time(),
                'montant' => $request->montant,
                'frais' => 0,
                'commission' => 0,
                'libelle' => 'Approvisionnement du compte',
                'user_id' => Auth::user()->id,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return back()->withSuccess("Approvisionnement initié avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }


    public function partenaireApiRechargeAttente(Request $request){
        try {
            $appros = ApiPartenaireTransaction::where('type','Appro')->where('deleted',0)->where('status',null)->get();
            return view('partenairesApi.approAttentes',compact('appros'));

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function partenaireApiRechargeValidate(Request $request)
    {   
        try{     
            $appro = ApiPartenaireTransaction::where('id',$request->id)->first();

            $partenaire = ApiPartenaireAccount::where('id',$appro->apiPartenaireAccount->id)->first();
            $soldeAvant = $partenaire->balance;
            $partenaire->balance += $appro->montant;
            $partenaire->save();

            $appro->solde_avant = $soldeAvant;
            $appro->solde_apres = $partenaire->balance;
            $appro->status = 1;
            $appro->validate_id = Auth::user()->id;
            $appro->validate_time = Carbon::now();
            $appro->save();
            return back()->withSuccess("Approvisionnement validé avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireApiRechargeUnvalidate(Request $request)
    {   
        try{     
            $appro = ApiPartenaireTransaction::where('id',$request->id)->first();
            $appro->status = 0;
            $appro->comment = $request->comment;
            $appro->validate_id = Auth::user()->id;
            $appro->validate_time = Carbon::now();
            $appro->save();
            return back()->withSuccess("Approvisionnement rejetté avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }


    public function partenairesApiFee(Request $request)
    {   
        try{
            $fees = ApiPartenaireFee::where('deleted',0)->get();
            $partenaires = ApiPartenaireAccount::where('deleted',0)->get();
            return view('partenairesApi.fee',compact('fees','partenaires'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function partenaireApiFeeAdd(Request $request)
    {
        try{
            ApiPartenaireFee::create([
                        'id' => Uuid::uuid4()->toString(),
                'api_partenaire_account_id' => $request->partenaire,
                'type_fee' => $request->type_fee,
                'beguin' => $request->beguin,
                'end' => $request->end,
                'value' => $request->value,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return back()->withSuccess("Frais enregistré avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }    

    public function partenaireApiFeeEdit(Request $request){
        try {
            $fee = ApiPartenaireFee::where('id',$request->id)->where('deleted',0)->first();
            $fee->api_partenaire_account_id = $request->partenaire;
            $fee->type_fee = $request->type_fee;
            $fee->beguin = $request->beguin;
            $fee->end = $request->end;
            $fee->value = $request->value;
            $fee->save();
            return back()->withSuccess('Modification effectué avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function partenaireApiFeeDelete(Request $request){
        try {
            $fee = ApiPartenaireFee::where('id',$request->id)->where('deleted',0)->first();
            $fee->deleted = 1;
            $fee->save();
            return back()->withSuccess('Suppression effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }


    public function partenairesApiTransactions(Request $request){
        try {
            $transactions = ApiPartenaireTransaction::where('deleted',0)->get();
            $partners = ApiPartenaireAccount::where('deleted',0)->get();
            return view('partenairesApi.transactions',compact('transactions','partners'));

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function partenairesApiFilterTransactions(Request $request){
        try {
            $transactions = ApiPartenaireTransaction::where('deleted',0);

            if($request->partner != 'all'){
                $transactions->where('api_partenaire_account_id',$request->partner);
            }

            if($request->type != 'all'){
                $transactions->where('type',$request->type);
            }

            if($request->status != 'all'){
                $request->status == 'null' ? $status = null : $status = (int)$request->status;
                $transactions->where('status',$status);
            }

            if($request->date != null){
                $debut = date($request->date.' 00:00:00');
                $fin = date($request->date.' 23:59:59');
                $transactions->whereBetween('created_at',[$debut, $fin]);
            }
            $transactions = $transactions->get();
            return view('partenairesApi.filterTransactions',compact('transactions'));

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }
}
