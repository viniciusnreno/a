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

class StepCNPJ {
    private static $step = 'stepCNPJ';

    public static function init(&$chat, $json, $data){
        $out = new \stdClass();

        $whatsappHandler = new ZenviaWhatsapp();

        if($chat->company_cnpj){
            $out->status = true;
            $out->next = true;

            return $out;
        }

        if($data['type'] != 'text'){
            $whatsappHandler->send($data['author'], '*Promo PANCO:* Digite, por favor, o CNPJ que está na parte superior do seu cupom fiscal.');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $company_cnpj = preg_replace('/[^\d]/', '', $data['body']);

        // $searchStore = DB::table('stores')->where('store_cnpj', $company_cnpj)->first();

        if(!validCNPJ($data['body'])){
            $whatsappHandler->send($data['author'], '*Promo PANCO:* O CNPJ informado não é válido. Tenta novamente. Lembre-se: digite apenas os números.');

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

                $whatsappHandler->send($data['author'], '*Promo PANCO:* Houve um erro ao salvar o CNPJ informado. Digite novamente, por favor.');

                $out->status = false;
                $out->next = false;

                return $out;
            } else {
                $nextStep = $whatsappHandler->discoverNextStep(self::$step);
                $whatsappHandler->send($data['author'], $json['send'][$nextStep]['text']);

                $out->status = true;
                $out->next = false;

                return $out;
            }
        }
    }
}