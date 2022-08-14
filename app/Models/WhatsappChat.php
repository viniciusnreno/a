<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use App\Models\WhatsappUser;
use App\Models\Raffle;

class WhatsappChat extends Model
{
    use SoftDeletes;

    protected $table = 'whatsapp_chat';
    
    protected $fillable = [
                            'whatsappuser_id', 'raffle_id', 'name', 'mobile', 'mobile_full', 'invoice', 
                            'invoice_local', 'company_cnpj', 'coupon_number', 'amount', 'cpf',
                            'buy_date', 'status' 
                        ];
    protected $dates = [
        'buy_date',
    ];

    public function createChat($mobile){
        $out = new \stdClass();
        
        $findWhatsappUser = WhatsappUser::where('mobile', preg_replace("/^\d{2}/", "", $mobile))->first();

        if(!$findWhatsappUser){
            $out->status = false;
            $out->error = '*Promo PANCO:* Você ainda não possui cadastro em nosso whatsapp. Digite *cadastrar* para iniciar.';
        } else {

            $findOpenChat = self::where(['whatsappuser_id' => $findWhatsappUser->id, 'status' => 0])->get();
            if(count($findOpenChat) > 0){
                $out->status = false;
                $out->error = '*Promo PANCO:* Você já iniciou o cadastro de um cupom, mas não o finalizou.';
            } else {  
                $currentRaffle = Raffle::currentRaffle();

                if($currentRaffle !== null){

                    $createChat =  self::create(['whatsappuser_id' => $findWhatsappUser->id, 'raffle_id' => $currentRaffle['id']]);

                    if($createChat){
                        $out->status = true;
                        $out->data = $createChat;
                    } else {
                        $out->status = false;
                        $out->error = '*Promo PANCO:* Houve um erro ao iniciar o chat.';
                    }
                } else {
                    $out->status = false;
                    $out->error = '*Promo PANCO:* Não existe nenhum sorteio ativo.';
                }
            }
        }
        
        return $out;
    }

    public function deleteChat($mobile){
        
        $out = new \stdClass();
        $out->status = false;
        $out->error = null;

        $findWhatsappUser = WhatsappUser::where('mobile', preg_replace("/^\d{2}/", "", $mobile))->first();

        if(!$findWhatsappUser){
            $out->status = false;
            $out->error = '*Promo Fome de Aprender Panco*: Nao foi possível excluir o seu cupom, pois o seu usuário não foi encontrado.';            
        } else {

            $findChat = $this->where('whatsappuser_id', $findWhatsappUser->id)->where('status', 0)->first();

            if(!$findChat){
                $out->status = false;
                $out->error = '*Promo Fome de Aprender Panco*: Não foi possível encontrar um chat em andamento.';
            } else {
                if(!$findChat->delete()){
                    $out->status = false;
                    $out->error = '*Promo Fome de Aprender Panco*: Houve um erro ao remover o seu cupom. Tente novamente.';
                } else {
                    $out->status = true;
                    $out->error = NULL;
                    $out->data = $findChat;
                }

            }
        }

        return $out;
    }
    
    /*
    public function getCreatedAtAttribute($value){
        return dateBR($value, true);
    }

    public function getUpdatedAtAttribute($value){
        return dateBR($value, true);
    }
    */

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function raffle(){
        return $this->hasOne('App\Raffle');
    }
}
