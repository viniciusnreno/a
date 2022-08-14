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

class StepPrize {
    private static $step = 'stepPrize';

    public static function init(&$chat, $json, $data){
        $out = new \stdClass();

        $whatsappHandler = new ZenviaWhatsapp();    

        if($chat->prize_id){
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

        $findPrize = DB::table('prizes')->where([
            'id' => $data['body']
            ])->get()->count();

        if($findPrize == 0){
            $whatsappHandler->send($data['author'], '*Promo PANCO*: Este prêmio não existe. Tente novamente!');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $chat->prize_id = $data['body'];
        if(!$chat->save()){
            
            $whatsappHandler->send($data['author'], '*Promo PANCO*: Houve um erro ao salvar o prêmio escolhido. Digite novamente, por favor.');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();
            $nextStep = $whatsappHandler->discoverNextStep(self::$step);

            $this->send($data['author'], $json['send'][$nextStep]['text']);

            $out->status = true;
            $out->next = false;

            return $out;
        }
    }
}