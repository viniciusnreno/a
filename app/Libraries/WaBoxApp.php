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
use App\Models\Store;
use App\Models\WhatsappLog;
use App\Libraries\OCR;
use Intervention\Image\ImageManager as Image;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\RequestInterface;
use Illuminate\Support\Facades\Log;

use Dflydev\DotAccessData\Data;

class WaBoxApp {

    private $configWhatsapp;
    private $url;
    private $token;
    private $uid;

    private $steps = [
        'invoice' => 'stepInvoice',
        'cnpj' => 'stepCNPJ',
        'couponNumber' => 'stepCouponNumber',
        'buyDate' => 'stepBuyDate',
        // 'store' => 'stepStore',
        'products' => 'stepProducts',
        // 'products' => 'stepOneProduct',
        // 'requiredProduct' => 'stepRequiredProduct',
        'amount' => 'stepAmount',
        'lastStep' => 'stepLast'
    ];

    public function __construct(){
        $this->configWhatsapp = 'Whatsapp.json';

        $this->url = env('WHATSAPP_WABOX_URL');
        $this->token = env('WHATSAPP_WABOX_TOKEN');
        $this->uid = env('WHATSAPP_WABOX_UID');
    }

    public function send($to, $message, $data = []){

        $sendURL = $this->url . '/send/chat';

        try {
            $client = new GuzzleHttp\Client();
            $response = $client->request('POST', $sendURL, [
                'form_params' => [
                    'token' => $this->token,
                    'uid' => $this->uid,
                    'to' => $to,
                    'custom_uid' => Uuid::uuid1(),
                    'text' => vsprintf($message, $data)
                ]
            ]);

            WhatsappLog::create([
                'id_message' => Uuid::uuid4(),
                'to' => $to,
                'body' => vsprintf($message, $data),
                'type' => 'MESSAGE',
                'sender_name' => 'Broto Legal Account',
                'from_me' => 1,
                'author' => env('WHATSAPP_WABOX_UID')
            ]);
            return $response;
        } catch(Exception $e){
            return false;
        }
    }

    public function sendImage($to, $message, $data = []){

        $sendURL = $this->url . '/send/image';

        try {
            $client = new GuzzleHttp\Client();
            $response = $client->request('POST', $sendURL, [
                'form_params' => [
                    'token' => $this->token,
                    'uid' => $this->uid,
                    'to' => $to,
                    'custom_uid' => Uuid::uuid1(),
                    'url' => $message['url']
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

    public function receive($data){
        $json = $this->loadJSON();
        $event = $data->input('event');

        // $event = 'message';

        if($event == 'message'){            
            switch($data->input('message.type')){
                case 'chat': $body = $data->input('message.body.text'); break;
                case 'image': $body = $data->input('message.body.url'); break;
                default: $body = $data->input('message.body.text');
            }

            // $body = 'consultar';

            // Log::debug(json_encode($data->all()));

            WhatsappLog::create([
                'id_message' => $data->input('message.uid'),
                'to' => env('WHATSAPP_WABOX_UID'),
                'body' => $data->input('message.body.text'),
                'type' => $data->input('message.type'),
                'sender_name' => $data->input('contact.name'),
                'from_me' => ($data->input('contact.uid') == env('WHATSAPP_WABOX_UID')) ? 1 : 0,
                'author' => $data->input('contact.uid')
            ]);

            //if($data->input('contact.uid') == '5511996393535'){
            if(true){
                if($this->hasTextDefault(strtolower($body), $json)){
                    $this->runTextDefault(strtolower($body), $json, $data);
                } else {
                    $this->runSteps($body, $json, $data);
                }
            } else {
                if($data->input('message.dir') == 'i'){
                    $this->send($data->input('contact.uid'), '*Promo Fome de Aprender Panco*: O Whatsapp da promoção está em manutenção. Aguarde para participar.'); 
                }
            }
        }
    }

    private function hasTextDefault($key, $json){
        return array_key_exists($key, $json['receive']);
    }

    private function buildParams($params, $data, $input = true){
        $out = [];
        foreach($params as $item => $value){
            $out[$item] = $input ? $data->input($value) : $value;
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
        if(array_key_exists($key, $json['receive'])){
            if($json['receive'][$key]['response']['type'] == 'message'){
                $message = $json['receive'][$key]['response']['text'];
                $this->send($data->input('contact.uid'), $json['receive'][$key]['response']['text']);
            } else if($json['receive'][$key]['response']['type'] == 'action') {
                $class = new $json['receive'][$key]['response']['action']['class']();
                $method = $json['receive'][$key]['response']['action']['method'];
                $params = $json['receive'][$key]['response']['action']['params'];
                $fields = $json['receive'][$key]['response']['action']['fields'];

                $action = call_user_func([$class, $method], $this->buildParams($params, $data));

                if($action->status === true){
                    $message = vsprintf($json['receive'][$key]['response']['action']['text'], $this->buildFields($fields, $action->data));
                } else {
                    $message = $action->error;
                }
            
                
                $this->send($data->input('contact.uid'), $message);
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

    private function readInvoiceImage($file){
        $ocr = new OCR();

        $response = $ocr->getText($file);

        preg_match('/\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}/', $response, $getCNPJ);

        $data['cnpj'] = trim(preg_replace('/[^\d]/', '', $getCNPJ[0]));

        return $data;
    }

    private function copyLocal($data){

        sleep(2);

        $filename = basename($data['body']);
        $filepath = public_path('storage/_upload/' . $filename);

        if(file_exists($filepath)){
            $filename = time() . '_' . $filename;
        }
        
        $imgHandler = new Image();

        try {
            $img = $imgHandler->make($data['body']);
        } catch (Exception $e){
            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao ler a sua imagem.');
        }

        return $img->save(public_path('storage/_upload/' . $filename));
        
    }


    private function startChat($whatsappUserID, $json, $data){
        $this->send($data->input('contact.uid'), $json['send']['start']['text']);
    }

    private function runStep($chat, $json, $data){
        foreach($this->steps as $item => $method){
            $action = call_user_func_array([$this, $method], [&$chat, $json, $data]);

            if($action->next === false){
                break;
            }
        }
    }

    private function stepInvoice(&$chat, $json, $data){
	print 'opa';
        $out = new \stdClass();

        if($chat->invoice && $chat->invoice_local){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'image'){
            $this->send($data['author'], '*Promo PANCO:* Você precisa enviar uma imagem legível do seu cupom fiscal');

            $out->status = false;
            $out->next = false;

            return $out;
        }
    
        $chat->invoice = $data['body'];

        if($chat->save()){
            $chat->fresh();
            $this->send($data['author'], "*Promo PANCO:* Ótimo! O seu cupom está sendo analisado e isso pode levar alguns minutos . Mas não se preocupe,  enviaremos uma mensagem assim que a análise for concluída.");
            
            $copyInvoice = $this->copyLocal($data); 
            if(!$copyInvoice){
                $this->send($data['author'], "*Promo PANCO:* Ops! Houve uma falha ao processar o seu cupom fiscal. Envie novamente, por favor.");
                $out->status = true;
                $out->next = false;

                return $out;
            } else {
                $file = str_replace('/var/www/html/public/', '', $copyInvoice->dirname) . '/' . $copyInvoice->basename;

                $chat->invoice_local = $file;
                
                if(!$chat->save()){
                    $this->send($data['author'], "*Promo PANCO:* Ops! Houve uma falha ao processar o seu cupom fiscal. Envie novamente, por favor.");

                    $out->status = true;
                    $out->next = false;

                    return $out;
                } 
                
                $chat->fresh();    
                
                $googleEnabled = false;
                
                if($googleEnabled === true){

                    $readImage = $this->readInvoiceImage($file);

                    $company_cnpj = preg_replace('/[^\d]/', '', $readImage['cnpj']);

                    // $searchStore = DB::table('stores')->where('store_cnpj', $company_cnpj)->first();

                    if(!validCNPJ($readImage['cnpj'])){
                        $this->send($data['author'], "*Promo PANCO:* Infelizmente o CNPJ na imagem do seu cupom fiscal não é válido ou não está com a leitura necessária para o seu reconhecimento. Sendo assim, digite por favor, o CNPJ que está na parte superior de seu cupom fiscal.");
                        $out->status = true;
                        $out->next = false;
                        return $out;
                    }
                    /*else if(!$searchStore){
                        $this->send($data['author'], "O CNPJ informado não participa da promoção.\n\nDigite, por favor, o CNPJ que está na parte superior do seu cupom fiscal.");
                        $out->status = true;
                        $out->next = false;
                        return $out;
                    }*/
                    else {
                        $chat->company_cnpj = $readImage['cnpj'];
                        if(!$chat->save()){

                            $this->send($data['author'], "*Promo PANCO:* Infelizmente não conseguimos salvar o CNPJ recebido no cupom enviado.\n\nDigite, por favor, o CNPJ que está na parte superior do seu cupom fiscal.");

                            $out->status = true;
                            $out->next = false;

                            return $out;
                            
                        } else {
                            $chat->fresh();
                            $nextStep = $this->discoverNextStep('stepCNPJ');
                            $this->send($data['author'], $json['send'][$nextStep]['text']);

                            $out->status = true;
                            $out->next = false;

                            return $out;
                        }
                    }
                } else {
                    $chat->fresh();
                    $nextStep = $this->discoverNextStep(__FUNCTION__);
                    $this->send($data['author'], $json['send'][$nextStep]['text']);

                    $out->status = true;
                    $out->next = false;

                    return $out;
                }
            }
        } else {
            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao processar o seu cupom. Tente novamente!');

            $out->status = true;
            $out->next = false;

            return $out;                    
        }
    }

    private function stepAmount(&$chat, $json, $data){
        $out = new \stdClass();

        if($chat->amount){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'chat'){
            $this->send($data['author'], '*Promo PANCO:* Você precisa enviar um texto (' . $data['type'] . ')');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $amount = str_replace( array( '.', ',' ), array( ',', '.' ), $data['body'] );

        $amount = preg_replace("/([^\d\.])/", "", $amount);

        if(preg_match("/^\d+(\.\d{1,2})?$/", $amount) == 0){
            $this->send($data['author'], '*Promo PANCO:* Formato de valor inválido. Tente novamente.');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        if($amount > env('PROMO_MAX_AMOUNT')){
            $this->send($data['author'], '*Promo PANCO:* O valor informado ultrapassa o máximo de participações permitidas.');
            $out->status = false;
            $out->next = false;

            return $out; 
        }

        /*
	if($amount < 20){
            $this->send($data['author'], '*Promo PANCO:* O valor mínimo por cupom é de R$ 20,00');
            $out->status = false;
            $out->next = false;

            return $out; 
        }
	*/

        $findUser = User::where('mobile', substr($data['author'], 2))->first();
        if($findUser){
            $sumAmount = Coupon::sumCoupons($findUser->id);
            if($amount + $sumAmount > env('PROMO_MAX_AMOUNT'))
            {
                $this->send($data['author'], '*Promo PANCO:* O valor máximo de participações é de R$ 2.000,00. O seu total até o momento, incluindo este cupom, é: R$ ' . ($amount + $sumAmount) );
                $out->status = false;
                $out->next = false;

                return $out; 
            } 
        }

        $chat->amount = $amount;

        if(!$chat->save()){
            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar o valor do cupom. Digite novamente, por favor.');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();
            $nextStep = $this->discoverNextStep(__FUNCTION__);
            $this->send($data['author'], $json['send'][$nextStep]['text']);

            $out->status = true;
            $out->next = true;

            return $out;
        }
    }

    private function stepCouponNumber(&$chat, $json, $data){
        $out = new \stdClass();

        if($chat->coupon_number){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'chat'){
            $this->send($data['author'], '*Promo PANCO:* Você precisa enviar um texto (' . $data['type'] . ')');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $findCNPJCoupon = DB::table('whatsapp_chat')->where([
            'company_cnpj' => $chat->company_cnpj,
            'coupon_number' => $data['body']
            ])->get()->count();

        if($findCNPJCoupon > 0){
            $this->send($data['author'], '*Promo PANCO:* Este cupom já foi cadastrado');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $chat->coupon_number = $data['body'];
        if(!$chat->save()){
            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar o número do cupom. Digite novamente, por favor.');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();
            $nextStep = $this->discoverNextStep(__FUNCTION__);
            $this->send($data['author'], $json['send'][$nextStep]['text']);

            $out->status = true;
            $out->next = false;

            return $out;
        }
    }

    private function stepProducts(&$chat, $json, $data){
        $out = new \stdClass();

        if($chat->products == 1){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'chat'){
            $this->send($data['author'], 'Você precisa enviar um texto (' . $data['type'] . ')');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $productOpen = DB::table('whatsapp_chat_products')->where(['chat_id' => $chat->id, 'completed' => 0])->first();
        if($productOpen){
            if($productOpen->qty == null){

                if((int) $data['body'] > 99){
                    $this->send($data['author'], '*Promo PANCO:* A quantidade máxima de produtos é 99');

                    $out->status = false;
                    $out->next = false;

                    return $out;
                }

                if((int) $data['body'] < 1){
                    $this->send($data['author'], '*Promo PANCO:* A quantidade mínima de produtos é 1');

                    $out->status = false;
                    $out->next = false;

                    return $out;
                }
                
                $updateChatProduct = DB::table('whatsapp_chat_products')
                                        ->where('chat_id', $chat->id)
                                        ->where('id', $productOpen->id)
                                        ->update([
                                            'qty' => (int) $data['body'],
                                            'completed' => 1
                                        ]);

                if(!$updateChatProduct){
                    $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar a quantidade do produto. Digite novamente, por favor.');

                    $out->status = false;
                    $out->next = false;

                    return $out;
                } else {
                    $this->send($data['author'], '*Promo PANCO:* Produto salvo com sucesso! Há mais produtos participantes  para cadastrar deste mesmo cupom fiscal? Se sim, digite o *número* que identifica o produto, caso contrário, digite *não*');

                    $out->status = true;
                    $out->next = false;

                    return $out;
                }
            }
        } else {
            $productsTotal = DB::table('whatsapp_chat_products')->where(['chat_id' => $chat->id, 'completed' => 1])->get()->count(); 
            if($productsTotal > 0){
                if(strtolower(str_replace(['ã'], 'a', $data['body'])) == 'nao'){
                    // $this->send($data['author'], 'Vai pro último passo');

                    $listProducts = DB::table('whatsapp_chat_products')->where('chat_id', $chat->id)->get()->pluck('product_id')->toArray();

                    // $sumProducts = DB::table('whatsapp_chat_products')->where('chat_id', $chat->id)->get()->sum('qty');
                    $sumProducts = DB::table('whatsapp_chat_products')->where('chat_id', $chat->id)->count();

                    if($sumProducts < 2){

                        $this->send($data['author'], '*Promo PANCO:* O cupom fiscal precisa conter os dois produtos participantes. Caso você queira cancelar o envio deste cupom, digite CANCELAR');
                        
                        $products = Product::where(['status' => 1])->get();
                        $listProductsWhatsapp = [];
                        foreach($products->all() as $itemP){
                            $listProductsWhatsapp[] = '*' .  $itemP->id . '*) ' . $itemP->product . ($itemP->selected == 1 ? '(Obrigatório)' : '');
                        }

                        $this->send($data['author'], $json['send']['stepProducts']['text'], [implode("\n", $listProductsWhatsapp)]);

                
                        $out->status = false;
                        $out->next = false;
            
                        return $out;
                    }

                    if(Product::hasRequired($listProducts)){

                        $chat->products = 1;

                        if(!$chat->save()){
                            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar os produtos. Digite *não* novamente, por favor.');
                
                            $out->status = false;
                            $out->next = false;
                
                            return $out;
                        } else {
                            $chat->fresh();

                            $prizes = DB::table('prizes')->get();

                            $listPrizes = [];
                            foreach($prizes->all() as $itemP){
                                $listPrizes[] = '*' .  $itemP->id . '*) ' . $itemP->prize;
                            }
    
                            $nextStep = $this->discoverNextStep(__FUNCTION__);
                            $this->send($data['author'], $json['send'][$nextStep]['text'], [implode("\n", $listPrizes)]);

                            $out->status = true;
                            $out->next = false;

                            return $out;
                        }
                    } else {
                        $this->send($data['author'], 'Você precisa selecionar o produto obrigatório!');
                        $products = Product::where(['status' => 1])->get();

                        $listProducts = [];
                        foreach($products->all() as $itemP){
                            $listProducts[] = '*' .  $itemP->id . '*) ' . $itemP->product . ($itemP->selected == 1 ? '(Obrigatório)' : '');
                        }

                        $this->send($data['author'], $json['send']['stepProducts']['text'], [implode("\n", $listProducts)]);

                        $out->status = true;
                        $out->next = false;
                    }
                    
                    return $out;
                } else {
                    $findProduct = Product::where('id', $data['body'])->first();

                    if(!$findProduct){

                        $products = Product::where(['status' => 1])->get();

                        $listProducts = [];
                        foreach($products->all() as $itemP){
                            $listProducts[] = '*' .  $itemP->id . '*) ' . $itemP->product;
                        }

                        $this->send($data['author'], "*Promo PANCO:* O produto informado não participa da promoção. Os produtos participantes são: \n" . implode("\n", $listProducts) . "\n" . "Para escolher, digite o número da opção. *Digite um produto por vez*.");

                        $out->status = false;
                        $out->next = false;

                        return $out;
                    } else {
                        $insertProduct = DB::table('whatsapp_chat_products')->insert([
                            'chat_id' => $chat->id,
                            'product_id' => $findProduct->id
                        ]);
        
                        if(!$insertProduct)
                        {
                            $this->send($data['author'], '*Promo PANCO:* Falha ao cadastrar o produto, digite novamente o código.');
        
                            $out->status = false;
                            $out->next = false;
        
                            return $out;
                        } else {
                            $this->send($data['author'], '*Promo PANCO:* Agora digite a quantidade deste produto');

                            $out->status = true;
                            $out->next = false;

                            return $out;
                        }                    
                    }
                }
            } else {

                $findProduct = Product::where('id', $data['body'])->first();

                if(!$findProduct){
                    $products = Product::where(['status' => 1])->get();

                    $listProducts = [];

                    foreach($products->all() as $itemP){
                        $listProducts[] = '*' .  $itemP->id . '*) ' . $itemP->product;
                    }

                    $this->send($data['author'], "Promo Fome de Aprender Panco:* O produto informado não participa da promoção. Os produtos participantes são: \n" . implode("\n", $listProducts) . "\n" . "Para escolher, digite o número da opção. *Digite um produto por vez*.");

                    $out->status = false;
                    $out->next = false;

                    return $out;
                } else {
                    $insertProduct = DB::table('whatsapp_chat_products')->insert([
                        'chat_id' => $chat->id,
                        'product_id' => $findProduct->id
                    ]);
    
                    if(!$insertProduct)
                    {
                        $this->send($data['author'], '*Promo PANCO:* Falha ao cadastrar o produto, digite novamente o código.');
    
                        $out->status = false;
                        $out->next = false;
    
                        return $out;
                    } else {
                        $this->send($data['author'], '*Promo PANCO:* Agora, digite a quantidade comprada deste produto, de acordo com o cupom fiscal.');

                        $out->status = true;
                        $out->next = false;

                        return $out;
                    }                    
                }
            }
        }
    }

    private function stepOneProduct(&$chat, $json, $data){
        $out = new \stdClass();

        if($chat->products == 1){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'chat'){
            $this->send($data['author'], 'Você precisa enviar um texto (' . $data['type'] . ')');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $qtdProduct = (int) $data['body'];

        if((int) $qtdProduct > 99){
            $this->send($data['author'], '*Promo PANCO:* A quantidade máxima de produtos é 99');

            $out->status = false;
            $out->next = false;

            return $out;
        }

        $insertProduct = DB::table('whatsapp_chat_products')->insert([
            'chat_id' => $chat->id,
            'product_id' => Product::where('status', 1)->first()->id,
            'qty' => $qtdProduct,
            'completed' => 1
        ]);

        if(!$insertProduct){
            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar a sua resposta. Tente novamente!');

            $out->status = false;
            $out->next = false;

            return $out;
        }

        $chat->products = 1;

        if(!$chat->save()){
            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar os produtos. Digite *não* novamente, por favor.');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();

            $prizes = DB::table('prizes')->get();

            $listPrizes = [];
            foreach($prizes->all() as $itemP){
                $listPrizes[] = '*' .  $itemP->id . '*) ' . $itemP->prize;
            }

            $nextStep = $this->discoverNextStep(__FUNCTION__);
            $this->send($data['author'], $json['send'][$nextStep]['text'], [implode("\n", $listPrizes)]);

            $out->status = true;
            $out->next = false;

            return $out;
        }
    }

    private function stepPrize(&$chat, $json, $data){
        $out = new \stdClass();

        if($chat->prize_id){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'chat'){
            $this->send($data['author'], '*Promo PANCO:* Você precisa enviar um texto (' . $data['type'] . ')');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $findPrize = DB::table('prizes')->where([
            'id' => $data['body']
            ])->get()->count();

        if($findPrize == 0){
            $this->send($data['author'], '*Promo PANCO:* Este prêmio não existe. Tente novamente!');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $chat->prize_id = $data['body'];
        if(!$chat->save()){
            
            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar o prêmio escolhido. Digite novamente, por favor.');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();
            $nextStep = $this->discoverNextStep(__FUNCTION__);

            $products = Product::where(['status' => 1])->get();

            $listProducts = [];
            foreach($products->all() as $itemP){
                $listProducts[] = '*' .  $itemP->id . '*) ' . $itemP->product;
            }

            $this->send($data['author'], $json['send'][$nextStep]['text'], [implode("\n", $listProducts)]);

            $out->status = true;
            $out->next = false;

            return $out;
        }
    }

    private function stepCNPJ(&$chat, $json, $data){
        $out = new \stdClass();

        if($chat->company_cnpj){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'chat'){
            $this->send($data['author'], '*Promo PANCO:* Digite, por favor, o CNPJ que está na parte superior do seu cupom fiscal.');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $company_cnpj = preg_replace('/[^\d]/', '', $data['body']);

        // $searchStore = DB::table('stores')->where('store_cnpj', $company_cnpj)->first();

        if(!validCNPJ($data['body'])){
            $this->send($data['author'], '*Promo PANCO:* O CNPJ que você digitou não é válido.');

            $out->status = false;
            $out->next = false;

            return $out;
        }
        /* else if(!$searchStore){
            $this->send($data['author'], 'O CNPJ informado não participa da promoção. Tente novamente!');

            $out->status = false;
            $out->next = false;

            return $out;
        }*/
        else {
            $chat->company_cnpj = $data['body'];
            if(!$chat->save()){
                $chat->fresh();

                $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar o CNPJ informado. Digite novamente, por favor');

                $out->status = false;
                $out->next = false;

                return $out;
            } else {
                $nextStep = $this->discoverNextStep(__FUNCTION__);
                $this->send($data['author'], $json['send'][$nextStep]['text']);

                $out->status = true;
                $out->next = false;

                return $out;
            }
        }
    }

    private function stepBuyDate(&$chat, $json, $data){
        $out = new \stdClass();

        if($chat->buy_date){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'chat'){
            $this->send($data['author'], 'Você precisa enviar um texto (' . $data['type'] . ')');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $explodedData = explode('/', $data['body']);

        if(count($explodedData) != 3){
            $this->send($data['author'], '*Promo PANCO:* Qual a data de emissão? Digite no formato DD/MM/AAAA. Por favor não esqueça de colocar as barras separando os números.');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        list($day, $month, $year) = $explodedData;
        $day = (int) $day;
        $month = (int) $month;
        $year = (int) $year;
        
        if(count($explodedData) != 3){
            $this->send($data['author'], '*Promo PANCO:* Qual a data de emissão? Digite no formato DD/MM/AAAA. Por favor não esqueça de colocar as barras separando os números.');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        if(!checkdate(trim($month), trim($day), trim($year))){
            $this->send($data['author'], '*Promo PANCO:* Qual a data de emissão? Digite no formato DD/MM/AAAA. Por favor não esqueça de colocar as barras separando os números.');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $timeDate = mktime(0, 0, 0, trim($month), trim($day), trim($year));
        $timePromo = mktime(0, 0, 0, 9, 1, 2020);

        if($timeDate < $timePromo){
            $this->send($data['author'], '*Promo PANCO:* A data de emissão está fora do período da promoção. Digite novamente.');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $bodyDate = trim($day) .'/'.trim($month).'/'.trim($year);
        $chat->buy_date = dateEN($bodyDate, false);

        if(!$chat->save()){
            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar a data de emissão. Digite novamente, por favor, no formato *DD/MM/YYYY*');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();
            $nextStep = $this->discoverNextStep(__FUNCTION__);
            
            $products = Product::where(['status' => 1])->get();
            $listProducts = [];
            foreach($products->all() as $itemP){
                $listProducts[] = '*' .  $itemP->id . '*) ' . $itemP->product . ($itemP->selected == 1 ? '(Obrigatório)' : '');
            }

            $this->send($data['author'], $json['send'][$nextStep]['text'], [implode("\n", $listProducts)]);
            
            $out->status = true;
            $out->next = false;

            return $out;
        }
    }

    private function stepBuyState(&$chat, $json, $data){
        $out = new \stdClass();

        if($chat->buy_state){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'chat'){
            $this->send($data['author'], '*Promo PANCO:* Você precisa enviar um texto (' . $data['type'] . ')');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        // $validStates = ["AC", "AL", "AM", "AP", "BA", "CE", "DF", "ES", "GO", "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI", "RJ", "RN", "RO", "RS", "RR", "SC", "SE", "SP", "TO"];
        $validStates = ["SP"];

        if(!in_array(strtoupper($data['body']), $validStates)){
            $this->send($data['author'], '*Promo PANCO:* O Estado informado não participar da promoção. Os Estados participantes são: ' . implode(', ', $validStates));
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $chat->buy_state = strtoupper($data['body']);

        if(!$chat->save()){
            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar o Estado em que a compra foi realizada. Digite novamente, por favor.');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();
            $nextStep = $this->discoverNextStep(__FUNCTION__);

            $products = Product::where(['status' => 1])->get();

            $listProducts = [];
            foreach($products->all() as $itemP){
                $listProducts[] = '*' .  $itemP->id . '*) ' . $itemP->product;
            }

            $this->send($data['author'], $json['send'][$nextStep]['text'], [implode("\n", $listProducts)]);

            $out->status = true;
            $out->next = false;

            return $out;
        }
    }

    private function stepRequiredProduct(&$chat, $json, $data){
        $out = new \stdClass();

        if($chat->required_product){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'chat'){
            $this->send($data['author'], '*Promo PANCO:* Você precisa enviar um texto (' . $data['type'] . ')');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        
        if(strtolower($data['body']) != 'sim'){
            $this->send($data['author'], '*Promo PANCO:* Resposta inválida. Você confirma a compra de pelo menos 1 (um) Arroz Broto Legal?');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $chat->required_product = ucfirst(strtolower($data['body']));

        if(!$chat->save()){
            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar sua resposta. Digite novamente, por favor.');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();
            $nextStep = $this->discoverNextStep(__FUNCTION__);

            $this->send($data['author'], $json['send'][$nextStep]['text']);

            $out->status = true;
            $out->next = false;

            return $out;
        }
    }

    private function stepStore(&$chat, $json, $data){

        $out = new \stdClass();

        if($chat->store_id){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'chat'){
            $this->send($data['author'], '*Promo PANCO:* Digite, por favor, o número que representa a loja que você desejar resgatar o seu prêmio.');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $storeID = (int) $data['body'];

        $findStore = DB::table('stores')->where('id', $storeID)->first();
        if(!$findStore){
            $this->send($data['author'], '*Promo PANCO:* A loja informada não participa da promoção');
            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->store_id = $storeID;
            if(!$chat->save()){
                $this->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar a sua resposta. Tente novamente!');
                $out->status = false;
                $out->next = false;

                return $out;
            } else {
                $chat->fresh();
                $nextStep = $this->discoverNextStep(__FUNCTION__);

                $products = Product::where(['status' => 1])->get();
                $listProducts = [];
                foreach($products->all() as $itemP){
                    $listProducts[] = '*' .  $itemP->id . '*) ' . $itemP->product . ($itemP->selected == 1 ? '(Obrigatório)' : '');
                }

                $this->send($data['author'], $json['send'][$nextStep]['text'], [implode("\n", $listProducts)]);

                $out->status = true;
                $out->next = false;

                return $out;
            }
        }
    }


    private function stepLast(&$chat, $json, $data){
        $out = new \stdClass();

        if($chat->status == 1){
            $out->status = true;
            $out->next = false;

            return $out;
        }

        $chat->status = 1;
        if(!$chat->save()){

            $this->send($data['author'], '*Promo PANCO:* Houve um erro ao finalizar o seu cupom. Entre em contato conosco informando o número #' . $chat->id);

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();

            $allowedMobile = [
                '5511996393535',
                '5511999494898'
            ];

            //if(in_array($data['author'], $allowedMobile)){
            if(true){
                $insertWhatsapp = Coupon::validateWhatsappChat($chat);

                if($insertWhatsapp){
                    if(count($insertWhatsapp->luckyNumbers) > 0){
                        $msg = "*Promo PANCO:*  Você está concorrendo com os números da sorte a seguir: " . implode(', ', $insertWhatsapp->luckyNumbers);
                    
                        $this->send($data['author'], $msg);
                    }
                }
                
                if($insertWhatsapp->instantPrize['hasPrize'] === true){
                    $msg = "*Promo PANCO:* Você ganhou um crédito da Picpay valor de *R$ ". $insertWhatsapp->instantPrize['prize']->text_value.",00*.  O seu cupom será validado em até 5 dias úteis. O código interno para nosso controle é: " . $insertWhatsapp->instantPrize['hash'];
                
                    $this->send($data['author'], $msg);
    
                    $instantWinner = true;
    
                } else {
                    $msg = "Você *não* ganhou um prêmio instantâneo dessa vez, mas continue participando!";
                    // $msg = "*Promo PANCO:* Premiação instantânea encerrada, prêmios esgotados!";
                
                    $this->send($data['author'], $msg);
                }
            }

            $this->send($data['author'], "*Promo PANCO:* Parabéns, o seu cupom foi recebido com sucesso. Caso queira acompanhar a sua participação, entre no site https://promopanco.com.br e faça o login com este mesmo número de WhatsApp e acesse a página \"Minha Participação\". No link acima você também terá acesso aos regulamentos e demais informações da ação.");


            

            ################################################
            # QUANDO HOUVER PERGUNTA DEPOIS DA FINALIZAÇÃO #
            ################################################

            /*
            if($instantWinner === true){
                $chat->fresh();
                $nextStep = $this->discoverNextStep(__FUNCTION__);

                $stores = Store::where(['status' => 1])->get();

                $listStores = [];
                foreach($stores->all() as $itemS){
                    $listStores[] = '*' .  $itemS->id . '*) ' . $itemS->store_name;
                }

                $this->send($data['author'], $json['send'][$nextStep]['text'], [implode("\n", $listStores)]);

                $out->status = true;
                $out->next = false;

                return $out;
            }
            */

            #################################################
            # /QUANDO HOUVER PERGUNTA DEPOIS DA FINALIZAÇÃO #
            #################################################


            $out->status = true;
            $out->next = false;

            return $out;
        }
    }

    private function handleWhatsappUser($json, $data){
        
        $findWhatsappUser = WhatsappUser::where(['mobile' => preg_replace("/^\d{2}/", "", $data->input('contact.uid'))])->first();

        if($findWhatsappUser){      
            if($findWhatsappUser->name === null){
                $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                $whatsappUserModel->name = $data->input('message.body.text');
                if($whatsappUserModel->save()){
                    $this->send($data->input('contact.uid'), '*Promo PANCO:* Agora, para continuar o seu cadastro em nosso WhatsApp, digite o seu email.');
                } else {
                    $this->send($data->input('contact.uid'), '*Promo PANCO:* Houve um problema ao salvar o seu e-mail. Tente novamente!');
                }
            } else if($findWhatsappUser->email === null){
                if(filter_var($data->input('message.body.text'), FILTER_VALIDATE_EMAIL) === false){
                    $this->send($data->input('contact.uid'), '*Promo PANCO:* O e-mail informado é inválido. Nos informe um e-mail válido para finalizar o seu cadastro em nosso Whatsapp.');
                } else {
                    
                    $findWhatsappEmail = WhatsappUser::where(['email' => $data->input('message.body.text')])->get();

                    if(count($findWhatsappEmail) > 0){
                        $this->send($data->input('contact.uid'), '*Promo PANCO:* O e-mail informado já está atrelado a um outro número de celular. Por favor, escolha outro e-mail.');
                    } else {
                        $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                        $whatsappUserModel->email = strtolower($data->input('message.body.text'));
                        if($whatsappUserModel->save()){
                            $this->send($data->input('contact.uid'), "*Promo PANCO:* O seu e-mail foi cadastrado com sucesso! Agora, nos informe, por favor, o seu CPF");
                        } else {
                            $this->send($data->input('contact.uid'), '*Promo PANCO:* Houve um problema ao salvar o seu e-mail. Tente novamente!');
                        }
                    }
                }              
            } else if($findWhatsappUser->cpf == null){
                $cpf = preg_replace('/[^\d]/', '', $data->input('message.body.text'));

                $findWhatsappCPF = WhatsappUser::where(['cpf' => $cpf])->get();

                if(count($findWhatsappCPF) > 0){
                    $this->send($data->input('contact.uid'), '*Promo PANCO:* O CPF informado já está atrelado a um outro número de celular.');
                } else if(!validCPF($cpf)){
                    $this->send($data->input('contact.uid'), '*Promo PANCO:* O CPF informado é inválido.');
                } else {
                    $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                    $whatsappUserModel->cpf = $cpf;
                    if($whatsappUserModel->save()){
                        $this->send($data->input('contact.uid'), '*Promo PANCO:* CPF cadastrado com sucessso. Informe o seu Estado');
                    } else {
                        $this->send($data->input('contact.uid'), '*Promo PANCO:* Houve um problema ao salvar o seu CPF. Tente novamente!');
                    }
                }
            } else if($findWhatsappUser->state == null){
                $state = strtoupper($data->input('message.body.text'));

                $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                // $validStates = ["AC", "AL", "AM", "AP", "BA", "CE", "DF", "ES", "GO", "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI", "RJ", "RN", "RO", "RS", "RR", "SC", "SE", "SP", "TO"];
                $validStates = ["SP"];

                if(!in_array($state, $validStates)){
                    $this->send($data->input('contact.uid'), '*Promo PANCO:* O Estado informado não participa da promoção. Os Estados participantes são: ' . implode(', ', $validStates));
                } else {
                    $whatsappUserModel->state = $state;

                    if($whatsappUserModel->save()){
                        $this->send($data->input('contact.uid'), '*Promo PANCO:* Estado cadastrado com sucesso. Informe, agora, a cidade.');
                    } else {
                        $this->send($data->input('contact.uid'), '*Promo PANCO:* Houve um problema ao salvar o seu Estado. Tente novamente!');
                    }
                }
            } else if($findWhatsappUser->city == null){
                $city = $data->input('message.body.text');

                $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                $onlyCapital = array('PE', 'CE', 'GO', 'SC', 'PR', 'RS');

                $errorCity = false;

                /*
                if(in_array($whatsappUserModel->state, $onlyCapital)){
                    if($whatsappUserModel->state == 'PE'){
                        if(strtolower($city) != 'recife'){
                            $errorCity = true;
                            $currentCity = 'Recife';
                        }
                    } else if($whatsappUserModel->state == 'CE'){
                        if(strtolower($city) != 'fortaleza'){
                            $errorCity = true;
                            $currentCity = 'Fortaleza';
                        }
                    } else if($whatsappUserModel->state == 'GO'){
                        if(strtolower($city) != 'goiania' && strtolower($city) != 'goiânia'){
                            $errorCity = true;
                            $currentCity = 'Goiânia';
                        }
                    } else if($whatsappUserModel->state == 'SC'){
                        if(strtolower($city) != 'florianopolis' && strtolower($city) != 'florianópolis'){
                            $errorCity = true;
                            $currentCity = 'Florianópolis';
                        }
                    } else if($whatsappUserModel->state == 'PR'){
                        if(strtolower($city) != 'curitiba'){
                            $errorCity = true;
                            $currentCity = 'Curitiba';
                        }
                    } else if($whatsappUserModel->state == 'R'){
                        if(strtolower($city) != 'porto alegre'){
                            $errorCity = true;
                            $currentCity = 'Porto Alegre';
                        }
                    }
                }
                */

                if($errorCity === true){
                    $this->send($data->input('contact.uid'), '*Promo PANCO:* Para o Estado de '. $whatsappUserModel->state .', a única cidade participante é '. $currentCity );
                } else {
                    $whatsappUserModel->city = $city;

                    if($whatsappUserModel->save()){
                        $this->send($data->input('contact.uid'), '*Promo PANCO:* Cidade cadastrada com sucesso. Informe sua data de nascimento no formato DD/MM/AAAA');
                    } else {
                        $this->send($data->input('contact.uid'), '*Promo PANCO:* Houve um problema ao salvar o seu Estado. Tente novamente!');
                    }
                }
            } else if($findWhatsappUser->birth_date == null){

                $explodedDate = explode('/', $data->input('message.body.text'));
                
                if(count($explodedDate) != 3){
                    $this->send($data->input('contact.uid'), '*Promo Fome de Aprender Panco*: A data informada é inválida. Para continuarmos, poderia nos informar sua data de nascimento? Digite no formato DD/MM/AAAA e não esqueça de colocar as / entre os números');
                    return;
                }

                $birthDate = dateEN($data->input('message.body.text'), false);


                list($day, $month, $year) = $explodedDate;

                $day = (int) $day;
                $month = (int) $month;
                $year = (int) $year;

                if(count($explodedDate) != 3){
                    $this->send($data->input('contact.uid'), '*Promo PANCO:* A data informada é inválida. Para continuarmos, poderia nos informar sua data de nascimento? Digite no formato DD/MM/AAAA e não esqueça de colocar as / entre os números');
                } else {

                    $d1 = new \DateTime($birthDate);
                    $d2 = new \DateTime(date('Y-m-d'));

                    $interval = $d1->diff($d2);

                    if(!checkdate(trim($month), trim($day), trim($year))){
                        $this->send($data->input('contact.uid'), '*Promo PANCO:* Data inválida. A data deve ser no formato *DD/MM/AAAA*.');
                    } else if($interval->y < 18){
                        $this->send($data->input('contact.uid'), '*Promo PANCO:* Você precisa ter mais de 18 anos para participar.');
                    } else {
                        $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                        $whatsappUserModel->birth_date = $birthDate;

                        if($whatsappUserModel->save()){
                            $this->send($data->input('contact.uid'), '*Promo PANCO:* Digite uma senha para acesso ao site.');
                        } else {
                            $this->send($data->input('contact.uid'), '*Promo PANCO:* Houve um problema ao salvar a sua data de nascimento. Tente novamente!');
                        }
                    }
                }
            } else if($findWhatsappUser->password == null){
                $password = $data->input('message.body.text');

                $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                $whatsappUserModel->password = Hash::make($password);

                if($whatsappUserModel->save()){
                    $this->send($data->input('contact.uid'), '*Promo PANCO:* Você concorda com os termos deste regulamento?  Responda SIM ou NÃO. Consulte o regulamento em https://promopanco.com.br. Para estar habilitado a participar da “Promoção VONO Dinheiro na Mão – 2021 Prêmios” , o usuário concorda expressamente com os termos do Regulamento Interno, em específico quanto ao consentimento dado em relação ao fornecimento espontâneo de seus dados pessoais, os quais serão coletados, tratados e armazenados conforme regras do Regulamento Interno e da Política de Privacidade da Broto legal Alimentos S.A. Se concordar digite SIM');
                } else {
                    $this->send($data->input('contact.uid'), '*Promo PANCO:* Houve um problema ao salvar a sua senha. Tente novamente!');
                }
            } else if($findWhatsappUser->agree_regulation == null){

                $company_cnpj = preg_replace('/[^\d]/', '', $data->input('message.body.text'));

                if(strtolower($data->input('message.body.text')) != 'sim'){
                    $this->send($data->input('contact.uid'), '*Promo PANCO:* Para dar continuidade à sua participação, você deve aceitar os termos do Regulamento. Você concorda com os termos deste regulamento? Você precisa responder SIM para continuar o seu cadastro.');
                } else {
                    $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                    $whatsappUserModel->agree_regulation = 1;
                    $whatsappUserModel->completed = 1;

                    if($whatsappUserModel->save()){                       
                        $this->send($data->input('contact.uid'), '*Promo PANCO:* Ótimo. Finalizamos o seu cadastro!');
                        $this->send($data->input('contact.uid'), $json['send']['cadastro-sucesso']['text']);
                    } else {
                        $this->send($data->input('contact.uid'), '*Promo PANCO:* Houve um problema ao salvar a sua resposta. Tente novamente!');
                    }
                }
            } else {
                $this->send($data->input('contact.uid'), '*Promo PANCO:* Você já está cadastrado em nosso Whatsapp. Agora, para enviar um cupom, digite CUPOM!');
            }
        } else {
            $this->send($data->input('contact.uid'), $json['send']['hello']['text']);
        }
    }

    private function discoverNextStep($current){
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

    private function runSteps($body, $json, $data){
        
        $chatData['id_message'] = $data->input('message.uid');
        $chatData['body'] = $body;
        $chatData['type'] = $data->input('message.type');
        $chatData['sender_name'] = $data->input('contact.name');
        $chatData['from_me'] = ($data->input('message.dir') == 'o') ? 1 : 0;
        $chatData['author'] = $data->input('contact.uid');
        $chatData['time'] = date('Y-m-d H:i:s', time() );
        $chatData['chat_id'] = $data->input('message.cuid');
        $chatData['messageNumber'] = NULL;

        $mobile = $data->input('contact.uid');
        $mobileClean = preg_replace("/^\d{2}/", "", $mobile);

        $listMobile['full'] = $mobile;
        $listMobile['clean'] = $mobileClean;


        if($chatData['from_me'] == 0){
            DB::transaction(function () use ($data, $json, $listMobile, $chatData) {
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
                    $this->handleWhatsappUser($json, $data);
                }
            });
        }
    }
}
