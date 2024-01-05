<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\UserClient;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class ValidatorController extends Controller
{
    public function loginCompteValdiator(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return  sendError($validator->errors(), [],422);
            }

            if (!$token = Auth::guard('apiValidator')->attempt($validator->validated())) {
                return  sendError('Identifiants incorrectes', [],401);
            }
            
            $user = auth('apiValidator')->user();
            
            if($user->status == 0){
                return sendError('Compte inactif', [], 401);
            }


            $resultat['token'] = $this->createNewToken($token);
            
            $user->makeHidden(['password']);
            $resultat['user'] = $user;

            return sendResponse($resultat, 'Connexion réussie');
            
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function pendingCustomerAccount(Request $request){
        try {
            $userClients = UserClient::where('deleted',0)->where('verification_step_one',1)->where('verification_step_two',1)->where('verification_step_three',1)->where('verification',0)->orderBy('id','desc')->get();

            foreach($userClients as $userClient){
                $userClient->kycClient;
            }
            return sendResponse($userClients, 'Liste');
            
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function getMotifList(Request $request){
        try {
            $motifs = [[
                'niveau' => 2,
                'libelle' => 'Information incorrectes',
            ],[
                'niveau' => 3,
                'libelle' => 'Pieces ou photo non valide',
            ]];

            return sendResponse($motifs, 'Liste');
            
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function validatePendingCustomerAccount(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'customer_id' => 'required',
            ]);

            $userClient = UserClient::where('id',$request->customer_id)->where('deleted',0)->first();
            $user = User::where('id',$request->user_id)->where('deleted',0)->first();
            
            $userClient->status = 1;
            $userClient->verification = 1;
            $userClient->user_id = $user->id;
            $userClient->updated_at = Carbon::now();
            $userClient->save();

            $userClient->kycClient;
            
            return sendResponse($userClient, 'Connexion réussie');
            
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    public function rejectPendingCustomerAccount(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'customer_id' => 'required',
                'motif' => 'required',
                'description' => 'nullable',
            ]);

            $userClient = UserClient::where('id',$request->customer_id)->where('deleted',0)->first();

            $userClient->verification = 0;
            if($request->motif == 2){
                $motif = 'Information incorrectes';
                $userClient->verification_step_two = 0;
            }else if($request->motif == 3){
                $motif = 'Pieces ou photo non valide';
                $userClient->verification_step_three = 0;
            }

            $description = $request->description ? $request->description : '';

            $userClient->updated_at = Carbon::now();
            $userClient->save();

            /*if($userClient->kycClient->email){
                Mail::to([$userClient->kycClient->email,])->send(new AlerteRejet(['motif' => $motif,'description' => $description]));
            }*/
            return sendResponse($userClient, 'Rejeté avec success');
            
        } catch (\Exception $e) {
            return sendError($e->getMessage(), [], 500);
        };
    }

    private function sendResponse($data, $message){
    	$response = [
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }
    
    private function sendError($message, $data = [], $code = 404){
    	$response = [
            'success' => false,
            'errors' => $message,
        ];


        if(!empty($data)){
            $response['data'] = $data;
        }
        return response()->json($response, $code);
    }
    
    protected function createNewToken($token){
        return $token;
    }
}
