<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Raffle;
use App\Models\Coupon;
use Illuminate\Support\Facades\Log;

class LuckyNumber extends Model
{
	protected $fillable = [
		'user_id', 'coupon_id', 'raffle_id', 'number', 'ip', 'final', 'parent_coupon_id'
	];

	public static function calculateLuckyNumbers(Coupon $coupon){
		$out = new \StdClass();
		$out->luckyNumbers = 0;

        if(env('PROMO_HAS_LUCKY_NUMBERS') === true){
            
            $maxLuckyNumbers = env('PROMO_MAX_LUCKY_NUMBERS') - (int) LuckyNumber::countLuckyNumbersRaffle($coupon->user_id);

            /*
            Log::debug('Cupom: ' . var_export($coupon, true));
            // Log::debug('Cupom Products: ' . var_export($coupon->products, true));
            Log::debug('Has LuckyNumbers');
            Log::debug('Max LuckyNumbers: ' . $maxLuckyNumbers);
            Log::debug('Cupom ID (2): ' . $coupon->id);
            $listProducts = [];
            $listCategoryProducts = [];
            foreach($coupon->products as $item){
                Log::debug('Produto Qtd.: ' . $item->pivot->qty);
                $out->luckyNumbers += $item->pivot->qty;
                $listProducts[] = $item->id;
                $listCategoryProducts[] = $item->category;
            }
            $out->luckyNumbers = floor($out->luckyNumbers / env('PROMO_LUCKY_NUMBERS_MIN_PRODUCTS'));
            */

            $out->luckyNumbers = floor($coupon->amount / env('PROMO_LUCKY_NUMBERS_VALUE'));
            Log::debug('Total LuckyNumbers (1): ' . $out->luckyNumbers);

            if($out->luckyNumbers > env('PROMO_LUCKY_NUMBERS_MAX_PER_COUPON')){
                $out->luckyNumbers = env('PROMO_LUCKY_NUMBERS_MAX_PER_COUPON');
                Log::debug('Total LuckyNumbers (2): ' . $out->luckyNumbers);
            }

            if(env('PROMO_HAS_SPECIAL_PRODUCT') === true){
                if(array_search('Recheado', $listCategoryProducts) !== false ){
                    $out->luckyNumbers++;
                }
            }

            $out->luckyNumbers = ($out->luckyNumbers > $maxLuckyNumbers) ? $maxLuckyNumbers : $out->luckyNumbers;

        }

        return $out->luckyNumbers;
    }


    public static function generate(){
			$setNumber['number_1'] 			= str_pad( rand( 0, 999 ), 3, "0", STR_PAD_LEFT );
			$setNumber['number_2'] 			= str_pad( rand( 0, 999 ), 3, "0", STR_PAD_LEFT );

			$number = $setNumber['number_1'] . '.' . $setNumber['number_2'];

			$checkNumber = LuckyNumber::where('number', $number) ->get();

			if( $checkNumber->count() > 0 || $number == "" ){
				return self::generate();
			} else {
				return $number;
			}
	}

	public static function countLuckyNumbersRaffle($userID, $raffleID = null){
		if($raffleID !== null){
			$coupons = Coupon::where(['user_id' => $userID, 'raffle_id' => $raffleID, 'status' => 1])->get();
		} else {
			$coupons = Coupon::where(['user_id' => $userID, 'status' => 1])->get();
		}

        $total = 0;
        foreach($coupons as $item){
            $total +=  count($item->luckyNumbers);
        }
        return $total;
    }

    public static function updateFriend($user){
        $getCoupons = Coupon::where('friend_email', $user->email)->where('friend_status', 1)->get();

        foreach($getCoupons as $coupon){
            $luckyNumber = LuckyNumber::where('parent_coupon_id', $coupon->id)->first();

            if($luckyNumber != null){
                $luckyNumber->user_id = $user->id;
                $luckyNumber->save();
            }
        }
    }
	
	public function raffle(){
		return $this->hasOne('App\Models\Raffle');
	}
}
