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

class StepSimpleProduct {
    private static $step = 'stepSimpleProduct';

    public static function init(&$chat, $json, $data){
        $out = new \stdClass();

        $whatsappHandler = new ZenviaWhatsapp();

        $searchProduct = DB::table('whatsapp_chat_products')->where('chat_id', $chat->id)->count();
        if($searchProduct >= 1){
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

        $qtd = (int) $data['body'];

        if($qtd < env('PROMO_MIN_PRODUCTS')){
            $whatsappHandler->send($data['author'], 'A quantidade mínima de produtos é ' . env('PROMO_MIN_PRODUCTS'));
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $simpleProduct = DB::table('whatsapp_chat_products')->insert([
            'chat_id' => $chat->id,
            'product_id' => 1,
            'qty' => $qtd,
            'completed' => 1
        ]);

        if(!$simpleProduct){
            $whatsappHandler->send($data['author'], '*Promo PANCO*: Houve um erro ao salvar a quantidade de produtos. Digite novamente, por favor.');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();
            $nextStep = $whatsappHandler->discoverNextStep(self::$step);
            $whatsappHandler->send($data['author'], $json['send'][$nextStep]['text']);

            $out->status = true;
            $out->next = false;

            return $out;
        }
    }
}
