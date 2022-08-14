<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Raffle extends Model
{
    public static function currentRaffle(){
		$raffles = self::all();
		
		$out = [];

		$now = date( 'Y-m-d H:i:s' );

		foreach( $raffles as $item )
		{
			$now = time();
			$start = strtotime( $item->dt_start . ' 00:00:00' );
			$end = strtotime( $item->dt_end . '23:59:59' );

			if( ( $now >= $start ) && ( $now <= $end ) )
		    {
		      	$out['id'] 					= $item->id;
		      	$out['verifying_digit'] 	= $item->verifying_digit;
		      	$out['dt_start'] 			= $item->dt_start;
		      	$out['dt_end'] 				= $item->dt_end;
		      	break;
		    }
		}
		return $out;
	}
	
	public static function currentRaffleID(){
		$current = self::currentRaffle();

		return (!empty($current['id'])) ? $current['id'] : null;
	}

    public function whatsappChats(){
        return $this->hasMany('App\Models\WhatsappChat');
    }
}
