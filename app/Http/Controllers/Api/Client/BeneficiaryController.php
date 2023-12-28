<?php
    namespace App\Http\Controllers\Api\Client;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use App\Models\UserClient;
    use Illuminate\Support\Carbon;
    use App\Models\Beneficiaire;
    use App\Models\BeneficiaireBcv;
    use App\Models\BeneficiaireCard;
    use App\Models\BeneficiaireMomo;
    use Ramsey\Uuid\Uuid;

class BeneficiaryController extends Controller
{

    public function getBeneficiaries(Request $request){
        try {
            $beneficiaries = Beneficiaire::where('user_client_id',$request->user_id)->where('deleted',0)->get();

            foreach($beneficiaries as $beneficiary){
                $beneficiary->bcvBeneficiaries;
                $beneficiary->cardBeneficiaries;
                $beneficiary->momoBeneficiaries;
            }

            return $this->sendResponse(collect($beneficiaries), 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addBeneficiary(Request $request){
        try {
            $beneficiary = Beneficiaire::where('user_client_id',$request->user_id)->where('name',$request->name)->where('deleted',0)->first();

            if($beneficiary){
                return $this->sendError('Vous avez déjà un contact avec ce nom', [], 401);
            }
            
            $contacts = $request->data;
            foreach($contacts as $contact){
                if($contact['type'] != 'momo' && $contact['type'] != 'bmo' && $contact['type'] != 'bcv' && $contact['type'] != 'visa'){
                    return $this->sendError('Type de contact '.$contact['type'].' inconnu', [], 401);
                }
            }

            $beneficiary = Beneficiaire::create([
                'id' => Uuid::uuid4()->toString(),
                "user_client_id" => $request->user_id,
                "name" => $request->name,
                "deleted" => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);


            foreach($contacts as $contact){

                if($contact['type'] == 'momo' || $contact['type'] == 'bmo'){
                    BeneficiaireMomo::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "type" => $contact['type'],
                        "code" => $contact['code'],
                        "telephone" => $contact['telephone'],
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }else if($contact['type'] == 'bcv'){
                    $client = UserClient::where('username',$contact['username'])->first();
                    BeneficiaireBcv::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "user_client_id" => $client->id,
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }else if($contact['type'] == 'visa'){
                    BeneficiaireCard::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "customer_id" => $contact['customer_id'],
                        "last_digits" => $contact['last_digits'],
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }

            $beneficiary->bcvBeneficiaries;
            $beneficiary->cardBeneficiaries;
            $beneficiary->momoBeneficiaries;

            return $this->sendResponse($beneficiary, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function deleteBeneficiary(Request $request){
        try {
            $beneficiary = Beneficiaire::where('id',$request->id)->first();

            if(!$beneficiary){
                return $this->sendError('Contact non trouvé', [], 401);
            }

            $beneficiary->deleted = 1;
            $beneficiary->save();

            return $this->sendResponse($beneficiary, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function editBeneficiary(Request $request){
        try {
            $beneficiary = Beneficiaire::where('id',$request->id)->first();

            if(!$beneficiary){
                return $this->sendError('Contact non trouvé', [], 401);
            }

            $beneficiary->name = $request->name;
            $beneficiary->save();

            return $this->sendResponse($beneficiary, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function addContact(Request $request){
        try {
            $beneficiary = Beneficiaire::where('id',$request->beneficiary_id)->first();

            $contacts = $request->data;

            foreach($contacts as $contact){

                if($contact['type'] == 'momo' || $contact['type'] == 'bmo'){
                    BeneficiaireMomo::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "type" => $contact['type'],
                        "code" => $contact['code'],
                        "telephone" => $contact['telephone'],
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }else if($contact['type'] == 'bcv'){
                    $client = UserClient::where('username',$contact['username'])->first();
                    BeneficiaireBcv::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "user_client_id" => $client->id,
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }else if($contact['type'] == 'visa'){
                    BeneficiaireCard::create([
                        'id' => Uuid::uuid4()->toString(),
                        "beneficiaire_id" => $beneficiary->id,
                        "customer_id" => $contact['customer_id'],
                        "last_digits" => $contact['last_digits'],
                        "deleted" => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }

            $beneficiary->bcvBeneficiaries;
            $beneficiary->cardBeneficiaries;
            $beneficiary->momoBeneficiaries;

            return $this->sendResponse($beneficiary, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function deleteContact(Request $request){
        try {
            if($request->type == 'momo' || $request->type == 'bmo'){
                $contact = BeneficiaireMomo::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $contact->deleted = 1;
                $contact->save();
            }else if($request->type == 'bcv'){
                $contact = BeneficiaireBcv::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $contact->deleted = 1;
                $contact->save();
            }else if($request->type == 'visa'){
                $contact = BeneficiaireCard::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $contact->deleted = 1;
                $contact->save();
            }else{
                return $this->sendError('Type inconnu', [], 401);
            }

            return $this->sendResponse($contact, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }

    public function editContact(Request $request){
        try {
            if($request->type == 'momo' || $request->type == 'bmo'){
                $contact = BeneficiaireMomo::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $contact->code = $request->code;
                $contact->telephone = $request->telepone;
                $contact->save();
            }else if($request->type == 'bcv'){
                $contact = BeneficiaireBcv::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $client = UserClient::where('username',$request->username)->first();
                $contact->user_client_id = $client->id;
                $contact->save();
            }else if($request->type == 'visa'){
                $contact = BeneficiaireCard::where('id',$request->id)->first();
                if(!$contact){
                    return $this->sendError('Contact non trouvé', [], 401);
                }
                $contact->customer_id = $request->customer_id;
                $contact->last_digits = $request->last_digits;
                $contact->save();
            }else{
                return $this->sendError('Type inconnu', [], 401);
            }
            return $this->sendResponse($contact, 'Success');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        };
    }
}
