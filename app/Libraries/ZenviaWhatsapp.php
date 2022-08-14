<?php
namespace App\Libraries;

use Storage;
use GuzzleHttp;

use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

use Ramsey\Uuid\Uuid;
use App\Models\User;
use App\Models\WhatsappUser;
use App\Models\WhatsappChat;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\WhatsappLog;
use App\Libraries\OCR;
use App\Mail\RegisterCoupon;
use Intervention\Image\ImageManager as Image;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\RequestInterface;
use Illuminate\Support\Facades\Log;

use Dflydev\DotAccessData\Data;

class ZenviaWhatsapp {

    private $configWhatsapp;
    private $url;
    private $token;
    private $uid;

    private $steps = [
        'invoice' => 'stepInvoice',
        'cnpj' => 'stepCNPJ',
        // 'companyName' => 'stepCompanyName',
        'couponNumber' => 'stepCouponNumber',
        'buyDate' => 'stepBuyDate',
        'products' => 'stepProducts',
        // 'products' => 'stepSimpleProduct',
        // 'prize' => 'stepPrize',
        'amount' => 'stepAmount',
        // 'prizeStore' => 'stepPrizeStore',
        'lastStep' => 'stepLast'
    ];

    public function __construct(){
        $this->configWhatsapp = 'Whatsapp.json';

        $this->url = env('WHATSAPP_ZENVIA_URL');
        $this->token = env('WHATSAPP_ZENVIA_TOKEN');
        $this->uid = env('WHATSAPP_ZENVIA_UID');
    }

    public function send($to, $message, $data = []){

        $sendURL = $this->url . '/v1/channels/whatsapp/messages';

        /*
        $createLog = WhatsappLog::create([
            'to' => $to,
            'body' => $message,
            'type' => 'MESSAGE_SENT',
            'sender_name' => 'Coqueiro',
            'from_me' => 1,
            'author' => env('WHATSAPP_ZENVIA_UID')
        ]);

        $lastLog = WhatsappLog::where('to', $to)->where('author', env('WHATSAPP_ZENVIA_UID'))->where('body', $message)->whereDate('created_at', '>=', date('Y-m-d H:i:s', time() - 86400))->skip(1)->first();

        */
        try {
            // if($createLog->body != $lastLog->body){
            if(true){
                
                $client = new GuzzleHttp\Client();
                $response = $client->request('POST', $sendURL, [
                    'json' => [
                        'from' => $this->uid,
                        'to' => $to,
                        'contents' => [
                            ['type' => 'text', 'text' => vsprintf($message, $data)]
                        ]
                    ],
                    'headers' => [
                        'X-API-TOKEN' => $this->token,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    ],
                    'debug' => false
                ]);
                // print json_encode($response->getBody()->getContents());

                // Log::debug('Enviando Mensagem Para: ' . $to . ': ' . vsprintf($message, $data));

                WhatsappLog::create([
                    'id_message' => Uuid::uuid4(),
                    'to' => $to,
                    'body' => vsprintf($message, $data),
                    'type' => 'MESSAGE',
                    'sender_name' => 'Coqueiro Account',
                    'from_me' => 1,
                    'author' => env('WHATSAPP_ZENVIA_UID')
                ]);
        

                return $response;
            } else {
                // Log::debug('Envio de mensagem repetida');
            }
        } catch(ClientException $e){
            print $e->getResponse()->getBody()->getContents();
        }
    }

    public function sendImage($to, $message, $data = []){

        $sendURL = $this->url . '/send/image';

        try {
            $client = new GuzzleHttp\Client();
            $response = $client->request('POST', $sendURL, [
                'form_params' => [
                    'from' => '',
                    'to' => '',
                    'contents' => [
                        'type' => 'file',
                        'fileUrl' => $message['url'],
                        'filemimeType' => $message['mime'],
                        'fileCaption' => $message['caption']
                    ]
                ]
            ]);
            return $response;
        } catch(Exception $e){
            return false;
        }
    }

    private function loadJSON(){
        return json_decode(Storage::disk('local')->get($this->configWhatsapp), true);
    }

    private function buildMessage($content){

        $message  = NULL;

        foreach($content['message']['contents'] as $message){
            if($message['type'] == 'text'){
                $message = $message['text'];
                break;
            } else if($message['type'] == 'file'){
                $message = $message['fileUrl'];
                break;
            }
        }

        // Log::debug('Join: ZenviaWhatsapp::buildMessage() will return: ' . $message);

        return $message;
    }

    private function buildType($content){

        $type  = NULL;

        foreach($content['message']['contents'] as $message){
            if($message['type'] == 'text'){
                $type = $message['type'];
                break;
            } else if($message['type'] == 'file'){
                $type = $message['type'];
                break;
            }
        }

        // Log::debug('Join: ZenviaWhatsapp::buildType() will return: ' . $type);

        return $type;
    }

    public function receiveMessage($request){
        $json = $this->loadJSON();

        $requestBody = json_decode($request->getContent(), true);

        // $body = $requestBody['message']['contents']['text'];

        Log::debug('MESSAGE_RECEIVED: ' . strtotime($requestBody['timestamp']) . ' / ' . (time()-120));
        if(strtotime($requestBody['timestamp']) < (time() - 120)){
            Log::debug('#NOT_RECEIVED: Tempo máximo para envio ultrapassado.');
            return;
        }

        WhatsappLog::create([
            'id_message' => $requestBody['id'],
            'to' => env('WHATSAPP_ZENVIA_UID'),
            'body' => $request->getContent(),
            'type' => $requestBody['type'],
            'sender_name' => $requestBody['message']['visitor']['name'],
            'from_me' => ($requestBody['message']['from'] == env('WHATSAPP_ZENVIA_UID')) ? 1 : 0,
            'author' => $requestBody['message']['from']
        ]);

        $body = $this->buildMessage($requestBody);
        $type = $this->buildType($requestBody);

        // Log::debug('Call: ZenviaWhatsapp::buildMessage()');
        
        //if($data['message']['from'] == '5511996393535'){
        if(true){
            if($this->hasTextDefault(strtolower($body), $json)){

                // Log::debug('Call: ZenviaWhatsapp::runTextDefault()');
                $this->runTextDefault(strtolower($body), $json, $requestBody);
            } else {
                // Log::debug('Call: ZenviaWhatsapp::runSteps()');
                $mimeType = (isset($requestBody->fileMimeType)) ? $requestBody->fileMimeType : null;
                $this->runSteps($body, $json, $requestBody, $type, $mimeType);
            }
        } else {
            if($requestBody['direction'] == 'IN'){
                $this->send($data['message']['from'], 'O Whatsapp da promoção está em manutenção. Aguarde para participar.'); 
            }
        }
    }

    private function hasTextDefault($key, $json){
        return array_key_exists($key, $json['receive']);
    }

    private function assignArrayByPath(&$arr, $path, $value, $separator='.') {
        $keys = explode($separator, $path);
    
        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }
    
        $arr = $value;
    }

    private function buildParams($params, $data, $input = true){
        // Log::debug('Join: ZenviaWhatsapp::buildParams() params: ' . json_encode($params));
        // Log::debug('Join: ZenviaWhatsapp::buildParams() data: ' . json_encode($data));
        $data = new Data($data);
        $out = [];
        foreach($params as $item => $value){
            // $out[$item] = $input ? $data->{$value} : $value;
            $out[$item] = $data->get($value);
        }
        return $out;

    }

    public function buildFields($fields, $action){
        $out = [];

        foreach($fields as $item){
            $out[] = $action->$item;
        }
        return $out;
    }

    private function runTextDefault($key, $json, $data){
        // Log::debug('Join: ZenviaWhatsapp::runTextDefault()');
        if(array_key_exists($key, $json['receive'])){
            // Log::debug('Check: ZenviaWhatsapp::runTextDefault() has receive key');
            if($json['receive'][$key]['response']['type'] == 'message'){
                $message = $json['receive'][$key]['response']['text'];
                $this->send($data['message']['from'], $json['receive'][$key]['response']['text']);
            } else if($json['receive'][$key]['response']['type'] == 'action') {
                // Log::debug('Check: ZenviaWhatsapp::runTextDefault() type is action');

                $class = new $json['receive'][$key]['response']['action']['class']();
                $method = $json['receive'][$key]['response']['action']['method'];
                $params = $json['receive'][$key]['response']['action']['params'];
                $fields = $json['receive'][$key]['response']['action']['fields'];

                $action = call_user_func([$class, $method], $this->buildParams($params, $data));

                // Log::debug('Show: ZenviaWhatsapp::runTextDefault() buildParam is: ' . json_encode($this->buildParams($params, $data)));
                // Log::debug('Show: ZenviaWhatsapp::runTextDefault() action is: ' . json_encode($action));

                if($action->status === true){
                    $message = vsprintf($json['receive'][$key]['response']['action']['text'], $this->buildFields($fields, $action->data));
                } else {
                    $message = $action->error;
                }
            
                
                $this->send($data['message']['from'], $message);
            }

            if(isset($json['receive'][$key]['response']['next'])){
                $this->runTextDefault($json['receive'][$key]['response']['next'], $json, $data);
            }
        }
    }

    private function findMobile($mobileClean, $mobileFiltered, $mobileWithNine){
        return DB::table('users')
                ->where('mobile', $mobileClean)
                ->orWhere('mobile', $mobileFiltered)
                ->orWhere('mobile', $mobileWithNine)
                ->latest()
                ->limit(1)
                ->get();
    }

    private function createUser($mobile){
        return User::create([
            'mobile' => preg_replace('/[^\d]/', '', $mobile)
        ]);
    }

    private function getLastChat($whatsappUserID){
        return WhatsappChat::where(['whatsappuser_id' => $whatsappUserID, 'status' => 0])->latest()->first();
    }

    private function startChat($whatsappUserID, $json, $data){
        $this->send($data['message']['from'], $json['send']['start']['text']);
    }

    private function runStep($chat, $json, $data){
        foreach($this->steps as $item => $method){
            $action = call_user_func_array([$this, $method], [&$chat, $json, $data]);

            if($action->next === false){
                break;
            }
        }
    }

    public function discoverNextStep($current){
        $out = 'lastStep';
        $discovered = false;
        foreach($this->steps as $item => $method){

            if($discovered === true){
                $out = $method;
                break;
            }

            if($method == $current){
                $discovered = true;
            }
        }
        return $out;
    }

    private function runSteps($body, $json, $data, $type, $mimeType = null){
        
        // Log::debug('Join: ZenviaWhatsapp::runSteps()');

        $chatData['id_message'] = $data['id'];
        $chatData['body'] = $body;
        $chatData['type'] = $type;
        $chatData['mimeType'] = $mimeType;
        $chatData['sender_name'] = $data['message']['visitor']['name'];
        $chatData['from_me'] = ($data['message']['from'] == env('WHATSAPP_ZENVIA_UID')) ? 1 : 0;
        $chatData['author'] = $data['message']['from'];

        $mobile = $data['message']['from'];
        $mobileClean = preg_replace("/^\d{2}/", "", $mobile);

        $listMobile['full'] = $mobile;
        $listMobile['clean'] = $mobileClean;

        if($chatData['from_me'] == 0){
            // Log::debug('Join: ZenviaWhatsapp::runSteps() field \'from_me\' == 0');
            DB::transaction(function () use ($data, $json, $listMobile, $chatData, $body) {
                $mobileFiltered = (strlen($listMobile['clean']) == 11) ? substr_replace($listMobile['clean'], '', 2, 1) : $listMobile['clean'];
                $mobileWithNine = (strlen($listMobile['clean']) == 10) ? substr_replace($listMobile['clean'], '9', 2, 0) : $listMobile['clean'];

                $findWhatsappUser = WhatsappUser::where('mobile', $listMobile['clean'])->where('completed', 1)->first();
                if($findWhatsappUser){
                    $lastChat = $this->getlastChat($findWhatsappUser->id);

                    if($lastChat){
                        $this->runStep($lastChat, $json, $chatData);
                    } else {
                        $this->startChat($findWhatsappUser->id, $json, $data);
                    }
                } else {
                    Log::debug('Call: ZenviaWhatsapp::handleWhatsappUser()');
                    $this->handleWhatsappUser($json, $data, $body);
                }
            });
        }
    }

    private function stepInvoice(&$chat, $json, $data){
        return WhatsappSteps\StepInvoice::init($chat, $json, $data);        
    }

    private function stepAmount(&$chat, $json, $data){
        return WhatsappSteps\StepAmount::init($chat, $json, $data);
    }

    private function stepCouponNumber(&$chat, $json, $data){
        return WhatsappSteps\StepCouponNumber::init($chat, $json, $data);
    }

    private function stepProducts(&$chat, $json, $data){
        return WhatsappSteps\StepProducts::init($chat, $json, $data);
    }

    private function stepSimpleProduct(&$chat, $json, $data){
        return WhatsappSteps\StepSimpleProduct::init($chat, $json, $data);
    }

    private function stepPrize(&$chat, $json, $data){
        return WhatsappSteps\StepPrize::init($chat, $json, $data);
    }

    private function stepPrizeStore(&$chat, $json, $data){
        return WhatsappSteps\StepPrizeStore::init($chat, $json, $data);
    }

    private function stepCNPJ(&$chat, $json, $data){
        return WhatsappSteps\StepCNPJ::init($chat, $json, $data);
    }

    private function stepCompanyName(&$chat, $json, $data){
        return WhatsappSteps\StepCompanyName::init($chat, $json, $data);
    }

    private function stepBuyDate(&$chat, $json, $data){
        return WhatsappSteps\StepBuyDate::init($chat, $json, $data);
    }

    private function stepBuyState(&$chat, $json, $data){
        return WhatsappSteps\StepBuyState::init($chat, $json, $data);
    }

    private function stepRequiredProduct(&$chat, $json, $data){
        return WhatsappSteps\StepRequiredProduct::init($chat, $json, $data);
    }


    private function stepLast(&$chat, $json, $data){
        return WhatsappSteps\StepLast::init($chat, $json, $data);
    }

    private function handleWhatsappUser($json, $data, $body){
        WhatsappSteps\HandleWhatsappUser::init($this, $json, $data, $body);
    }
}