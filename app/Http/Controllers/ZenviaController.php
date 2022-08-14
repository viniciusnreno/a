<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Libraries\OCR;
use App\Libraries\ZenviaWhatsapp;
use App\Models\WhatsappStatus;


class ZenviaController extends BaseController
{
    public function webhookMessage(Request $request){

        $whatsapp = new ZenviaWhatsapp();
        
        // $whatsapp->receiveMessage($request);
        
        $requestBody = json_decode($request->getContent(), true);
        $whatsapp->send($requestBody['message']['from'], 'Promoção encerrada.');

        return response()->json(['status' => 'OK']);
    }

    public function webhookStatus(Request $request){
        
        $body = json_decode($request->getContent(), true);


        $data['zenvia_id'] = $body['id'];
        $data['zenvia_timestamp'] = $body['timestamp'];
        $data['zenvia_type'] = $body['type'];
        $data['zenvia_subscriptionId'] = $body['subscriptionId'];
        $data['zenvia_channel'] = $body['channel'];
        $data['zenvia_messageId'] = $body['messageId'];
        $data['zenvia_contentIndex'] = $body['contentIndex'];
        $data['zenvia_status_timestamp'] = $body['messageStatus']['timestamp'];
        $data['zenvia_status_code'] = $body['messageStatus']['code'];
        $data['zenvia_status_description'] = $body['messageStatus']['description'];
        $data['zenvia_status_cause_channelErrorCode'] = ($body['messageStatus']['causes'] !== null ) ? $body['messageStatus']['causes']['channelErrorCode'] : null;
        $data['zenvia_status_cause_reason'] = ($body['messageStatus']['causes'] !== null) ? $body['messageStatus']['causes']['reason'] : null;
        WhatsappStatus::create($data);

        return response()->json(['status' => 'OK']);

    }

    public function sendMessage(Request $request){

        $whatsapp = new ZenviaWhatsapp();
        
        $whatsapp->send('5511996393535', 'Opa');        

    }
    
    public function send(Request $request){
        $whatsapp = new WaBoxApp();
        $whatsapp->send('5511996393535', "Opa! \n\n \xF0\x9F\x98\x8D");
    }
}
