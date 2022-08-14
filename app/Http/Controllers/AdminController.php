<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

use Maatwebsite\Excel\Facades\Excel;    

use App\Models\User;
use App\Models\Coupon;
use App\Models\WhatsappChat;
use App\Models\LuckyNumber;
use App\Models\Code;
use App\Models\WhatsappLog;
use App\Models\WhatsappUser;
use App\Models\Prize;
use App\Mail\ValidCoupon;
use App\Mail\InvalidCoupon;
use App\Mail\ValidVideo;
use App\Mail\InvalidVideo;
use App\Mail\ValidPrize;
use App\Mail\InvalidPrize;
use App\Mail\ValidCNAMini;
use App\Mail\InvalidCNAMini;
use App\Libraries\ZenviaWhatsapp;
use App\Libraries\WaBoxApp;
use App\Jobs\ProcessPicpay;

use Illuminate\Support\Facades\Log;

use App\Excel\Exports\LuckyNumbersExport;

class AdminController extends Controller
{
    public function index(){
        
        $usersHasCoupon = collect(DB::select('SELECT COUNT(DISTINCT(user_id)) as totalUsersWithCoupon FROM coupons GROUP BY user_id'))->first();
        $data['users']['count'] = User::all()->count();

        if($usersHasCoupon !== null){
            $data['users']['average'] = ($usersHasCoupon->totalUsersWithCoupon != 0) ? 
                                        $usersHasCoupon->totalUsersWithCoupon / $data['users']['count'] * 100
                                        : 0;
        } else {
            $data['users']['average'] = 0;
        }

        $data['totalCoupon'] = Coupon::all()->count();

        $siteValidated = Coupon::whereNull('whatsappchat_id')->where('status' , 1)->get()->count();

        $data['siteCoupon']['count'] = Coupon::whereNull('whatsappchat_id')->get()->count();
        $data['siteCoupon']['percentageTotal'] = 0;
        if($data['totalCoupon'] > 0){
            $data['siteCoupon']['percentageTotal'] = ($data['siteCoupon']['count'] > 0) ? 
                                                        $siteValidated / $data['siteCoupon']['count'] * 100
                                                        : 0;

        }
        
        $data['whatsappCoupon']['count'] = WhatsappChat::all()->count();
        $data['whatsappCoupon']['abandoned'] = 0;
        if($data['whatsappCoupon']['count'] > 0){
            $abandonedCoupon = WhatsappChat::where('status', 0)->count();
            $data['whatsappCoupon']['abandoned'] = ($abandonedCoupon > 0 ) ?
                                                    $abandonedCoupon / $data['whatsappCoupon']['count'] * 100
                                                    : 0;
        }
        
        
        $userHasLuckyNumber = collect(DB::select('SELECT COUNT(id) as totalUsers FROM users WHERE id IN(SELECT DISTINCT(user_id) FROM lucky_numbers)'))->first();
        $data['luckyNumbers']['count'] = LuckyNumber::all()->count();
        $data['luckyNumbers']['average'] = ($userHasLuckyNumber->totalUsers != 0) ? 
                                            $userHasLuckyNumber->totalUsers / $data['users']['count'] * 100
                                            : 0;

        $data['couponsToValidate'] = Coupon::whereNull('status')->get();
        return view('admin.index', $data);
    }

    public function validCoupons(){

        $data['coupons'] = Coupon::where('status', 1)->get();
        
        return view('admin.validCoupons', $data);
    }

    public function invalidCoupons(){

        $data['coupons'] = Coupon::where('status', 0)->get();
        
        return view('admin.invalidCoupons', $data);
    }

    public function instantPrizeCoupons(){

        $data['coupons'] = Coupon::whereNotNull('instant_prize_hash')->get();
        
        return view('admin.instantPrizeCoupons', $data);
    }

    public function videoCoupons(){

        $data['coupons'] = Coupon::whereNotNull('video_url')->get();
        
        return view('admin.videoCoupons', $data);
    }

    public function firstCoupon(){

        $data['coupons'] = Coupon::where('cna_mini_curso', 1)->get();
        
        return view('admin.firstCoupon', $data);
    }

    public function validateCoupon($id){
        $coupon = Coupon::findOrFail($id);

        $data['coupon'] = $coupon;
        $data['user'] = User::find($coupon->user_id);
        $data['prize'] = DB::table('prizes')->where('id', $coupon->prize_id)->first();
        $data['prizes'] = DB::table('prizes')->where(['id' => $coupon->prize_id])->get();
        $data['codesAndLuckyNumbers'] = Coupon::calculateCodesAndLuckyNumbers($id);
        $data['currentCountLuckyNumbers'] = LuckyNumber::countLuckyNumbersRaffle($coupon->user_id, $coupon->raffle_id);
        $data['currentCountCodes'] = Code::countUserCodes($coupon->user_id);
        $data['luckyFriend'] = LuckyNumber::where('parent_coupon_id', $coupon->id)->whereNull('coupon_id')->first();
  
        foreach($data['prizes'] as $itemPrize){
            $type = (int) $itemPrize->instant == 1 ? 'instant' : 'code';

            $data['avaliablePrizes'][$type][$itemPrize->label]['label'] = 'R$ ' . $itemPrize->text_value;
            $data['avaliablePrizes'][$type][$itemPrize->label]['prize'] = $itemPrize->prize;
            $data['avaliablePrizes'][$type][$itemPrize->label]['qty'] = Code::where(['prizeType' => (int) $itemPrize->id, 'status' => 0])->get()->count();
        }
        
        return view('admin.cupons.validar', $data);
    }

    public function validateCouponAction(Request $request){
        $out = new \stdClass();

        $coupon = Coupon::findOrFail($request->input('coupon_id'));

        $user = User::find($coupon->user_id);

        $hasCode = DB::table('code_coupon')->where('user_id', $coupon->user_id)->count();

        $searchCoupon = Coupon::where(['company_cnpj' => $coupon->company_cnpj, 'coupon_number' => $coupon->coupon_number, 'status' => 1])->get();

        try {

            DB::transaction(function () use ($request, $coupon, $out, $user) {

                $completeTransfer = true;

                $coupon->status = ($request->input('validate') == 1) ? 1 : 0; 

                if($request->input('validate') == 0){
                    $coupon->reason = $request->input('reason');
                    $coupon->save();

                    if(env('PROMO_EMAIL_ENABLED') === true){
                        // Mail::to($user->email)->queue(new InvalidCoupon(['reason' => $request->input('reason')]));

                        if($coupon->prize_id != null){
                            Mail::to($user->email)->queue(new InvalidPrize(['reason' => $request->input('reason')]));
                        }

                        if($coupon->cna_mini_curso == 1){
                            Mail::to($user->email)->queue(new InvalidCNAMini(['reason' => $request->input('reason')]));
                        }                    
                    }
                } else {
                    $countValidCouponsUser = Coupon::countValidCounponsUser($user->id);
                    $userCountInstantPrize = Coupon::where(['user_id' => $coupon->user_id, 'status' => 1])->whereNotNull('prize_id')->count();
                    $userCountPrizeValue = Coupon::where(['user_id' => $coupon->user_id, 'status' => 1])->whereNotNull('prize_value')->count();

                    if($countValidCouponsUser >= env('PROMO_MAX_VALID_COUPONS')){
                        throw new \Exception('Usuário já possui o número máximo de cupons validados.');
                    } else {                    
                        /*
                        else if($coupon->prize_id != null && $request->input('pass_picpay') != '1281') {
                            throw new \Exception('Código incorreto para transferência PicPay.');
                        } else {
                        */

                        // $coupon->admin_video_valid_social_network = $request->input('has_link_social') == 1 ? 1 : 0;
                        // $coupon->admin_video_valid_friends = $coupon->admin_video_valid_friends == 1 ? 1 : 0;

                        // $coupon->save();

                        $calculateCodesAndLuckyNumbers = Coupon::calculateCodesAndLuckyNumbers($coupon->id);

                        $luckyNumbers = (env('PROMO_LUCKYNUMBERS_ADMIN_VALIDATE') === true) ? Coupon::setLuckyNumbers($coupon, $calculateCodesAndLuckyNumbers) : null;
                        $instantPrize = (env('PROMO_INSTANTPRIZE_ADMIN_VALIDATE') === true) ? Coupon::setInstantPrize($coupon, $calculateCodesAndLuckyNumbers) : null;
                        $codes = (env('PROMO_CODES_ADMIN_VALIDATE') === true) ? Coupon::setCodes($coupon, $calculateCodesAndLuckyNumbers) : null;
                        $prizeValue = (env('PROMO_PRIZE_VALUE_ADMIN_VALIDATE') === true) ? Coupon::setPrizeValue($coupon->amount) : null;
                            
                        $coupon->save();
                        $coupon->refresh();

                        $dataPrize = Prize::find($coupon->prize_id);
                            
                        if(env('PROMO_EMAIL_ENABLED') === true){

                            if($coupon->prize_id != null){
                                $dataPrize = Prize::find($coupon->prize_id);
                                Mail::to($user->email)->queue(new ValidPrize(['codes' => $codes, 'prizeType' => [] ,'luckyNumbers' => $luckyNumbers, 'view' => "mail.validCoupon{$dataPrize->text_value}"]));
                            }

                            if($coupon->cna_mini_curso == 1){
                                Mail::to($user->email)->queue(new ValidCNAMini());
                            }
                        }

                        if(env('PROMO_HAS_LUCKY_NUMBERS') === true){
                            if($request->input('has_link_social') == 1){
                                $luckySocial = Coupon::setLuckyNumbers($coupon, (object) ['luckyNumbers' => 2]);
                            }

                            if($request->input('has_friends') == 1){
                                $luckyFriends =Coupon::setLuckyNumbers($coupon, (object) ['luckyNumbers' => 2]);
                            }
                        }

                        if(env('PROMO_HAS_INSTANT_PRIZE') === true){
                            if($user->whatsappuser_id !== null && $coupon->prize_id != null){
                                $whatsapp = new ZenviaWhatsapp();
                                $dataPrize = Prize::find($coupon->prize_id);
                                $msg = "*Promo Fome de Aprender Panco*: O seu cupom fiscal foi validado com sucesso. O seu prêmio instantâneo no valor de *R$ ". $dataPrize->text_value.",00* será creditado via PicPay. O código interno para controle é: " . $dataPrize->hash. "*\nCaso você tenha alguma dúvida, acesse https://promopanco.com.br e entre em contato. Obrigado por participar.";
                            
                                $whatsapp->send('55' . $user->mobile, $msg);
                            }

                            if($coupon->prize_id != null && $userCountInstantPrize <  env('PROMO_INSTANT_PRIZE_MAX_PER_USER')){
                                if(env('APP_ENV') != 'local'){
                                    if(env('PICPAY_ENABLED') === true){
                                        Log::debug('Picpay: Chamei no Admin');
                                        ProcessPicpay::dispatch($coupon, $user->cpf, (int) $dataPrize->text_value, false);
                                    } else {
                                        Log::debug('Picpay: Desabilitado');
                                    }
                                }
                            }
                        }


                        if(env('PROMO_HAS_PRIZE_VALUE') === true){
                            if($user->whatsappuser_id !== null && $coupon->prize_value != null){
                                $whatsapp = new ZenviaWhatsapp();
                                $msg = "*Promo Fome de Aprender Panco*: O seu cupom fiscal foi validado com sucesso. O seu prêmio instantâneo no valor de *R$ ". str_replace('.', ',', $coupon->prize_value )."* será creditado via PicPay. \nCaso você tenha alguma dúvida, acesse https://promopanco.com.br e entre em contato. Obrigado por participar.";
                            
                                $whatsapp->send('55' . $user->mobile, $msg);
                            }

                            /*
                            if($coupon->prize_value != null && $userCountInstantPrize <  env('PROMO_PRIZE_VALUE_MAX_PER_USER')){
                                if(env('APP_ENV') == 'local'){
                                    ProcessPicpay::dispatch($coupon, $user->cpf, $coupon->prize_value, false);
                                }
                            }
                            */
                        }
                    }
                } 
            });
            
            $out->status = true;
            $out->redirect = route('admin');
        } catch(Exception $e){
            $out->status = false;
            $out->error = "Houve um erro ao validar o cupom. Tente novamente! <br />" . $e->getMessage();
        }

        return response()->json($out);
    }

    public function saveCodes(Request $request)
    {
        $data['stores'] = DB::table('stores')->where('status', 1)->get();
        $data['prizes'] = DB::table('prizes')->where('status', 1)->get();

        return view('admin.codes.index', $data);
    }

    public function saveCodesAction(Request $request)
    {
        $out = new \stdClass();

        $rootDir = 'public/codes';

        $filenameWithExt = $request->file('codes')->getClientOriginalName();
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        $extension = $request->file('codes')->getClientOriginalExtension();
        $fileNameToStore = $filename.'_'.time().'.'.$extension;

        if($extension == 'csv'){
            $saveCode = $request->file('codes')->storeAs($rootDir, $fileNameToStore);
            
            if($saveCode){
                $contentFile = readCSV('../storage/app/' . $saveCode, ['delimiter' => ',']);

                try {

                    DB::transaction(function () use ($contentFile, $request) {
                
                        foreach($contentFile as $line){

                            if(!empty($line[0])){
                                $hasCode = Code::where(['code' => $line[0]])->first();

                                if($hasCode){
                                    throw new \Exception('O código ' . $line[0] . ' já existe. Operação cancelada.');
                                }
                                
                                $create = Code::create([
                                    'code' => $line[0],
                                    'prizeType' => $request->input('prize'),
                                    // 'store_id' => $request->input('store'),
                                    'status' => 0,
                                ]);
                            }
                        }
                    });

                    $out->status = true;
                    $out->redirect = route('admin.codes.index');
                } catch(Exception $e){
                    $out->status = false;
                    $out->error = 'Houve um erro ao validar o cupom. Tente novamente!';
                }
            }
        } else {
            $out->status = false;
            $out->error = 'O arquivo deve ser no formato CSV';
        }

        

        return response()->json($out);
    }

    public function whatsapp($mobile = null){

        $fullMobile = '55' . $mobile;
        
        // $messages = WhatsappLog::whereIn('author', [env('WHATSAPP_ZENVIA_UID'), $fullMobile])->->orderBy('id', 'ASC')->get();

        $messages = WhatsappLog::where('author', env('WHATSAPP_ZENVIA_UID'))
                                    ->where('to', $fullMobile)
                                    ->orWhere(function($query) use ($fullMobile) {
                                        $query->where('author', $fullMobile)
                                            ->where('to', env('WHATSAPP_ZENVIA_UID'));
                                    })
                                    ->orderBy('id', 'ASC')
                                    ->get();

        // $getLog = WhatsappLog::select('author')->distinct()->where('author', '!=', env('WHATSAPP_ZENVIA_UID'))->get();

        // $data['users'] = WhatsappUser::selectRaw('DISTINCT author, whatsapp_log.id')->leftJoin('whatsapp_log', 'whatsapp_users.full_mobile', '=', 'whatsapp_log.author')->orderBy('whatsapp_log.id', 'DESC')->get();
        /*$data['users'] = WhatsappLog::select("REGEXP_REPLACE(whatsapp_log.author, '^55', '') AS filteredAuthor")
                                    ->where('author', '!=', env('WHATSAPP_ZENVIA_UID'))
                                    ->leftJoin('whatsapp_users', 'whatsapp_users.mobile', '=', "filteredAuthor")
                                    ->groupBy('filteredAuthor')
                                    ->orderBy('whatsapp_log.id', 'DESC')
                                    ->get();*/
        $data['users'] = WhatsappLog::selectRaw('author, MAX(created_at)')->distinct()->where('author', '!=', env('WHATSAPP_ZENVIA_UID'))->groupBy('author')->orderBy('MAX(created_at)', 'DESC')->get();

        $data['messages'] = [];
        $data['currentMobile' ] = NULL;
        if($mobile !== NULL){
            $data['messages'] = WhatsappLog::parseWhatsappMessages($messages);
            $data['currentMobile'] = $mobile;
        }

        return view('admin.whatsapp.index', $data);
    }
    
    public function picpay(Request $request)
    {
        $data['coupons'] = Coupon::where('status', 1)->whereNotNull('prize_id')->whereNull('picpay_return')->get();
        
        return view('admin.picpay.index', $data);
    }

    public function picpayAction(Request $request){

        $couponID = decrypt($request->query('id'), env('APP_NAME'));
        $coupon = Coupon::findOrFail($couponID);
        $user = User::findOrFail($coupon->user_id);
        $dataPrize = Prize::find($coupon->prize_id);

        
        if(env('APP_ENV') == 'local' && strlen($coupon->picpay_return) == 0){
            ProcessPicpay::dispatch($coupon, $user->cpf, (int) $dataPrize->text_value, false);
        }
        return redirect()->route('admin.picpay.index');
    }
    

    public function testeWhatsapp(){
        
        $whatsapp = new ZenviaWhatsapp();

        $img = 'https://promopanco.com.br/storage/_upload/SNFAh5CtpJ12oPTtbo7LjcCjmUaIHUSp547JWr5M.png';

        $msg = "Pomoção Vá De Broto Legal: o seu cupom prêmio instantâneo foi validado com sucesso. Caso você tenha alguma dúvida, acesse https://promopanco.com.br e entre em contato. Obrigado por participar.";
        
        $data['url'] = $img;
        // $data['url_thumb'] = $img;
        $data['caption'] = 'Teste';
        $data['description'] = 'Teste Mais Longo';

        $whatsapp->sendImage('5511996393535', $data);
    }

    public function exportLuckyNumbers(){
        return Excel::download(new LuckyNumbersExport, 'lucky_numbers.xlsx');
    }
}
