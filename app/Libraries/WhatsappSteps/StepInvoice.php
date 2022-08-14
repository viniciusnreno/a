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

class StepInvoice {
    private static $step = 'stepInvoice';

    public static function init(&$chat, $json, $data){
        // Log::debug('Join: ZenviaWhatsapp::stpInvoice() data is: ' . json_encode($data));

        $out = new \stdClass();

        $whatsappHandler = new ZenviaWhatsapp();

        if($chat->invoice && $chat->invoice_local){
            $out->status = true;
            $out->next = true;

            return $out;
        }    

        if($data['type'] != 'file'){
            $whatsappHandler->send($data['author'], '*Promo PANCO:* Você precisa enviar uma imagem legível do seu cupom fiscal.');

            $out->status = false;
            $out->next = false;

            return $out;
        }
    
        $chat->invoice = $data['body'];

        if($chat->save()){
            $chat->fresh();
            // $whatsappHandler->send($data['author'], "*Promo PANCO:* Ótimo! O seu cupom está sendo analisado e isso pode levar alguns minutos . Mas não se preocupe,  enviaremos uma mensagem assim que a análise for concluída.");
            
            $copyInvoice = self::copyLocal($data); 
            if(!$copyInvoice){
                $whatsappHandler->send($data['author'], "*Promo PANCO:* Ops! Houve uma falha ao processar o seu cupom fiscal. Envie novamente, por favor.");
                $out->status = true;
                $out->next = false;

                return $out;
            } else {
                $file = str_replace('/var/www/html/storage/public', '', $copyInvoice->dirname) . '/' . $copyInvoice->basename;

                $chat->invoice_local = $file;
                
                if(!$chat->save()){
                    $whatsappHandler->send($data['author'], "*Promo PANCO:* Ops! Houve uma falha ao processar o seu cupom fiscal. Envie novamente, por favor.");

                    $out->status = true;
                    $out->next = false;

                    return $out;
                } 
                
                $chat->fresh();  
                

                $nextStep = $whatsappHandler->discoverNextStep(self::$step);
                $whatsappHandler->send($data['author'], $json['send'][$nextStep]['text']);

                $out->status = true;
                $out->next = false;

                return $out;
            }
        } else {
            $whatsappHandler->send($data['author'], 'Houve um erro ao processar o seu cupom. Tente novamente!');

            $out->status = true;
            $out->next = false;

            return $out;                    
        }
    }

    private static function copyLocal($data){

        sleep(2);

        $rootDir = '/var/www/html/storage/app/';
        $pathDir = 'public/_upload/';

        switch($data['mimeType']){
            case 'image/jpeg': $extension = '.jpg'; break;
            case 'image/png': $extension = '.png'; break;
            default: $extension = '.jpg';
        }

        $filename = basename(str_replace('.bin', $extension, $data['body']));
        $filepath = Storage::disk('local')->exists($pathDir . $filename);

        if(file_exists($filepath)){
            $filename = time() . '_' . $filename;
        }
        
        // $imgHandler = new Image();

        try {
            // Log::debug('Imagem: ' . $pathDir . $filename);
            Storage::put($pathDir . $filename, file_get_contents($data['body']));
            $imgHandler = new Image();
            $img = $imgHandler->make($rootDir . $pathDir . $filename);
        } catch (Exception $e){
            $whatsappHandler->send($data['author'], 'Houve um erro ao ler a sua imagem.');
        }

        return $img->save($rootDir . $pathDir . $filename);
        
    }

    private function readInvoiceImage($file){
        $ocr = new OCR();

        $response = $ocr->getText($file);

        preg_match('/\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}/', $response, $getCNPJ);

        $data['cnpj'] = trim(preg_replace('/[^\d]/', '', $getCNPJ[0]));

        return $data;
    }
}