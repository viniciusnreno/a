<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
    public function coupons(){
        return $this->belongsToMany('App\Models\Coupon');
    }
    

    public static function hasRequired($items){
        if(env('PROMO_HAS_REQUIRED_PRODUCTS') === true){
            $requiredProduct = self::where(['selected' => 1, 'status' => 1])->get()->pluck('id')->toArray();
            $searchProductRequired = array_intersect($requiredProduct, $items);
            if(count($searchProductRequired) > 0){
                return true;
            }
            return false;
        } else {
            return true;
        }
    }

    public static function maskProductID($search, $type = 'out'){
        $map = [
            'A' => 1,
            'B' => 2,
            'C' => 3,
            'D' => 4,
            'E' => 5,
            'F' => 6,
            'G' => 7,
            'H' => 8
        ];

        if($type == 'out'){
            return array_search($search, $map);
        } else {
            if(!isset($map[strtoupper($search)])){
                return '--';
            } else {
                return $map[strtoupper($search)];
            }
        }
    }
}
