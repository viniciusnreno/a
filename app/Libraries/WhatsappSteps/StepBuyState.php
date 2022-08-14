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

class StepBuyState {
    private static $step = 'stepBuyState';

    public static function init(&$chat, $json, $data){
        $out = new \stdClass();

        $whatsappHandler = new ZenviaWhatsapp();    

        if($chat->buy_state){
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

        // $validStates = ["AC", "AL", "AM", "AP", "BA", "CE", "DF", "ES", "GO", "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI", "RJ", "RN", "RO", "RS", "RR", "SC", "SE", "SP", "TO"];
        $validStates = ["SP", "RJ", "MG", "PR"];

        if(!in_array(strtoupper($data['body']), $validStates)){
            $whatsappHandler->send($data['author'], 'O Estado informado não participar da promoção. Os Estados participantes são: ' . implode(', ', $validStates) . '.');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $chat->buy_state = strtoupper($data['body']);

        if(!$chat->save()){
            $whatsappHandler->send($data['author'], 'Houve um erro ao salvar o Estado em que a compra foi realizada. Digite novamente, por favor.');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();
            $nextStep = $whatsappHandler->discoverNextStep(self::$step);

            $products = Product::where(['status' => 1])->get();

            $listProducts = [];
            foreach($products->all() as $itemP){
                $listProducts[] = '*' .  $itemP->id . '*) ' . $itemP->product;
            }

            $whatsappHandler->send($data['author'], $json['send'][$nextStep]['text'], [implode("\n", $listProducts)]);

            $out->status = true;
            $out->next = false;

            return $out;
        }
    }
}