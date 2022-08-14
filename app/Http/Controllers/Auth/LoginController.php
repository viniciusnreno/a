<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\WhatsappUser;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'login_celular' => ['required'],
            'login_senha' => ['required']
        ]);
    }

    public function authenticate(Request $request)
    {
        $data = $request->only('login_celular', 'login_senha');

        $credentials = ['mobile' => preg_replace('/[^\d]/', '', $data['login_celular']), 'password' => $data['login_senha']];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if($user->forbidden == 0){
                if($user->whatsappuser_id === null){

                    $findWhatsappUser = WhatsappUser::where(['mobile' => $user->mobile, 'email' => $user->email, 'cpf' => $user->cpf])->first();

                    if($findWhatsappUser){
                        $user->whatsappuser_id = $findWhatsappUser->id;
                        $user->save();
                    }
                    
                }
                
                $out['status'] = true;
                $out['redirect'] = route('start');
            } else {
                Auth::logout();
                $out['status'] = false;
                $out['msg'] = 'O seu usuário foi bloqueado. Entre em contato conosco para mais informações.';
                $out['data'] = $credentials;
            }
        } else {
            $out['status'] = false;
            $out['msg'] = 'Falha na autenticação';
            $out['data'] = $credentials;
        }

        return response()->json($out);
    }

    public function username()
    {
        return 'mobile';
    }
}
