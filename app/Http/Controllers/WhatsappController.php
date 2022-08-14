<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;


use App\Libraries\OCR;
use App\Libraries\WaBoxApp;

class WhatsappController extends BaseController
{
    public function receive(Request $request){

        $whatsapp = new WaBoxApp();
        
        $whatsapp->receive($request);

        return response()->json(['status' => 'OK']);
        
        // $whatsapp->send('5511996393535', 'opa');
    }   
    
    public function send(Request $request){
        $whatsapp = new WaBoxApp();
        $whatsapp->send('5511996393535', "Opa! \n\n \xF0\x9F\x98\x8D");
    }
}
