<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use App\Models\User;

class WhatsappUser extends Model
{
    protected $fillable = [
        'mobile', 'name', 'email', 'cpf', 'birth_date', 'state', 'city', 'agree_regulation', 'password', 'completed'
    ];

    public function createUser($data){
        Log::debug('USER');
        $out = new \stdClass();

        $userExist = false;

        if($data['mobile']){
            $data['mobile'] = preg_replace("/^\d{2}/", "", $data['mobile']);

            $findUser = User::where(['mobile' => $data['mobile']])->first();

            if($findUser){
                $data['name'] = $findUser->name;
                $data['email'] = $findUser->email;
                $data['cpf'] = $findUser->cpf;
                $data['birth_date'] = $findUser->birth_date;
                $data['state'] = $findUser->state;
                $data['city'] = $findUser->city;
                $data['agree_regulation'] = $findUser->agree_regulation;
                $data['password'] = $findUser->password;
                $data['completed'] = 1;

                $userExist = true;
            }

            $whatsappUserExists = self::where('mobile', $data['mobile'])->get();

            if(count($whatsappUserExists) == 0){
                $whatsappUser = self::create($data);

                if($whatsappUser){
                    if($userExist !== true){
                        $out->status = true;
                        $out->data = $whatsappUser;
                    } else {
                        $out->status = false;
                        $out->error = 'Você já está cadastrado em nosso Whatsapp. Você pode enviar o seu cupom digitando *cupom*.';
                    }
                } else {
                    $out->status = false;
                    $out->error = 'Erro ao iniciar o cadastro. Tente novamente.';
                }
            } else {
                $out->status = false;
                $out->error = 'Você já está cadastrado em nosso Whatsapp. Você pode enviar o seu cupom digitando *cupom*.';
            }
        }
        return $out;
    }
}
