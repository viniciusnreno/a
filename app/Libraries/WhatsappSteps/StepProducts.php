<?php
namespace App\Libraries\WhatsappSteps;

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
use App\Libraries\ZenviaWhatsapp;
use App\Libraries\OCR;
use App\Mail\RegisterCoupon;
use Intervention\Image\ImageManager as Image;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\RequestInterface;
use Illuminate\Support\Facades\Log;

use Dflydev\DotAccessData\Data;

class StepProducts {
    private static $step = 'stepProducts';

    public static function init(&$chat, $json, $data){
        $out = new \stdClass();

        $whatsappHandler = new ZenviaWhatsapp();

        if($chat->products == 1){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'text'){
            $whatsappHandler->send($data['author'], 'Você precisa enviar um texto (' . $data['type'] . ')');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $productOpen = DB::table('whatsapp_chat_products')->where(['chat_id' => $chat->id, 'completed' => 0])->first();
        if($productOpen){
            if($productOpen->qty == null){

                if((int) $data['body'] > 99){
                    $whatsappHandler->send($data['author'], '*Promo PANCO*: A quantidade máxima de produtos é 99.');

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
                    $whatsappHandler->send($data['author'], '*Promo PANCO*: Houve um erro ao salvar a quantidade do produto. Digite novamente, por favor.');

                    $out->status = false;
                    $out->next = false;

                    return $out;
                } else {
                    $whatsappHandler->send($data['author'], '*Promo PANCO*: Produto salvo com sucesso! Há mais produtos participantes  para cadastrar deste mesmo cupom fiscal? Se sim, digite a *letra* do produto que corresponde ao produto participante adquirido, caso contrário, digite *não*.');

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

                    $sumProducts = DB::table('whatsapp_chat_products')->where('chat_id', $chat->id)->get()->sum('qty');

                    if($sumProducts < env('PROMO_MIN_PRODUCTS')){

                        $whatsappHandler->send($data['author'], '*Promo PANCO*: O cupom fiscal precisa conter pelo menos '.env('PROMO_MIN_PRODUCTS').' produtos Panco. Caso você queira cancelar o envio deste cupom, digite CANCELAR.');
                        
                        $products = Product::where(['status' => 1])->get();
                        $listProductsWhatsapp = [];
                        foreach($products->all() as $itemP){
                            $listProductsWhatsapp[] = '*' .  Product::maskProductID($itemP->id, 'out') . '*) ' . $itemP->product . ($itemP->selected == 1 ? '(Obrigatório)' : '');
                        }

                        $whatsappHandler->send($data['author'], $json['send']['stepProducts']['text'], [implode("\n", $listProductsWhatsapp)]);

                
                        $out->status = false;
                        $out->next = false;
            
                        return $out;
                    }

                    if(Product::hasRequired($listProducts)){

                        $chat->products = 1;

                        if(!$chat->save()){
                            $whatsappHandler->send($data['author'], '*Promo PANCO*: Houve um erro ao salvar os produtos. Digite *não* novamente, por favor.');
                
                            $out->status = false;
                            $out->next = false;
                
                            return $out;
                        } else {
                            $chat->fresh();

                            $prizes = DB::table('prizes')->get();

                            $listPrizes = [];
                            foreach($prizes->all() as $itemP){
                                $listPrizes[] = '*' .  Product::maskProductID($itemP->id, 'out') . '*) ' . $itemP->prize;
                            }
    
                            $nextStep = $whatsappHandler->discoverNextStep(self::$step);
                            $whatsappHandler->send($data['author'], $json['send'][$nextStep]['text'], [implode("\n", $listPrizes)]);

                            $out->status = true;
                            $out->next = false;

                            return $out;
                        }
                    } else {
                        $whatsappHandler->send($data['author'], 'Você precisa selecionar o produto obrigatório!');
                        $products = Product::where(['status' => 1])->get();

                        $listProducts = [];
                        foreach($products->all() as $itemP){
                            $listProducts[] = '*' .  Product::maskProductID($itemP->id, 'out') . '*) ' . $itemP->product . ($itemP->selected == 1 ? '(Obrigatório)' : '');
                        }

                        $whatsappHandler->send($data['author'], $json['send']['stepProducts']['text'], [implode("\n", $listProducts)]);

                        $out->status = true;
                        $out->next = false;
                    }
                    
                    return $out;
                } else {
                    $findProduct = Product::where('id', Product::maskProductID($data['body'], 'in'))->first();

                    if(!$findProduct){

                        $products = Product::where(['status' => 1])->get();

                        $listProducts = [];
                        foreach($products->all() as $itemP){
                            $listProducts[] = '*' .  Product::maskProductID($itemP->id, 'out') . '*) ' . $itemP->product;
                        }

                        $whatsappHandler->send($data['author'], "*Promo PANCO*: O produto informado não participa da promoção. Os produtos participantes são: \n" . implode("\n", $listProducts) . "\n" . "Para escolher, digite a letra da opção. *Digite um produto por vez*.");

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
                            $whatsappHandler->send($data['author'], '*Promo PANCO*: Falha ao cadastrar o produto, digite novamente o código.');
        
                            $out->status = false;
                            $out->next = false;
        
                            return $out;
                        } else {
                            $whatsappHandler->send($data['author'], '*Promo PANCO*: Agora digite a quantidade deste produto.');

                            $out->status = true;
                            $out->next = false;

                            return $out;
                        }                    
                    }
                }
            } else {

                $findProduct = Product::where('id', Product::maskProductID($data['body'], 'in'))->first();

                if(!$findProduct){
                    $products = Product::where(['status' => 1])->get();

                    $listProducts = [];

                    foreach($products->all() as $itemP){
                        $listProducts[] = '*' .  Product::maskProductID($itemP->id, 'out') . '*) ' . $itemP->product;
                    }

                    $whatsappHandler->send($data['author'], "*Promo PANCO*: O produto informado não participa da promoção. Os produtos participantes são: \n" . implode("\n", $listProducts) . "\n" . "Para escolher, digite a letra da opção. *Digite um produto por vez*.");

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
                        $whatsappHandler->send($data['author'], '*Promo PANCO*: Falha ao cadastrar o produto, digite novamente o código.');
    
                        $out->status = false;
                        $out->next = false;
    
                        return $out;
                    } else {
                        $whatsappHandler->send($data['author'], '*Promo PANCO*: Agora, digite a quantidade comprada deste produto, de acordo com o cupom fiscal.');

                        $out->status = true;
                        $out->next = false;

                        return $out;
                    }                    
                }
            }
        }
    }
}