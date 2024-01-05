<?php
use App\Models\UserClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Ramsey\Uuid\Uuid;

function sendResponse($data, $message){
  $response = [
        'success' => true,
        'data'    => $data,
        'message' => $message,
    ];

    return response()->json($response, 200);
}

function sendError($message, $data = [], $code = 404){
  $response = [
        'success' => false,
        'errors' => $message,
    ];


    if(!empty($data)){
        $response['data'] = $data;
    }
    return response()->json($response, $code);
}
    
function sendSms($receiver, $message){
    $endpoint = "https://api.wirepick.com/httpsms/send?client=ubabmo&password=udQ31DEzAXoC8Dyyhbut&phone=".$receiver."&text=".$message."&from=BCV"; 
        
    $client = new \GuzzleHttp\Client([                                                                                                                                                                   
        'verify' => false                                                                                                                                                                               
    ]);                                                                                                                                                                                               
                                                                                                                                                                                                        
    $response = $client->request('GET', $endpoint);                                                                                                                                   
                                                                                                                                                                                                        
    $statusCode = $response->getStatusCode();  
}

function encryptData($chaine, $cle) {
    $resultat = '';
    $longueurCle = strlen($cle);

    for ($i = 0; $i < mb_strlen($chaine, 'UTF-8'); $i++) {
        $char = mb_substr($chaine, $i, 1, 'UTF-8');
        $charCode = mb_ord($char, 'UTF-8') + mb_ord($cle[$i % $longueurCle], 'UTF-8');
        $resultat .= mb_chr($charCode, 'UTF-8');
    }

    return $resultat;
}

function decryptData($chaineEmbrouillee, $cle) {
    $resultat = '';
    $longueurCle = strlen($cle);

    for ($i = 0; $i < mb_strlen($chaineEmbrouillee, 'UTF-8'); $i++) {
        $char = mb_substr($chaineEmbrouillee, $i, 1, 'UTF-8');
        $charCode = mb_ord($char, 'UTF-8') - mb_ord($cle[$i % $longueurCle], 'UTF-8');
        $resultat .= mb_chr($charCode, 'UTF-8');
    }

    return $resultat;
}

function getUserSolde($user){
  $base_url = env('BASE_GTP_API');
  $programID = env('PROGRAM_ID');
  $authLogin = env('AUTH_LOGIN');
  $authPass = env('AUTH_PASS');

  $user = UserClient::where('id',$user)->first();
  $cards = $user->userCards;

  $solde = 0;
  foreach ($cards as $key => $value) {  
    $client = new Client();
    $encrypt_Key = env('ENCRYPT_KEY');
    $url = $base_url."accounts/".decryptData((string)$value->customer_id, $encrypt_Key)."/balance";

    $headers = [
      'programId' => $programID,
      'requestId' => Uuid::uuid4()->toString(),
    ];

    $auth = [
      $authLogin,
      $authPass
    ];

    try {
        $response = $client->request('GET', $url, [
            'auth' => $auth,
            'headers' => $headers,
        ]);

        $balance = json_decode($response->getBody());
        $solde += $balance->balance;
    } catch (BadResponseException $e) {
        $json = json_decode($e->getResponse()->getBody()->getContents());
        $error = $json->title.'.'.$json->detail;
        return $error;
    }
    
    return $solde;
  }
}

function getCarteInformation($code,$type){
  try {
      $encrypt_Key = env('ENCRYPT_KEY');

      $code = decryptData($code, $encrypt_Key);
      
      $base_url = env('BASE_GTP_API');
      $programID = env('PROGRAM_ID');
      $authLogin = env('AUTH_LOGIN');
      $authPass = env('AUTH_PASS');
      
      if($type == 'all'){
          
          $base_url = env('BASE_GTP_API');
          $programID = env('PROGRAM_ID');
          $authLogin = env('AUTH_LOGIN');
          $authPass = env('AUTH_PASS');

          $data = [];
          try {
              $client = new Client();
              $url = $base_url."accounts/".$code;
          
              $headers = [
                  'programId' => $programID,
                  'requestId' => Uuid::uuid4()->toString(),
              ];
          
              $auth = [
                  $authLogin,
                  $authPass
              ];
              $response = $client->request('GET', $url, [
                  'auth' => $auth,
                  'headers' => $headers,
              ]);
          
              $clientInfo = json_decode($response->getBody());
          } catch (BadResponseException $e) {
              $json = json_decode($e->getResponse()->getBody()->getContents());
              $error = $json->title.'.'.$json->detail;
              return sendError($error, [], 500);
          }
      
          try {
  
              $client = new Client();
              $url = $base_url."accounts/".$code."/balance";
      
              $headers = [
                  'programId' => $programID,
                  'requestId' => Uuid::uuid4()->toString(),
              ];
      
              $auth = [
                  $authLogin,
                  $authPass
              ];

              $response = $client->request('GET', $url, [
                  'auth' => $auth,
                  'headers' => $headers,
              ]);
      
              $balance = json_decode($response->getBody());
          } catch (BadResponseException $e) {
              $json = json_decode($e->getResponse()->getBody()->getContents());
              $error = $json->title.'.'.$json->detail;
              return sendError($error, [], 500);
          } 
          try {
              $client = new Client();
              $url = $base_url."accounts/phone-number";
  
              $headers = [
                  'programId' => $programID,
                  'requestId' => Uuid::uuid4()->toString(),
              ];
      
              $query = [
                  'phoneNumber' => $clientInfo->mobilePhoneNumber
              ];
      
              $auth = [
                  $authLogin,
                  $authPass
              ];
              $response = $client->request('GET', $url, [
                  'auth' => $auth,
                  'headers' => $headers,
                  'query' => $query
              ]);
              
              $accountInfo = null;
              $accountInfoLists = json_decode($response->getBody())->accountInfoList;
              foreach ($accountInfoLists as $value) {
                  if($value->accountId == $code){
                      $accountInfo = $value;
                      break;
                  }
              }
          } catch (BadResponseException $e) {
              $json = json_decode($e->getResponse()->getBody()->getContents());   
              $error = $json->title.'.'.$json->detail;
              return sendError($error, [], 500);
          }
          

          $data['clientInfo'] = $clientInfo;
          $data['balance'] = $balance;
          $data['accountInfo'] = $accountInfo;
          return $data;
      }else{
          if($type == 'clientInfo'){
              try {  
                  $client = new Client();
                  $url = $base_url."accounts/".$code;
              
                  $headers = [
                      'programId' => $programID,
                      'requestId' => Uuid::uuid4()->toString(),
                  ];
              
                  $auth = [
                      $authLogin,
                      $authPass
                  ];
                  $response = $client->request('GET', $url, [
                      'auth' => $auth,
                      'headers' => $headers,
                  ]);
              
                  $clientInfo = json_decode($response->getBody());
                  return $clientInfo;
              } catch (BadResponseException $e) {
                  $json = json_decode($e->getResponse()->getBody()->getContents());
                  $error = $json->title.'.'.$json->detail;
                  return sendError($error, [], 500);
              }
          }else if($type == 'accountInfo'){
              try {
                
                  $client = new Client();
                  $url = $base_url."accounts/".$code;
              
                  $headers = [
                      'programId' => $programID,
                      'requestId' => Uuid::uuid4()->toString(),
                  ];
              
                  $auth = [
                      $authLogin,
                      $authPass
                  ];
                  $response = $client->request('GET', $url, [
                      'auth' => $auth,
                      'headers' => $headers,
                  ]);
              
                  $clientInfo = json_decode($response->getBody());
                  

                  $client = new Client();
                  $url = $base_url."accounts/phone-number";
      
                  $headers = [
                      'programId' => $programID,
                      'requestId' => Uuid::uuid4()->toString(),
                  ];
          
                  $query = [
                      'phoneNumber' => $clientInfo->mobilePhoneNumber
                  ];
          
                  $auth = [
                      $authLogin,
                      $authPass
                  ];
                  $response = $client->request('GET', $url, [
                      'auth' => $auth,
                      'headers' => $headers,
                      'query' => $query
                  ]);
                  
                  $accountInfo = null;
                  $accountInfoLists = json_decode($response->getBody())->accountInfoList;
                  foreach ($accountInfoLists as $value) {
                      if($value->accountId == $code){
                          $accountInfo = $value;
                          break;
                      }
                  }
                  return $accountInfo;
              } catch (BadResponseException $e) {
                  $json = json_decode($e->getResponse()->getBody()->getContents());   
                  $error = $json->title.'.'.$json->detail;
                  return sendError($error, [], 500);
              }
          }else if($type == 'balance'){        
              try {      
                  $client = new Client();
                  $url = $base_url."accounts/".$code."/balance";
          
                  $headers = [
                      'programId' => $programID,
                      'requestId' => Uuid::uuid4()->toString(),
                  ];
          
                  $auth = [
                      $authLogin,
                      $authPass
                  ];
  
                  $response = $client->request('GET', $url, [
                      'auth' => $auth,
                      'headers' => $headers,
                  ]);
          
                  $balance = json_decode($response->getBody())->balance;
                  return $balance;
              } catch (BadResponseException $e) {
                  $json = json_decode($e->getResponse()->getBody()->getContents());
                  $error = $json->title.'.'.$json->detail;
                  return sendError($error, [], 500);
              } 
          }
      }
      
  } catch (\Exception $e) {
      return sendError($e->getMessage(), [], 500);
  }
}

function unaccent( $str ){
    $transliteration = array(
    'Ĳ' => 'I', 'Ö' => 'O','Œ' => 'O','Ü' => 'U','ä' => 'a','æ' => 'a',
    'ĳ' => 'i','ö' => 'o','œ' => 'o','ü' => 'u','ß' => 's','ſ' => 's',
    'À' => 'A','Á' => 'A','Â' => 'A','Ã' => 'A','Ä' => 'A','Å' => 'A',
    'Æ' => 'A','Ā' => 'A','Ą' => 'A','Ă' => 'A','Ç' => 'C','Ć' => 'C',
    'Č' => 'C','Ĉ' => 'C','Ċ' => 'C','Ď' => 'D','Đ' => 'D','È' => 'E',
    'É' => 'E','Ê' => 'E','Ë' => 'E','Ē' => 'E','Ę' => 'E','Ě' => 'E',
    'Ĕ' => 'E','Ė' => 'E','Ĝ' => 'G','Ğ' => 'G','Ġ' => 'G','Ģ' => 'G',
    'Ĥ' => 'H','Ħ' => 'H','Ì' => 'I','Í' => 'I','Î' => 'I','Ï' => 'I',
    'Ī' => 'I','Ĩ' => 'I','Ĭ' => 'I','Į' => 'I','İ' => 'I','Ĵ' => 'J',
    'Ķ' => 'K','Ľ' => 'K','Ĺ' => 'K','Ļ' => 'K','Ŀ' => 'K','Ł' => 'L',
    'Ñ' => 'N','Ń' => 'N','Ň' => 'N','Ņ' => 'N','Ŋ' => 'N','Ò' => 'O',
    'Ó' => 'O','Ô' => 'O','Õ' => 'O','Ø' => 'O','Ō' => 'O','Ő' => 'O',
    'Ŏ' => 'O','Ŕ' => 'R','Ř' => 'R','Ŗ' => 'R','Ś' => 'S','Ş' => 'S',
    'Ŝ' => 'S','Ș' => 'S','Š' => 'S','Ť' => 'T','Ţ' => 'T','Ŧ' => 'T',
    'Ț' => 'T','Ù' => 'U','Ú' => 'U','Û' => 'U','Ū' => 'U','Ů' => 'U',
    'Ű' => 'U','Ŭ' => 'U','Ũ' => 'U','Ų' => 'U','Ŵ' => 'W','Ŷ' => 'Y',
    'Ÿ' => 'Y','Ý' => 'Y','Ź' => 'Z','Ż' => 'Z','Ž' => 'Z','à' => 'a',
    'á' => 'a','â' => 'a','ã' => 'a','ā' => 'a','ą' => 'a','ă' => 'a',
    'å' => 'a','ç' => 'c','ć' => 'c','č' => 'c','ĉ' => 'c','ċ' => 'c',
    'ď' => 'd','đ' => 'd','è' => 'e','é' => 'e','ê' => 'e','ë' => 'e',
    'ē' => 'e','ę' => 'e','ě' => 'e','ĕ' => 'e','ė' => 'e','ƒ' => 'f',
    'ĝ' => 'g','ğ' => 'g','ġ' => 'g','ģ' => 'g','ĥ' => 'h','ħ' => 'h',
    'ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','ī' => 'i','ĩ' => 'i',
    'ĭ' => 'i','į' => 'i','ı' => 'i','ĵ' => 'j','ķ' => 'k','ĸ' => 'k',
    'ł' => 'l','ľ' => 'l','ĺ' => 'l','ļ' => 'l','ŀ' => 'l','ñ' => 'n',
    'ń' => 'n','ň' => 'n','ņ' => 'n','ŉ' => 'n','ŋ' => 'n','ò' => 'o',
    'ó' => 'o','ô' => 'o','õ' => 'o','ø' => 'o','ō' => 'o','ő' => 'o',
    'ŏ' => 'o','ŕ' => 'r','ř' => 'r','ŗ' => 'r','ś' => 's','š' => 's',
    'ť' => 't','ù' => 'u','ú' => 'u','û' => 'u','ū' => 'u','ů' => 'u',
    'ű' => 'u','ŭ' => 'u','ũ' => 'u','ų' => 'u','ŵ' => 'w','ÿ' => 'y',
    'ý' => 'y','ŷ' => 'y','ż' => 'z','ź' => 'z','ž' => 'z','Α' => 'A',
    'Ά' => 'A','Ἀ' => 'A','Ἁ' => 'A','Ἂ' => 'A','Ἃ' => 'A','Ἄ' => 'A',
    'Ἅ' => 'A','Ἆ' => 'A','Ἇ' => 'A','ᾈ' => 'A','ᾉ' => 'A','ᾊ' => 'A',
    'ᾋ' => 'A','ᾌ' => 'A','ᾍ' => 'A','ᾎ' => 'A','ᾏ' => 'A','Ᾰ' => 'A',
    'Ᾱ' => 'A','Ὰ' => 'A','ᾼ' => 'A','Β' => 'B','Γ' => 'G','Δ' => 'D',
    'Ε' => 'E','Έ' => 'E','Ἐ' => 'E','Ἑ' => 'E','Ἒ' => 'E','Ἓ' => 'E',
    'Ἔ' => 'E','Ἕ' => 'E','Ὲ' => 'E','Ζ' => 'Z','Η' => 'I','Ή' => 'I',
    'Ἠ' => 'I','Ἡ' => 'I','Ἢ' => 'I','Ἣ' => 'I','Ἤ' => 'I','Ἥ' => 'I',
    'Ἦ' => 'I','Ἧ' => 'I','ᾘ' => 'I','ᾙ' => 'I','ᾚ' => 'I','ᾛ' => 'I',
    'ᾜ' => 'I','ᾝ' => 'I','ᾞ' => 'I','ᾟ' => 'I','Ὴ' => 'I','ῌ' => 'I',
    'Θ' => 'T','Ι' => 'I','Ί' => 'I','Ϊ' => 'I','Ἰ' => 'I','Ἱ' => 'I',
    'Ἲ' => 'I','Ἳ' => 'I','Ἴ' => 'I','Ἵ' => 'I','Ἶ' => 'I','Ἷ' => 'I',
    'Ῐ' => 'I','Ῑ' => 'I','Ὶ' => 'I','Κ' => 'K','Λ' => 'L','Μ' => 'M',
    'Ν' => 'N','Ξ' => 'K','Ο' => 'O','Ό' => 'O','Ὀ' => 'O','Ὁ' => 'O',
    'Ὂ' => 'O','Ὃ' => 'O','Ὄ' => 'O','Ὅ' => 'O','Ὸ' => 'O','Π' => 'P',
    'Ρ' => 'R','Ῥ' => 'R','Σ' => 'S','Τ' => 'T','Υ' => 'Y','Ύ' => 'Y',
    'Ϋ' => 'Y','Ὑ' => 'Y','Ὓ' => 'Y','Ὕ' => 'Y','Ὗ' => 'Y','Ῠ' => 'Y',
    'Ῡ' => 'Y','Ὺ' => 'Y','Φ' => 'F','Χ' => 'X','Ψ' => 'P','Ω' => 'O',
    'Ώ' => 'O','Ὠ' => 'O','Ὡ' => 'O','Ὢ' => 'O','Ὣ' => 'O','Ὤ' => 'O',
    'Ὥ' => 'O','Ὦ' => 'O','Ὧ' => 'O','ᾨ' => 'O','ᾩ' => 'O','ᾪ' => 'O',
    'ᾫ' => 'O','ᾬ' => 'O','ᾭ' => 'O','ᾮ' => 'O','ᾯ' => 'O','Ὼ' => 'O',
    'ῼ' => 'O','α' => 'a','ά' => 'a','ἀ' => 'a','ἁ' => 'a','ἂ' => 'a',
    'ἃ' => 'a','ἄ' => 'a','ἅ' => 'a','ἆ' => 'a','ἇ' => 'a','ᾀ' => 'a',
    'ᾁ' => 'a','ᾂ' => 'a','ᾃ' => 'a','ᾄ' => 'a','ᾅ' => 'a','ᾆ' => 'a',
    'ᾇ' => 'a','ὰ' => 'a','ᾰ' => 'a','ᾱ' => 'a','ᾲ' => 'a','ᾳ' => 'a',
    'ᾴ' => 'a','ᾶ' => 'a','ᾷ' => 'a','β' => 'b','γ' => 'g','δ' => 'd',
    'ε' => 'e','έ' => 'e','ἐ' => 'e','ἑ' => 'e','ἒ' => 'e','ἓ' => 'e',
    'ἔ' => 'e','ἕ' => 'e','ὲ' => 'e','ζ' => 'z','η' => 'i','ή' => 'i',
    'ἠ' => 'i','ἡ' => 'i','ἢ' => 'i','ἣ' => 'i','ἤ' => 'i','ἥ' => 'i',
    'ἦ' => 'i','ἧ' => 'i','ᾐ' => 'i','ᾑ' => 'i','ᾒ' => 'i','ᾓ' => 'i',
    'ᾔ' => 'i','ᾕ' => 'i','ᾖ' => 'i','ᾗ' => 'i','ὴ' => 'i','ῂ' => 'i',
    'ῃ' => 'i','ῄ' => 'i','ῆ' => 'i','ῇ' => 'i','θ' => 't','ι' => 'i',
    'ί' => 'i','ϊ' => 'i','ΐ' => 'i','ἰ' => 'i','ἱ' => 'i','ἲ' => 'i',
    'ἳ' => 'i','ἴ' => 'i','ἵ' => 'i','ἶ' => 'i','ἷ' => 'i','ὶ' => 'i',
    'ῐ' => 'i','ῑ' => 'i','ῒ' => 'i','ῖ' => 'i','ῗ' => 'i','κ' => 'k',
    'λ' => 'l','μ' => 'm','ν' => 'n','ξ' => 'k','ο' => 'o','ό' => 'o',
    'ὀ' => 'o','ὁ' => 'o','ὂ' => 'o','ὃ' => 'o','ὄ' => 'o','ὅ' => 'o',
    'ὸ' => 'o','π' => 'p','ρ' => 'r','ῤ' => 'r','ῥ' => 'r','σ' => 's',
    'ς' => 's','τ' => 't','υ' => 'y','ύ' => 'y','ϋ' => 'y','ΰ' => 'y',
    'ὐ' => 'y','ὑ' => 'y','ὒ' => 'y','ὓ' => 'y','ὔ' => 'y','ὕ' => 'y',
    'ὖ' => 'y','ὗ' => 'y','ὺ' => 'y','ῠ' => 'y','ῡ' => 'y','ῢ' => 'y',
    'ῦ' => 'y','ῧ' => 'y','φ' => 'f','χ' => 'x','ψ' => 'p','ω' => 'o',
    'ώ' => 'o','ὠ' => 'o','ὡ' => 'o','ὢ' => 'o','ὣ' => 'o','ὤ' => 'o',
    'ὥ' => 'o','ὦ' => 'o','ὧ' => 'o','ᾠ' => 'o','ᾡ' => 'o','ᾢ' => 'o',
    'ᾣ' => 'o','ᾤ' => 'o','ᾥ' => 'o','ᾦ' => 'o','ᾧ' => 'o','ὼ' => 'o',
    'ῲ' => 'o','ῳ' => 'o','ῴ' => 'o','ῶ' => 'o','ῷ' => 'o','А' => 'A',
    'Б' => 'B','В' => 'V','Г' => 'G','Д' => 'D','Е' => 'E','Ё' => 'E',
    'Ж' => 'Z','З' => 'Z','И' => 'I','Й' => 'I','К' => 'K','Л' => 'L',
    'М' => 'M','Н' => 'N','О' => 'O','П' => 'P','Р' => 'R','С' => 'S',
    'Т' => 'T','У' => 'U','Ф' => 'F','Х' => 'K','Ц' => 'T','Ч' => 'C',
    'Ш' => 'S','Щ' => 'S','Ы' => 'Y','Э' => 'E','Ю' => 'Y','Я' => 'Y',
    'а' => 'A','б' => 'B','в' => 'V','г' => 'G','д' => 'D','е' => 'E',
    'ё' => 'E','ж' => 'Z','з' => 'Z','и' => 'I','й' => 'I','к' => 'K',
    'л' => 'L','м' => 'M','н' => 'N','о' => 'O','п' => 'P','р' => 'R',
    'с' => 'S','т' => 'T','у' => 'U','ф' => 'F','х' => 'K','ц' => 'T',
    'ч' => 'C','ш' => 'S','щ' => 'S','ы' => 'Y','э' => 'E','ю' => 'Y',
    'я' => 'Y','ð' => 'd','Ð' => 'D','þ' => 't','Þ' => 'T','ა' => 'a',
    'ბ' => 'b','გ' => 'g','დ' => 'd','ე' => 'e','ვ' => 'v','ზ' => 'z',
    'თ' => 't','ი' => 'i','კ' => 'k','ლ' => 'l','მ' => 'm','ნ' => 'n',
    'ო' => 'o','პ' => 'p','ჟ' => 'z','რ' => 'r','ს' => 's','ტ' => 't',
    'უ' => 'u','ფ' => 'p','ქ' => 'k','ღ' => 'g','ყ' => 'q','შ' => 's',
    'ჩ' => 'c','ც' => 't','ძ' => 'd','წ' => 't','ჭ' => 'c','ხ' => 'k',
    'ჯ' => 'j','ჰ' => 'h' 
    );
    $str = str_replace( array_keys( $transliteration ),
                        array_values( $transliteration ),
                        $str);
    return $str;
}

