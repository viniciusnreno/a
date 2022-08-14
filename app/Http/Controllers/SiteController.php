<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Mail\FaleConosco;

use Ramsey\Uuid\Uuid;

use App\Models\LuckyNumber;
use App\Models\Code;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Models\Store;
use App\Models\Influencer;
use App\Libraries\PicPay;

class SiteController extends Controller
{
    public function index(){
        $data = [];

        $data['products'] = Product::where('status', 1)->orderBy("category", "asc")->get();
        $data['stores'] = Store::where('status', 1)->orderBy("store_name", "asc")->get();
        $data['prizes'] = DB::table('prizes')->where('status', 1)->get();
        $data['ficouSabendo'] = DB::table('ficou_sabendo')->get();
        // $data['videos'] = Coupon::where('admin_video_valid_social_network', 1)->whereNotNull('video_url')->orderBy('id', 'desc')->limit(10)->get();
        if (Auth::check()) {
            $data['coupons'] = Coupon::where('user_id', \Auth::user()->id)->get();
        }

        return view('index', $data);
    
    }

    public function cadastro(){
        
        if (\Auth::check()) {
            return redirect('minha-conta/cadastro/cupom');
        }

        $data['ficouSabendo'] = DB::table('ficou_sabendo')->get();

        return view('cadastro', $data);
    }

    public function produtos(){
        return view('produtos');
    }
    public function lojas(){
        return view('lojas');
    }
    public function duvidas(){
        return view('duvidas');
    }

    public function comprar(){
        return view('comprar');
    }

    public function regulamento(){
        return view('regulamento');
    }

    public function cadastroSucesso(){
        return view('cadastro-sucesso');
    }

    public function cupomSucesso(){
        return view('cupom-sucesso');
    }

    public function sorteios(){
        return view('sorteios');
    }

    public function historico(){
        // $data['luckyNumbers'] = LuckyNumber::select('lucky_numbers.*','raffles.verifying_digit')->where('user_id', \Auth::user()->id)->leftJoin('raffles', 'lucky_numbers.raffle_id', '=', 'raffles.id')->get();
        
        $coupons = Coupon::where(['user_id' => \Auth::user()->id, 'status' => 1])->get();

        $data['coupons'] = [];

        $i = 0;
        foreach($coupons as $item){
            $data['coupons'][$i]['luckyNumbers'] = $item->luckyNumbers;
            $data['coupons'][$i]['codes'] = $item->codes;
            $data['coupons'][$i]['raffle'] = $item->raffle->verifying_digit;
            $data['coupons'][$i]['date'] = $item->created_at;
            $i++;
        }
        return view('historico', $data);
    }

    public function faleConosco(){
        return view('fale-conosco');
    }

    public function faleConoscoAction(Request $request){
        
        $out = new \stdClass();

        try {
            Mail::to('faleconoscopromopanco@promobrazil.com.br')->send(new FaleConosco($request->all()));

            $out->status = true;
            $out->redirect = route('start');
        } catch(Exception $e){
            $out->status = false;
            $out->error = 'Houve um erro ao enviar a sua mensagem. Tente novamente';
        }

        return response()->json($out);
    }

    public function updateProfileAction(Request $request){

        $out = new \stdClass();
        
        $user = Auth::user();

        $user->name = $request->input('md_nome');
        $user->birth_date = dateEN($request->input('nascimento'), false);
        $user->zipcode = $request->input('md_cep');
        $user->address = $request->input('md_endereco');
        $user->address_number = $request->input('md_numero');
        $user->address_note = $request->input('md_complemento');
        $user->neighborhood = $request->input('md_bairro');
        $user->state = $request->input('md_estado');
        $user->city = $request->input('md_cidade');

        try {
            $user->save();

            $out->status = true;
            $out->data = $coupon;
            $out->redirect = route('start');

        } catch(Exception $e) {
            $out->status = false;
            $out->error = 'Houve um erro ao atualizar os seus dados. Tente novamente';
        }

        return response()->json($out);
    }

    public function influencers($type, $profile, Request $request){
        $influencer = DB::table('influencers_info')->where(['profile' => $profile, 'type' => $type])->first();

        if($influencer){
            $influencerData = Influencer::where('id', $influencer->influencer_id)->firstOrFail();
            $request->session()->put('influencer', $influencerData->key);
            $request->session()->put('influencerID', $influencerData->id);
            
            return redirect($influencer->url);
        } else {
            abort(404);
        }
    }

    public function acceptNewRulesAction(Request $request){
        
        $user = User::findOrFail(\Auth::user()->id);

        $out = new \stdClass();

        if($user){
            $user->new_rules = 1;
            $user->new_rules_at = date('Y-m-d H:i:s');
            if(!$user->save()){
                $out->status = false;
                $out->error = 'Erro ao salvar.';
            } else {
                $out->status = true;
            }
        } else {
            $out->status = false;
            $out->error = 'Usuário não encontrado.';
        }

        return response()->json($out);
    }

    public function picpay(Request $request){
        if($request->query('code') != '1281'){
            return;
        }

        $picpay = new PicPay();
        $picpay->getToken();
        $picpay->transfer('33167005874', 0.01, false);
    }

    public function PicPayCreateProject(Request $request){
        if($request->query('code') != '1281'){
            return;
        }

        $picpay = new PicPay();
        $picpay->getToken();
        $picpay->createProject();
    }

    public function PicPayProjects(Request $request){
        if($request->query('code') != '1281'){
            return;
        }

        $picpay = new PicPay();
        $picpay->getToken();
        $response = $picpay->projects();

        print $response->getBody();
    }

    public function fixError(Request $request){
        Coupon::fixLuckyNumberCouponError();
    }
}
