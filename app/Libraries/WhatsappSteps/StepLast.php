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
use App\Mail\CNAMini;
use Intervention\Image\ImageManager as Image;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\RequestInterface;
use Illuminate\Support\Facades\Log;

use Dflydev\DotAccessData\Data;

class StepLast {
    private static $step = 'stepLast';

    public static function init(&$chat, $json, $data){
        $out = new \stdClass();

        $whatsappHandler = new ZenviaWhatsapp();   

        if($chat->status == 1){
            $out->status = true;
            $out->next = false;

            return $out;
        }

        $chat->status = 1;
        if(!$chat->save()){

            $whatsappHandler->send($data['author'], 'Houve um erro ao finalizar o seu cupom. Entre em contato conosco informando o número #' . $chat->id . '.');

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
                    if(env('PROMO_EMAIL_ENABLED') === true){
                        $user = DB::table('whatsapp_users')->where('id', $chat->whatsappuser_id)->first();
                        // Log::debug('Mail: data is: ' . json_encode($user));
                        /*
                        if($user){
                            Mail::to($user->email)->send(new RegisterCoupon([]));
                        }
                        */

                        if($insertWhatsapp->coupon->cna_mini_curso == 1){
                            // Envia o email do curso de inglês
                            Mail::to($user->email)->send(new CNAMini([]));
                        } else {
                            Mail::to($user->email)->send(new RegisterCoupon([]));
                        }
                    }
                }
            }

            $whatsappHandler->send($data['author'], "*Promo PANCO*: Parabéns, o seu cupom foi recebido com sucesso. Em caso de cupom premiado, iremos valida-lo em até 5 dias úteis, por isso fique atento também à sua caixa de SPAM. Você pode acompanhar a sua participação, entre no site https://promopanco.com.br e faça o login com este mesmo número de WhatsApp e acesse a página \"Minha Participação\". No link acima você também terá acesso aos regulamentos e demais informações da ação.");


            if(env('PROMO_HAS_LUCKY_NUMBERS') === true){
                if(count($insertWhatsapp->luckyNumbers) > 0){
                    $msg = "*Promo PANCO*: Você já está concorrendo com os números da sorte a seguir: " . implode(', ', $insertWhatsapp->luckyNumbers) . ".";
                
                    $whatsappHandler->send($data['author'], $msg);
                }
            }
            
            if(env('PROMO_HAS_INSTANT_PRIZE') === true){

                if($insertWhatsapp->instantPrize['hasPrize'] === true){
                    $msg = "Você ganhou um prêmio instantâneo no valor de *R$ ". $insertWhatsapp->instantPrize['prize']->text_value.",00*. A chave para resgate é: " . $insertWhatsapp->instantPrize['hash'] . ".";
                
                    $whatsappHandler->send($data['author'], $msg);
                } else {
                    $msg = "Você *não* ganhou um prêmio instantâneo dessa vez, mas continue participando!";
                    // $msg = "Premiação instantânea encerrada, prêmios esgotados!";
                
                    $whatsappHandler->send($data['author'], $msg);
                }

                // $whatsappHandler->send($data['author'], 'Promoção instantânea encerrada.');
            }

            $out->status = true;
            $out->next = false;

            return $out;
        }
    }
}