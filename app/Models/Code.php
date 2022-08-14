<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Coupon;
use App\Models\User;
use App\Models\Product;

class Code extends Model
{
    protected $fillable = [
        'code', 'status', 'store_id', 'prizeType'
    ];

    public static function calculateCodes(Coupon $coupon){
        
        $out = new \StdClass();
        $out->codes = 0;

        if(env('PROMO_HAS_CODE') === true){
            $maxCodes = env('PROMO_MAX_CODES') - (int) self::countUserCodes($coupon->user_id);

            $listProducts = [];
            foreach($coupon->products as $item){
                $out->codes += $item->pivot->qty;
                $listProducts[] = $item->id;
            }

            $out->codes = floor($out->codes / env('PROMO_CODES_MIN_PRODUCTS'));

            if($out->codes > env('PROMO_CODES_MAX_PER_COUPON')){
                $out->codes = env('PROMO_CODES_MAX_PER_COUPON');
            }

            $out->codes = ($out->codes > $maxCodes) ? $maxCodes : $out->codes;

            $out->codes = Product::hasRequired($listProducts) === true ? $out->codes : 0;
        }


        return $out->codes;
    }

    public static function countUserCodes($userID){
        $coupons = Coupon::where(['user_id' => $userID, 'status' => 1])->get();
        
        $total = 0;
        foreach($coupons as $item){
            $total += count($item->codes);
        }
        return $total;
    }

    public function coupon(){
        return $this->belongsToMany('App\Models\Coupon');
    }

    public function prizes(){
        return $this->belongsToMany('App\Models\Prize');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
