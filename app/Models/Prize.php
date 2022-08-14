<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{    
    public static function getActivePrize(){
        return self::where('status', 1)->first();
    }
    public function coupons(){
        return $this->belongsToMany('App\Models\Coupon');
    }
}
