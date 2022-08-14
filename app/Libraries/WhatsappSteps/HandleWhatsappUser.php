<?php
namespace App\Libraries\WhatsappSteps;

use Storage;
use GuzzleHttp;

use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

use Ramsey\Uuid\Uuid;
use App\Models\User;
use App\Models\WhatsappUser;
use App\Models\WhatsappChat;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\WhatsappLog;
use App\Libraries\ZenviaWhatsapp;
use App\Libraries\OCR;
use App\Mail\RegisterCoupon;
use Intervention\Image\ImageManager as Image;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\RequestInterface;
use Illuminate\Support\Facades\Log;

use Dflydev\DotAccessData\Data;



class HandleWhatsappUser {
    public static function init($whatsappHandler, $json, $data, $body){
        // Log::debug('Join: ZenviaWhatsapp::handleWhatsappUser() data is: ' . json_encode($data));
        // Log::debug('Join: ZenviaWhatsapp::handleWhatsappUser() body is: ' . $body);
        
        $findWhatsappUser = WhatsappUser::where(['mobile' => preg_replace("/^\d{2}/", "", $data['message']['from'])])->first();

        if($findWhatsappUser){      
            if($findWhatsappUser->name === null){
                $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                $whatsappUserModel->name = $body;
                if($whatsappUserModel->save()){
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Agora, poderia nos informar o seu e-mail? Não se esqueça de checar se ele foi escrito corretamente, ok?');
                } else {
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Houve um problema ao salvar o seu nome. Tente novamente!');
                }
            } else if($findWhatsappUser->email === null){
                if(filter_var($body, FILTER_VALIDATE_EMAIL) === false){
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: O e-mail informado é inválido. Para continuarmos, poderia nos informar o seu e-mail?');
                } else {
                    
                    $findWhatsappEmail = WhatsappUser::where(['email' => $body])->get();

                    if(count($findWhatsappEmail) > 0){
                        $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: O e-mail informado já está atrelado a um outro número de celular. Por favor, escolha outro e-mail.');
                    } else {
                        $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                        $whatsappUserModel->email = strtolower($body);
                        if($whatsappUserModel->save()){
                            $whatsappHandler->send($data['message']['from'], "*Promo PANCO*: Muito bem! Agora precisamos que nos informe seu CPF. Por favor, digite apenas os números.");
                        } else {
                            $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Houve um problema ao salvar o seu e-mail. Tente novamente!');
                        }
                    }
                }              
            } else if($findWhatsappUser->cpf == null){
                $cpf = preg_replace('/[^\d]/', '', $body);

                $findWhatsappCPF = WhatsappUser::where(['cpf' => $cpf])->get();
                $isBlockedUser = DB::table('blocked_users')->where('document', $cpf)->count();

                if(count($findWhatsappCPF) > 0){
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: O CPF informado já está atrelado a um outro número de celular.');
                } else if(!validCPF($cpf)){
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: O CPF informado é inválido. Para continuarmos, poderia nos informar o seu CPF? Por favor, digite apenas os números');
                } else if($isBlockedUser > 0){
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: O CPF informado não pode participar desta promoção. Caso você tenha dúvida sobre essa mensagem, entre em contato com a gente pelo site.');
                } else {
                    $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                    $whatsappUserModel->cpf = $cpf;
                    if($whatsappUserModel->save()){
                        $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Ótimo, falta pouco, mas ainda precisamos de mais algumas informações. Qual é a sua data de nascimento? Digite no formato DD/MM/AAAA e não esqueça de colocar as / entre os números.');
                    } else {
                        $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Houve um problema ao salvar o seu CPF. Tente novamente!');
                    }
                }
            } else if($findWhatsappUser->birth_date == null){
                
                $explodedDate = explode('/', $body);
                
                if(count($explodedDate) != 3){
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: A data informada é inválida. Para continuarmos, poderia nos informar sua data de nascimento? Digite no formato DD/MM/AAAA e não esqueça de colocar as / entre os números');
                    return;
                }
                
                $birthDate = dateEN($body, false);

                list($day, $month, $year) = $explodedDate;

                $day = (int) $day;
                $month = (int) $month;
                $year = (int) $year;

                if(count($explodedDate) != 3){
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: A data informada é inválida. Para continuarmos, poderia nos informar sua data de nascimento? Digite no formato DD/MM/AAAA e não esqueça de colocar as / entre os números');
                } else {
                    

                    $d1 = new \DateTime($birthDate);
                    $d2 = new \DateTime(date('Y-m-d'));

                    $interval = $d1->diff($d2);

                    if(!checkdate(trim($month), trim($day), trim($year))){
                        $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: A data informada é inválida. Para continuarmos, poderia nos informar sua data de nascimento? Digite no formato DD/MM/AAAA e não esqueça de colocar as / entre os números');
                    } else if($interval->y < 18){
                        $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Somente maiores de 18 anos podem participar da Promoção Panco Fome de Aprender. Por favor, procure seus pais ou responsável para e inicie um novo cadastro. Agradecemos pelo interesse em participar!');
                    } else {
                        $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                        $whatsappUserModel->birth_date = $birthDate;

                        if($whatsappUserModel->save()){
                            $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Data de nascimento cadastrada com sucesso. Informe agora o seu Estado, digitando apenas a sigla do mesmo. (Exemplo, se for São Paulo, digite apenas SP)');
                        } else {
                            $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Houve um problema ao salvar a sua data de nascimento. Tente novamente!');
                        }
                    }
                }
            } else if($findWhatsappUser->state == null){
                $state = strtoupper($body);

                $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                // $validStates = ["AC", "AL", "AM", "AP", "BA", "CE", "DF", "ES", "GO", "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI", "RJ", "RN", "RO", "RS", "RR", "SC", "SE", "SP", "TO"];
                $validStates = ["SP", "RJ", "MG", "PR", "SC"];

                if(!in_array($state, $validStates)){
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Informe agora o seu Estado, digitando apenas a sigla do mesmo. (Exemplo, se for São Paulo, digite apenas SP). Os Estados que participam dessa promoção são: ' . implode(', ', $validStates) . '.');
                } else {
                    $whatsappUserModel->state = $state;

                    if($whatsappUserModel->save()){
                        $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Estado cadastrado com sucesso. Informe, agora, a sua cidade.');
                    } else {
                        $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Houve um problema ao salvar o seu Estado. Tente novamente!');
                    }
                }
            } else if($findWhatsappUser->city == null){
                $city = $body;

                $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                $onlyCapital = array('PE', 'CE', 'GO', 'SC', 'PR', 'RS');

                $errorCity = false;

                /*
                if(in_array($whatsappUserModel->state, $onlyCapital)){
                    if($whatsappUserModel->state == 'PE'){
                        if(strtolower($city) != 'recife'){
                            $errorCity = true;
                            $currentCity = 'Recife';
                        }
                }
                */

                if($errorCity === true){
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Para o Estado de '. $whatsappUserModel->state .', a única cidade participante é '. $currentCity );
                } else {
                    $whatsappUserModel->city = $city;

                    if($whatsappUserModel->save()){
                        $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Cidade cadastrada com sucesso. Estamos quase finalizando. Precisamos que digite uma senha para o acesso ao site.');
                    } else {
                        $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Houve um problema ao salvar a sua cidade. Tente novamente!');
                    }
                }
            } else if($findWhatsappUser->password == null){
                $password = $body;

                $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                if(strlen($password) < 4 || strlen($password) > 12){
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: A sua senha deve ter no mínimo 4 e no máximo 12 caracteres. Por favor, digite novamente.');
                } else {
                    $whatsappUserModel->password = Hash::make($password);
                    if($whatsappUserModel->save()){
                        $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Você concorda com os termos de nosso regulamento? Para consulta-lo, acesse: https://promopanco.com.br e responda SIM para finalizar seu cadastro.');
                    } else {
                        $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Houve um problema ao salvar a sua senha. Tente novamente!');
                    }
                }
            } else if($findWhatsappUser->agree_regulation == null){

                $company_cnpj = preg_replace('/[^\d]/', '', $body);

                if(strtolower($body) != 'sim'){
                    $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Você concorda com os termos de nosso regulamento? Para consulta-lo, acesse: https://promopanco.com.br e responda SIM para finalizar seu cadastro ou NÃO para cencelá-lo.');
                } else {
                    $whatsappUserModel = WhatsappUser::find($findWhatsappUser->id);

                    $whatsappUserModel->agree_regulation = 1;
                    $whatsappUserModel->completed = 1;

                    if($whatsappUserModel->save()){                       
                        $whatsappHandler->send($data['message']['from'], 'Ótimo. Finalizamos o seu cadastro!');
                        $whatsappHandler->send($data['message']['from'], $json['send']['cadastro-sucesso']['text']);
                    } else {
                        $whatsappHandler->send($data['message']['from'], 'Houve um problema ao salvar a sua resposta. Tente novamente!');
                    }
                }
            } else {
                $whatsappHandler->send($data['message']['from'], '*Promo PANCO*: Você já está cadastrado! Gostaria de enviar um cupom? Se sim, digite a palavra CUPOM para darmos continuidade.');
            }
        } else {
            // Log::debug('Send Message To: ' . $data['message']['from'] . ': ' . $json['send']['hello']['text']);
            $whatsappHandler->send($data['message']['from'], $json['send']['hello']['text']);  
        }
    }
}