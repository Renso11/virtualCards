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

function getFeeAndRepartition($type, $montant){
  $frais = Frai::where('deleted',0)->where('type_operation',$type)->where('start','<=',$montant)->where('end','>=',$montant)->orderBy('id','DESC')->first();
  return $frais;
}

function checkPayment($method, $reference, $amount){
  if($method == 'bmo'){            
    $base_url_bmo = env('BASE_BMO');

    $headers = [
      'X-Auth-ApiKey' => env('APIKEY_BMO'),
      'X-Auth-ApiSecret' => env('APISECRET_BMO'),
      'Content-Type' => 'application/json', 'Accept' => 'application/json'
    ];

    $client = new Client();
    $url = $base_url_bmo."/operation?partnerReference=".$reference;

    $response = $client->request('GET', $url, [
        'headers' => $headers
    ]);

    $response = json_decode($response->getBody());

    if($response->status == 'CONFIRMED'){
      if($response->amount == $amount){
        return 'success';
      }else{
        return 'bad_amount';
      }
    }else{
      return 'not_success';
    }
  }else{
    $public_key = env('API_KEY_KKIAPAY');
    $private_key = env('PRIVATE_KEY_KKIAPAY');
    $secret = env('SECRET_KEY_KKIAPAY');

    $kkiapay = new \Kkiapay\Kkiapay($public_key,$private_key,$secret);

    $response = $kkiapay->verifyTransaction($reference);
    
    if($response->status == 'SUCCESS'){
      if($response->amount == $amount){
        return 'success';
      }else{
        return 'bad_amount';
      }
    }else{
      return 'not_success';
    }
  }
}

function resultat_check_status_kkp($transactionId){
    try {  
        
        $base_url_kkp = env('BASE_KKIAPAY');

        $client = new Client();
        $url = $base_url_kkp."/api/v1/transactions/status";
        
        $headers = [
            'x-api-key' => env('API_KEY_KKIAPAY')
        ];

        $body = [
            'transactionId' => $transactionId
        ];

        $body = json_encode($body);

        $response = $client->request('POST', $url, [
            'headers' => $headers,
            'body' => $body
        ]);

        
        $externalTransaction = json_decode($response->getBody());

        return $externalTransaction;
        
    } catch (BadResponseException $e) {
        return $e->getMessage();
    }
}
