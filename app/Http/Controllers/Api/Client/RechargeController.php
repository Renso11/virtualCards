<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserClient;
use App\Models\Info;
use App\Models\GtpRequest;
use App\Models\Retrait;
use App\Models\Departement;
use App\Models\Commission;
use App\Models\AccountCommission;
use App\Models\AccountCommissionOperation;
use App\Models\AccountDistribution;
use App\Models\AccountDistributionOperation;
use App\Models\Depot;
use App\Models\Gamme;
use App\Models\CarteVirtuelle;
use App\Models\CartePhysique;
use App\Models\Recharge;
use App\Models\KycClient;
use App\Models\UserCardBuy;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationOtp;
use App\Mail\MailAlerteVerification;
use App\Mail\MailAlerte;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Ixudra\Curl\Facades\Curl;
use App\Mail\AlerteRecharge;
use App\Mail\VentePhysique as MailVentePhysique;
use App\Mail\VenteVirtuelle as MailVenteVirtuelle;
use App\Mail\CodeValidationRetrait;
use App\Mail\CodeValidationTransfert;
use App\Models\Frai;
use App\Models\SelfRetrait;
use App\Models\TransfertIn;
use App\Models\TransfertOut;
use App\Models\UserCard;
use App\Models\Service;
use App\Models\Beneficiaire;
use App\Models\BeneficiaireBcv;
use App\Models\BeneficiaireCard;
use App\Models\BeneficiaireMomo;
use App\Models\EntityAccountCommission;
use App\Models\EntityAccountCommissionOperation;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Ramsey\Uuid\Uuid;

class RechargeController extends Controller
{
    public function __construct() {
        $this->middleware('is-auth', ['except' => ['addContact','createCompteClient', 'loginCompteClient', 'sendCode', 'checkCodeOtp', 'resetPassword','verificationPhone', 'verificationInfoPerso','verificationInfoPiece','saveFile','sendCodeTelephoneRegistration','getServices','sendCodeTelephone']]);
    }

    public function tokenValide(Request $request){
        try {            
            return true;
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
