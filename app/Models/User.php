<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'whatsappuser_id', 'mobile', 'name', 'cpf', 'birth_date', 'genre', 'email', 'address', 'address_number', 'address_note', 
        'neighborhood', 'zipcode', 'city', 'state', 'ficou_sabendo_id', 'receive_information', 'keep_invoice', 'agree_regulation', 
        'ip', 'password', 'cnpj', 'company_person_name', 'company_person_cpf', 'gender', 'receive_information_email', 'receive_information_whatsapp', 'keep_data'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role($role) {
        $role = (array) $role;
        return in_array($this->role, $role);
    }
    

    public static function findUser($whereClause){

        $out= new \stdClass();

        $realWhereClause = [];

        foreach($whereClause as $item => $value){
            if($item == 'mobile'){
                $realWhereClause[$item] = preg_replace("/^\d{2}/", "", $value);
            } else {
                $realWhereClause[$item] = $value;
            }
        }

        $findUser = self::where($realWhereClause)->first();

        if($findUser){
            $out->data = $findUser;
            $out->status = true;
        } else {
            $out->status = false;
            $out->error = 'Usuário não encontrado.';
        }

        return $out;
    }

    public static function whatsappCreateUser($data){

        $out = new \stdClass();
        
        $findMobile = self::findUser($data);

        if($findMobile->status === false){
            $out->data = self::create($data);
    
            if($out->data){
                $out->status = true;
            } else {
                $out->status = false;
                $out->error = 'Houve um problema ao criar o seu usuário';
            }
        } else {
            $out->status = false;
            $out->error = 'O celular informado já está cadastrado';
        }

        
        return $out;        
    }

    public function whatsappChats()
    {
        return $this->hasMany('App\Models\WhatsappChat');
    }

    public function whatsappUsers(){
        return $this->hasOne('App\Models\WhatsappUser');
    }

    public function coupons(){
        return $this->hasMany('App\Models\Coupon');
    }

    public function codes(){
        return $this->hasManyThrough('App\Models\Code', 'App\Coupon');
    }

    public function luckyNumbers(){
        return $this->hasMany('App\Models\LuckyNumber');
    }
}
