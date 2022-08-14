<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    protected $table = 'whatsapp_log';

    protected $fillable = [
        'to', 'id_message', 'body', 'type', 'sender_name', 'from_me', 'author', 'chat_id', 'messageNumber'
    ];

    public static function parseWhatsappMessages($messages){
        $data = [];
        $i = 0;
        foreach($messages as $item){
        
            $data[$i]['author'] = $item->author;
            $data[$i]['sender_name'] = $item->sender_name;
            $data[$i]['created_at'] = $item->created_at;

            if($item->from_me == 1){
                $data[$i]['type'] = 'is-you';
                $data[$i]['body'] = $item->body;
            } else {
                $data[$i]['type'] = 'is-other';
                $data[$i]['body'] = self::unJsonZenviaMessage($item->body);
            }

            $i++;
        }

        return $data;
    }

    public static function unJsonZenviaMessage($message){
        $data = json_decode($message, true);
        // var_dump($data['message']['contents'][0]);
        // return;

        if(is_array($data['message']['contents'])){
            foreach($data['message']['contents'] as $item){
                if($item['type'] == 'text'){
                    return $item['text'];
                }
            }
        }
        return ":: ERRO AO LER A MENSAGEM ::";
    }
}