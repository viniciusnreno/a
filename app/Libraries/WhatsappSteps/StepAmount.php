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
use App\Models\Store;
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

class StepAmount {
    private static $step = 'stepAmount';

    public static function init(&$chat, $json, $data){
        $out = new \stdClass();

        $whatsappHandler = new ZenviaWhatsapp();   

        if($chat->amount){
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

        $amount = str_replace( array( '.', ',' ), array( ',', '.' ), $data['body'] );

        $amount = preg_replace("/([^\d\.])/", "", $amount);

        if(preg_match("/^\d+(\.\d{1,2})?$/", $amount) == 0){
            $whatsappHandler->send($data['author'], '*Promo PANCO*: Formato de valor inválido. Tente novamente!');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        /*
        if($amount > env('MAX_AMOUNT')){
            $whatsappHandler->send($data['author'], '*Promo PANCO*: O valor informado ultrapassa o máximo de participações permitidas.');
            $out->status = false;
            $out->next = false;

            return $out; 
        }
        */

        if($amount > env('PROMO_MAX_AMOUNT')){
            $whatsappHandler->send($data['author'], '*Promo PANCO*: O valor informado ultrapassa o valor máximo permitido de R$ ' . env('PROMO_MAX_AMOUNT') . '.');
            $out->status = false;
            $out->next = false;

            return $out; 
        }

        if($amount < env('PROMO_PRIZE_VALUE_AMOUNT')){
            $whatsappHandler->send($data['author'], '*Promo PANCO*: O valor mínimo por cupom é de R$ ' . env('PROMO_PRIZE_VALUE_AMOUNT'));
            $out->status = false;
            $out->next = false;

            return $out; 
        }

        $findUser = User::where('mobile', substr($data['author'], 2))->first();
        if($findUser){
            /*
            $sumAmount = Coupon::sumCoupons($findUser->id);
            if($amount + $sumAmount > env('MAX_AMOUNT'))
            {
                $whatsappHandler->send($data['author'], '*Promo PANCO*: O valor máximo de participações é de R$ 2.000,00. O seu total até o momento, incluindo este cupom, é: R$ ' . ($amount + $sumAmount) );
                $out->status = false;
                $out->next = false;

                return $out; 
            } 
            */
        }

        $chat->amount = $amount;

        if(!$chat->save()){
            $whatsappHandler->send($data['author'], '*Promo PANCO*: Houve um erro ao salvar o valor do cupom. Digite novamente, por favor.');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();
            $nextStep = $whatsappHandler->discoverNextStep(self::$step);

            $stores = Store::where(['status' => 1])->get();

            $listStores = [];
            foreach($stores->all() as $itemS){
                $listStores[] = '*' .  $itemS->id . '*) ' . $itemS->store_name;
            }

            $whatsappHandler->send($data['author'], $json['send'][$nextStep]['text'], [implode("\n", $listStores)]);

            $out->status = true;
            $out->next = true;

            return $out;
        }
    }
}