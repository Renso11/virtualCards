<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AppController extends Controller
{

    public function modulesClients(Request $request){
        try {
            $services = Service::where('deleted',0)->get();
            return view('appParams.clients.modules',compact('services'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        };
    }

    public function modulesClientsAdd(Request $request){
        try {
            Service::create([
                'id' => Uuid::uuid4()->toString(),
                'type' => $request->type,
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

    public function modulesClientsDelete(Request $request){
        try {
            $service = Service::where('id',$request->id)->where('deleted',0)->first();
            $service->deleted = 1;
            $service->save();
            return back()->withSuccess('Suppression effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function modulesClientsActivate(Request $request){
        try {
            $service = Service::where('id',$request->id)->where('deleted',0)->first();
            $service->status = 1;
            $service->save();
            return back()->withSuccess('Activation effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }

    public function modulesClientsDesactivate(Request $request){
        try {
            $service = Service::where('id',$request->id)->where('deleted',0)->first();
            $service->status = 0;
            $service->save();
            return back()->withSuccess('Desactivation effectuée avec success');

        } catch (\Exception $e) {
            return  back()->withError($e->getMessage());
        };
    }
}
