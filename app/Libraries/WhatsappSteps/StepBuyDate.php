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

class StepBuyDate {

    private static $step = 'stepBuyDate';

    public static function init(&$chat, $json, $data){
        $out = new \stdClass();

        $whatsappHandler = new ZenviaWhatsapp();

        if($chat->buy_date){
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

        $explodedData = explode('/', $data['body']);

        if(count($explodedData) != 3){
            $whatsappHandler->send($data['author'], '*Pomo Panco:* Qual a data de emissão? Digite no formato DD/MM/AAAA. Por favor não esqueça de colocar as barras separando os números.');
            $out->status = false;
            $out->next = false;

            return $out;
        }


        list($day, $month, $year) = $explodedData;
        $day = (int) $day;
        $month = (int) $month;
        $year = (int) $year;
        
        if(!checkdate(trim($month), trim($day), trim($year))){
            $whatsappHandler->send($data['author'], '*Pomo Panco:* Qual a data de emissão? Digite no formato DD/MM/AAAA. Por favor não esqueça de colocar as barras separando os números.');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $timeDate = mktime(0, 0, 0, trim($month), trim($day), trim($year));
        $timePromo = mktime(0, 0, 0, 1, 10, 2022);

        if($timeDate < $timePromo){
            $whatsappHandler->send($data['author'], '*Pomo Panco:* A data de emissão está fora do período da promoção. Digite novamente. Caso queira cancelar o envio desse cupom, digite Cancelar.');
            $out->status = false;
            $out->next = false;

            return $out;
        }

        $bodyDate = trim($day) .'/'.trim($month).'/'.trim($year);
        $chat->buy_date = dateEN($bodyDate, false);

        if(!$chat->save()){
            $whatsappHandler->send($data['author'], '*Pomo Panco:* Houve um erro ao salvar a data de emissão. Digite novamente, por favor, no formato *DD/MM/YYYY*.');

            $out->status = false;
            $out->next = false;

            return $out;
        } else {
            $chat->fresh();
            $nextStep = $whatsappHandler->discoverNextStep(self::$step);
            
            $products = Product::where(['status' => 1])->get();

            $listProducts = [];
            foreach($products->all() as $itemP){
                $listProducts[] = '*' .  Product::maskProductID($itemP->id, 'out') . '*) ' . $itemP->product;
            }

            $whatsappHandler->send($data['author'], $json['send'][$nextStep]['text'], [implode("\n", $listProducts)]);
            
            $out->status = true;
            $out->next = false;

            return $out;
        }
    }
}