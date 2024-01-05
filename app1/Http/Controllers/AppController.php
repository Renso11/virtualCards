<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Ramsey\Uuid\Uuid;
use App\Models\Info;
use App\Models\kkiapayRecharge;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AppController extends Controller
{
    public function appClient(Request $request){
        try {
            $info_card = Info::where('deleted',0)->first();
            $services = Service::where('deleted',0)->where('type','client')->get();
            return view('app.client',compact('info_card','services'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        };
    }

    public function serviceClientAdd(Request $request){
        try {
            Service::create([
                'id' => Uuid::uuid4()->toString(),
                'type' => 'client',
                'slug' => Str::slug($request->libelle , "-"),
                'status' => 1,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return back()->withSuccess('Module ajouté avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        }
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


    public function appPartenaire(Request $request){
        try {
            $services = Service::where('deleted',0)->where('type','partenaire')->get();
            return view('app.partenaire',compact('services'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        };
    }
    
    public function servicePartenaireAdd(Request $request){
        try {
            Service::create([
                'id' => Uuid::uuid4()->toString(),
                'type' => 'partenaire',
                'slug' => Str::slug($request->libelle , "-"),
                'status' => 1,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return back()->withSuccess('Module ajouté avec success');
        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        }
    }


    public function serviceDelete(Request $request){
        try {
            $service = Service::where('id',$request->id)->where('deleted',0)->first();
            $service->deleted = 1;
            $service->save();
            return back()->withSuccess('Suppression effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function serviceActivate(Request $request){
        try {
            $service = Service::where('id',$request->id)->where('deleted',0)->first();
            $service->status = 1;
            $service->save();
            return back()->withSuccess('Activation effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function serviceDesactivate(Request $request){
        try {
            $service = Service::where('id',$request->id)->where('deleted',0)->first();
            $service->status = 0;
            $service->save();
            return back()->withSuccess('Desactivation effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function rechargeKkp(Request $request){
        try {
            kkiapayRecharge::create([
                'id' => Uuid::uuid4()->toString(),
                'montant' => $request->montant,
                'reference' => $request->reference,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return back()->withSuccess('Rechargement effectué avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }
}
