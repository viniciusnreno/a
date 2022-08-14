<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

use Ramsey\Uuid\Uuid;


use App\Models\Store;
use App\Models\Coupon;

use App\Mail\CouponSent;
use App\Mail\RegisterCoupon;
use App\Mail\InstantPrize;
use App\Mail\CNAMini;

class CouponController extends Controller
{
    public function send(){
        Mail::to('denispose@msn.com')->queue(new CouponSent());
    }

    public function registerCouponAction(Request $request){

        $out = new \StdClass();

        $validCoupon = Coupon::isValidCoupon($request);

        if($validCoupon->status === false){
            return response()->json(['error' => $validCoupon->error]);
        }

        $dataCreate = Coupon::buildCreateCoupon($request);
        if(isset($dataCreate['error'])){
            return response()->json(['error' => $dataCreate['error']]);
        }

        try {
            DB::transaction(function () use ($request, $out, $dataCreate) {

                $isFirstCoupon = Coupon::isFirstCoupon();

                $coupon = Coupon::create($dataCreate);

                Coupon::setProducts($request, $coupon);

                $calculateCodesAndLuckyNumbers = Coupon::calculateCodesAndLuckyNumbers($coupon->id);

                $luckyNumbers = (env('PROMO_LUCKYNUMBERS_ADMIN_VALIDATE') === false) ? Coupon::setLuckyNumbers($coupon, $calculateCodesAndLuckyNumbers) : null;
                $instantPrize = (env('PROMO_INSTANTPRIZE_ADMIN_VALIDATE') === false) ? Coupon::setInstantPrize($coupon, $calculateCodesAndLuckyNumbers) : null;
                $codes = (env('PROMO_CODES_ADMIN_VALIDATE') === false) ? Coupon::setCodes($coupon, $calculateCodesAndLuckyNumbers) : null;
                $prizeValue = (env('PROMO_PRIZE_VALUE_ADMIN_VALIDATE') === false) ? Coupon::setPrizeValue($coupon) : null;

                Coupon::payBackFriend($coupon);

                if(env('PROMO_EMAIL_ENABLED') === true){

                    if($isFirstCoupon){
                        // Envia o email do curso de inglês
                        Mail::to(\Auth::user()->email)->queue(new CNAMini([]));
                    } else {
                        Mail::to(\Auth::user()->email)->queue(new RegisterCoupon([]));
                    }

                    if($instantPrize['hasPrize'] === true){
                        Mail::to(\Auth::user()->email)->send(new InstantPrize(['tpl' => 'Coupon' . $instantPrize['prize']->text_value]));
                    }

                    if($coupon->video_url != null){
                        // Mail::to(\Auth::user()->email)->queue(new VideoUpload([]));
                    }
                }                
                
                /*
                Coupon::newCouponEmails([
                    'coupon' => $coupon,
                    'luckyNumbers' => $luckyNumbers,
                    'instantPrize' => $instantPrize
                ]);
                */

                $out->status = true;
                $out->data = $coupon;
                $out->luckyNumbers = $luckyNumbers;                            
                $out->instantPrize = $instantPrize;  
                $out->codes = $codes;                          
                $out->redirect = route('start');

            });
        } catch(Exception $e){
            $out->status = false;
            $out->error = 'Houve um erro no banco de dados. Tente novamente';
        }

        sleep(1);
        
        return response()->json($out); 
    }

    public function registerVideoAction(Request $request){

        $out = new \StdClass();

        try {
            DB::transaction(function () use ($request, $out) {

                $coupon = Coupon::find($request->input('couponID'));

                if($coupon){
                    if($coupon->video_url != NULL){
                        throw new \Exception('O cupom informado já possui um vídeo cadastrado.');
                    }

                    if($coupon->user_id != \Auth::user()->id){
                        throw new \Exception('Cupom inválido!');
                    }
                } else {
                    throw new \Exception('Cupom não encontrado!');
                }

                if($request->file('video')){
                    $videoInfo = \Cloudinary::uploadVideo($request->file('video')->getRealPath(), ['folder' => 'desafio-saudavel']);
                    
                    $hasVideo = true;

                    if(isset($videoInfo->getResponse()['duration'])){
                        if($videoInfo->getResponse()['duration'] > 60){
                            throw new \Exception('O vídeo precisa ter no máximo 1 minuto');
                        }
                    } else {
                        throw new \Exception('Falha ao ler as propriedades do vídeo');
                    };
                }

                if($hasVideo == true){
                    $coupon->video_url = $videoInfo->getSecurePath();
                    $coupon->video_social_network = $request->input('social_network');
                    $coupon->video_friends = $request->input('hasFriends');
                    $coupon->video_public_id = $videoInfo->getPublicId();

                    $coupon->save();
                } else {
                    throw new \Exception('Houve um problema ao enviar o seu vídeo. Tente novamente!');
                }

                $out->status = true;
                $out->data = $coupon;          
                $out->redirect = route('start');

            });
        } catch(Exception $e){
            $out->status = false;
            $out->error = 'Houve um erro no banco de dados. Tente novamente';
        }

        sleep(1);
        
        return response()->json($out); 
    }

    public function registerLuckyFriendAction(Request $request){

        $out = new \StdClass();

        try {
            DB::transaction(function () use ($request, $out) {

                $coupon = Coupon::find($request->input('couponID'));

                if($coupon){
                    if($coupon->friend_email != NULL){
                        throw new \Exception('O cupom informado já possui um amigo indicado.');
                    }

                    if($coupon->user_id != \Auth::user()->id){
                        throw new \Exception('Cupom inválido!');
                    }
                } else {
                    throw new \Exception('Cupom não encontrado!');
                }

                $coupon->friend_email = $request->input('friend_email');
                $coupon->friend_name = $request->input('friend_name');
                $coupon->friend_social = $request->input('friend_social');

                $coupon->save();

                Coupon::luckyFriend($coupon);

                $out->status = true;
                $out->data = $coupon;          
                $out->redirect = route('start');

            });
        } catch(Exception $e){
            $out->status = false;
            $out->error = 'Houve um erro no banco de dados. Tente novamente';
        }

        sleep(1);
        
        return response()->json($out); 
    }
}
