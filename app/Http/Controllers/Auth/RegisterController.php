<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

use App\Models\WhatsappUser;
use App\Models\InfluencerUser;
use App\Models\Coupon;
use App\Models\LuckyNumber;


class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        if($data['documento']){
            $data['documento'] = preg_replace('/[^\d]/', '', $data['documento']);
        }

        if($data['celular']){
            $data['celular'] = preg_replace('/[^\d]/', '', $data['celular']);
        }

        $attr = [
            'termos' => 'Termos do Regulamento',
            'receive_information_email' => 'Info. por Email e Tel.',
            'receive_information_whatsapp' => 'Info. por Whatsapp',
            // 'ciente' => 'Guardar Cupoom Fiscal',
        ];

        return Validator::make($data, [
            'nome' => ['required', 'string', 'max:255'],
            // 'birth_date' => ['required'],
            'documento' => ['required', 'string', 'unique:users,cpf', function ($attribute, $value, $fail) {
                $isBlockedUser = DB::table('blocked_users')->where('document', preg_replace('/[^\d]/', '', $value))->count();
                if ($isBlockedUser > 0) {
                    $fail('O ' . $attribute . ' informado não é permitido.');
                }
            }],
            // 'endereco' => ['required'],
            'estado' => ['required', function ($attribute, $value, $fail) {
                $isValidState = in_array($value, ['RS', 'SC', 'PR', 'SP']);
                if ($isValidState !== true) {
                    $fail('O ' . $attribute . ' informado não é permitido.');
                }
            }],
            // 'cidade' => ['required'],
            // 'termos' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', 'confirmed', 'unique:users'],
            'senha' => ['required', 'string', 'min:4', 'confirmed'],
            'celular' => ['required', 'unique:users,mobile'],
            //'termos' => ['required'],
            // 'receive_information_email' => ['required'],
            // 'receive_information_whatsapp' => ['required'],
            // 'ciente' => ['required']
        ], [], $attr);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {	
        $data['userType'] = 'F';

        if($data['userType'] == 'F'){
            $cpf = preg_replace('/[^\d]/', '', $data['documento']);
            $cnpj = null;
            $cName = null;
            $cCPF = null;
        } else {
            $cpf = null;
            $cnpj = preg_replace('/[^\d]/', '', $data['documento']);
            $cName = $data['company_person_name'];
            $cCPF = $data['company_person_cpf'];
        }

        $user =  User::create([
            'mobile' => preg_replace('/[^\d]/', '', $data['celular']),
            'name' => $data['nome'],
            'user_type' => $data['userType'],
            'cpf' => $cpf,
            'cnpj' => $cnpj,
            'company_person_name' => $cName,
            'company_person_cpf' => $cCPF,
            'birth_date' => dateEN($data['dt_nascimento'], false),
            'city' => null, //$data['cidade'],
            'email' => strtolower($data['email']),
            'state' => $data['estado'],
            'ficou_sabendo_id' => (isset($data['ficouSabendo'])) ? $data['ficouSabendo'] : null,
            'receive_information_email' => (isset($data['receive_information_email']) && $data['receive_information_email'] == 'On') ? 1 : 0,
            'keep_data' => (isset($data['keep_data']) && $data['keep_data'] == 'On') ? 1 : 0,
            'agree_regulation' => 1, //($data['termos'] == 'On') ? 1 : 0,
            'ip' => \Request::ip(),
            'password' => Hash::make($data['senha']),
            // 'address' => $data['endereco'],
            // 'address_number' => $data['numero'],
            // 'address_note' => $data['complemento'],
            // 'neighborhood' => $data['bairro'],
            // 'zipcode' => preg_replace('/[^\d]/', '', $data['cep']),
            // 'gender' => $data['genero'],
            // 'receive_information_whatsapp' => ($data['receive_information_whatsapp'] == 'On') ? 1 : 0,
            // 'keep_invoice' => ($data['ciente'] == 'On') ? 1 : 0,
            // 'new_rules' => 1,
        ]);

        if($user){
            if($user->whatsappuser_id === null){                
                $findWhatsappUser = WhatsappUser::where(['mobile' => $user->mobile, 'email' => $user->email, 'cpf' => $user->cpf])->first();

                if($findWhatsappUser){
                    $user->whatsappuser_id = $findWhatsappUser->id;
                    $user->save();
                }
                
            }

            if(session('influencerID')) {
                InfluencerUser::create([
                    'user_id' => $user->id,
                    'influencer_id' => session('influencerID'),
                    'ip' => \Request::ip()
                ]);
            }
        }

        $updatedUser = User::find($user->id);
        
        LuckyNumber::updateFriend($user);

        return $updatedUser;
    }

    public function registered($request, $user){
        return response()->json([
            'status' => true,
            'redirect' => route('start')
        ]);
    }

    public function showRegistrationForm() {
        
        if (\Auth::check()) {
            return redirect('minha-conta/cadastro/cupom');
        }
        
        $data['ficouSabendo'] = DB::table('ficou_sabendo')->get();

        return view('cadastro', $data);
    }
}
