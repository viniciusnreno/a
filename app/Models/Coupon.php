<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Models\Store;
use App\Models\Raffle;
use App\Models\User;
use App\Models\Product;
use App\Models\Code;
use App\Models\LuckyNumber;

use Ramsey\Uuid\Uuid;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'raffle_id', 'prize_id', 'whatsappchat_id', 'invoice', 'company_cnpj', 'coupon_number', 'amount', 'buy_date',
        'state', 'reason', 'chain_store', 'store_id', 'status', 'instant_prize_reason', 'required_product', 'ip', 'company_name',
        'video_url', 'video_social_network', 'video_friends', 'video_public_id', 'cna_mini_curso'
    ];

    public static function buildCreateCoupon($request){
        $hasVideo = false;

        $filePath = $request->file('cupomFile')->store('public/_upload');

        if($request->file('cupomVideo')){
            $videoInfo = \Cloudinary::uploadVideo($request->file('cupomVideo')->getRealPath(), ['folder' => 'desafio-saudavel']);
            
            $hasVideo = true;

            if(isset($videoInfo->getResponse()['duration'])){
                if($videoInfo->getResponse()['duration'] > 60){
                    return ['error' => 'O vídeo precisa ter no máximo 1 minuto'];
                }
            } else {
                return ['error' => 'Falha ao ler as propriedades do vídeo'];
            };
        }
        $companyCNPJ = preg_replace('/[^\d]/', '', $request->input('documento_estabelecimento'));

        return [
                'user_id' => \Auth::user()->id,
                'raffle_id' => Raffle::currentRaffleID(),
                'store_id' => null, // $request->input('store'),
                'prize_id' => null, //Prize::getActivePrize()->id, //(int) $request->input('prize'),
                'invoice' => str_replace('public/', 'storage/', $filePath),
                'company_cnpj' => $companyCNPJ,
                'company_name' => $request->input('nome_estabelecimento'),
                'coupon_number' => $request->input('nota_fiscal'),
                'amount' => str_replace( array( 'R$', '.', ',' ), array( '', '', '.' ), trim($request->input('valor')) ),
                'required_product' => 'Sim',
                'cna_mini_curso' => Coupon::isFirstCoupon() == true ? 1 : 0,
                'buy_date' => dateEN($request->input('data_emissao'), false),
                'ip' => $request->ip(),
                'store_id' => null, //Store::hasStore(['store_cnpj' => $companyCNPJ])->id
        ];
    }

    public static function isValidCoupon($formData){

        $validates = [
            // 'isValidStore',
            'isMaxAmountAllowed',
            'isMinAmountAllowed',
            // 'isTotalAmountAllowed',
            'isValidImage',
            'isValidImageSize',
            'isValidImageMimeType',
            // 'isMinProductsAllowed',
            'isDuplicateCoupon',
            'isMaxCouponsAllowed',
            // 'isValidState',
            'hasMinProducts',
            // 'hasStore',
        ];

        $out = new \StdClass();
        $out->status = true;

        $formData = (object) $formData;

        foreach($validates as $validate){
            $current = self::$validate($formData);
            if($current->status === false){
                $out->status = false;
                $out->error = $current->error;
                break;
            }
        }

        return $out;
    }

    public static function isFirstCoupon($userID = null){
        $userID = $userID == null ? Auth::user()->id : $userID;

        $countCoupon = self::where('user_id', $userID)->count();

        if($countCoupon  > 0){
            return false;
        }
        return true;
    }

    public static function isValidState($formData){
        $validStates = ['SP', 'RJ', 'MG', 'PR'];
        return (!in_array($formData->input('estado'), $validStates) ) ? 
        (object) ['status' => false, 'error' => __('promo.coupon_validate_states', ['validStates' => implode(', ', $validStates)])] 
        : (object) ['status' => true];
    }

    public static function hasMinProducts($formData){
        return array_sum($formData->input('productQty')) < env('PROMO_MIN_PRODUCTS') ? 
        (object) ['status' => false, 'error' => __('promo.coupon_validate_min_products', ['minProducts' => env('PROMO_MIN_PRODUCTS')])] 
        : (object) ['status' => true];
    }

    public static function hasStore($formData){
        return !Store::find($formData->input('store')) ? 
        (object) ['status' => false, 'error' => __('promo.coupon_validate_store')] 
        : (object) ['status' => true];
    }

    public static function isMaxCouponsAllowed($formData){
        return Coupon::countCounponsUser(\Auth::user()->id) >= env('PROMO_MAX_COUPONS') ?
        (object) ['status' => false, 'error' => __('promo.coupon_validate_max_coupons')] 
        : (object) ['status' => true];
    }

    public static function isValidImage($formData){
        return $formData->file('cupomFile')->isValid() === false ?
                (object) ['status' => false, 'error' => __('promo.coupon_validate_invalid_image')] 
                : (object) ['status' => true];
    }

    public static function isValidImageSize($formData){
        return $formData->file('cupomFile')->getSize() / 1024 > 5000 ?
                (object) ['status' => false, 'error' => __('promo.coupon_validate_invalid_image_size')] 
                : (object) ['status' => true];
    }

    public static function isValidImageMimeType($formData){

        $validMime = ['image/jpeg', 'image/jpg', 'image/png'];

        return (!in_array($formData->file('cupomFile')->getMimeType(), $validMime)) ?
                (object) ['status' => false, 'error' => __('promo.coupon_validate_invalid_image_mime')] 
                : (object) ['status' => true];
    }

    public static function isTotalAmountAllowed($formData){
        $totalAmount = Coupon::where('user_id', \Auth::user()->id )->sum('amount');

        return ($totalAmount >= env("PROMO_MAX_AMOUNT")) ? 
                (object) ['status' => false, 'error' => __('promo.coupon_validate_total_amount')] 
                : (object) ['status' => true];
    }

    public static function isMaxAmountAllowed($formData){
        $amount = str_replace( array( 'R$', '.', ',' ), array( '', '', '.' ), trim($formData->input('valor')));

        return env("PROMO_MAX_AMOUNT") && $amount > env("PROMO_MAX_AMOUNT") ? 
                (object) ['status' => false, 'error' => __('promo.coupon_validate_max_amount', ['maxValue' => env("PROMO_MAX_AMOUNT")])] 
                : (object) ['status' => true];
    }

    public static function isMinAmountAllowed($formData){
        $amount = str_replace( array( 'R$', '.', ',' ), array( '', '', '.' ), trim($formData->input('valor')));
        return env("PROMO_MIN_AMOUNT") && $amount < env("PROMO_MIN_AMOUNT") ? 
                (object) ['status' => false, 'error' => __('promo.coupon_validate_min_amount', ['minValue' => env("PROMO_MIN_AMOUNT")])] 
                : (object) ['status' => true];
    }

    public static function isValidStore($formData){
        $findStore = Store::hasStore(['store_cnpj' => preg_replace('/[^\d]/', '', $formData->input('documento_estabelecimento'))]);
        return !$findStore ? 
                (object) ['status' => false, 'error' => __('promo.coupon_validate_store')] 
                : (object) ['status' => true];
    }

    public static function isMinProductsAllowed($formData){
        return ((int) $formData->input('qtd') < env('PROMO_MIN_PRODUCTS')) ? 
                (object) ['status' => false, 'error' => __('promo.coupon_validate_min_products', ['minProducts' => env('PROMO_MIN_PRODUCTS')])] 
                : (object) ['status' => true];
    }

    public static function isDuplicateCoupon($formData){
        $companyCNPJ = preg_replace('/[^\d]/', '', $formData->input('documento_estabelecimento'));

        $searchCoupon = Coupon::where(['company_cnpj' => $companyCNPJ, 'coupon_number' => $formData->input('nota_fiscal')])->get();

        return (count($searchCoupon) > 0) ?
            (object) ['status' => false, 'error' => __('promo.coupon_validate_duplicate_coupon')] 
            : (object) ['status' => true];
    }

    public static function setLuckyNumbers($coupon, $howMany){
        $luckyNumbers = [];

        for($i = 0; $i < $howMany->luckyNumbers; $i++){
            
            $createLuckyNumber = LuckyNumber::create([
                'user_id' => $coupon->user_id,
                'coupon_id' => $coupon->id,
                'raffle_id' => $coupon->raffle_id,
                'number' => LuckyNumber::generate(),
                'ip' => \Request::ip(),
                'final' => 0
            ]);

            $luckyNumbers[] = $createLuckyNumber->number;
        }

        return $luckyNumbers;
    }

    public static function setInstantPrize($coupon){
        $out = [];

        if(env('PROMO_HAS_INSTANT_PRIZE') === true){
            $codes = [];

            $winnerInstantPrize = ( (Coupon::count() % env('PROMO_INSTANT_PRIZE_INTERVAL')) == 0) ? true : false;            
            $qtdProductsAllowed = (int) $coupon->products->sum('pivot.qty');
            
            $avaliablePrizes = DB::table('prizes')->where(['instant' => 1, 'status' => 1])->inRandomOrder()->first();

            if($avaliablePrizes === null){
                $out['hasPrize'] = false;
                $out['reason'] = 'Não há prêmios disponíveis!';

                $coupon->fresh();
                $coupon->instant_prize_reason = $out['reason'];
                $coupon->save();

                return $out;
            }

            $totalInstantPrizes = Coupon::where(['prize_id' => $avaliablePrizes->id])->count();
            $userCountInstantPrize = Coupon::where('user_id', $coupon->user_id)
                                            ->whereNotNull('prize_id')
                                            ->where(function ($query) {
                                                $query->where('status', '!=', 0)
                                                    ->orWhereNull('status');
                                            })->count();

            if($qtdProductsAllowed < env('PROMO_MIN_PRODUCTS')){

                $out['hasPrize'] = false;
                $out['reason'] = 'Não atingiu a quantidade mínima de produtos adquiridos ('. env('PROMO_MIN_PRODUCTS') .')';

                $coupon->fresh();
                $coupon->instant_prize_reason = $out['reason'];
                $coupon->save();

                return $out;
                
            }             
                                            
            if($winnerInstantPrize === false){
                $out['hasPrize'] = false;
                $out['reason'] = 'Não é múltiplo de '  . env('PROMO_INSTANT_PRIZE_INTERVAL') . '.';

                $coupon->fresh();
                $coupon->instant_prize_reason = $out['reason'];
                $coupon->save();
                
                return $out;
            }
            
            if($totalInstantPrizes >= $avaliablePrizes->qty){
                
                $out['hasPrize'] = false;
                $out['reason'] = 'Total de prêmios já foi alcançado.(' . $avaliablePrizes->id . ')';

                $coupon->fresh();
                $coupon->instant_prize_reason = $out['reason'];
                $coupon->save();

                return $out;
                
            } 

            if($userCountInstantPrize >= env('PROMO_INSTANT_PRIZE_MAX_PER_USER')){
                $out['hasPrize'] = false;
                $out['reason'] = 'Você já ganhou o número máximo ('. env('PROMO_INSTANT_PRIZE_MAX_PER_USER') . ') de prêmios instantâneos por participante ou possui prêmios pendentes de validação.';

                $coupon->fresh();
                $coupon->instant_prize_reason = $out['reason'];
                $coupon->save();

                return $out;
            } 


            session()->flash('instant_prize_hash', Uuid::uuid4());

            $coupon->fresh();
            $coupon->prize_id = $avaliablePrizes->id;
            $coupon->instant_prize_hash = session('instant_prize_hash');
            
            if($coupon->save()){
                $out['hasPrize'] = true;
                $out['prize'] = $avaliablePrizes;
                $out['hash'] = session('instant_prize_hash');
            } else {
                $out['hasPrize'] = false;
                $out['reason'] = 'Falha ao salvar o prêmio instantâneo.';
            }
        }
        return $out;
    }

    public static function calculateCodesAndLuckyNumbers($couponID){
        
        $out = new \stdClass();
        $out->codes = 0;
        $out->luckyNumbers = 0;
        $out->errors = [];

        
        $coupon = self::find($couponID);

        $out->luckyNumbers = LuckyNumber::calculateLuckyNumbers($coupon);
        $out->codes = Code::calculateCodes($coupon);

        return $out;
    }

    public static function setCodes($coupon, $howMany){

        $out = new \StdClass();

        if(env('PROMO_HAS_CODE') === true){
            for($i = 0; $i < $howMany->codes; $i++){
                
                $code = Code::where(['status' => 0, 'prizeType' => $coupon->prize_id])->inRandomOrder()->first();
            
                if(!$code){
                    throw new \Exception('Não há mais códigos disponíveis');
                } else {
                    $coupon->codes()->attach($code->id, ['user_id' => $coupon->user_id]);

                    $code->fresh();
                    $code->status = 1;
                    $code->save();

                    $out->codes[$i] = $code->code;
                    $out->codeType[] = $code->prizeType;
                }

            }
        }

        return $out;
    }

    public static function setProducts($request, $coupon){
        if(env('PROMO_HAS_PRODUCTS') === true){
            if(env('PROMO_PRODUCTS_FIXED') === true){
                self::setFixedProducts($request, $coupon);
            } else {
                self::setVariableProducts($request, $coupon);
            }
        }
    }

    public static function setVariableProducts($request, $coupon){
        $productsNames = $request->input('productName');
        $productsQty = $request->input('productQty');

        // var_dump($productsNames);
        // var_dump($productsQty);

        foreach($productsNames as $key => $val){
            if($productsNames[$key] != "" && $productsQty[$key] > 0){
                $products[$productsNames[$key]] = [
                    'user_id' => \Auth::user()->id,
                    'coupon_id' => $coupon->id,
                    'qty' => $productsQty[$key]
                ];
            }
        }

        if(count($products) > 0){
            $coupon->products()->attach($products);
        }
    }

    public static function setFixedProducts($request, $coupon){
        $qtd = (int) $request->input('qtd');

        if($qtd > 0){
            $coupon->products()->attach(Product::where('status', 1)->first()->id, [
                'user_id' => \Auth::user()->id,
                'coupon_id' => $coupon->id,
                'qty' => $qtd
            ]);
        }
    }

    public static function calculatePrizeValue($amount){

        $out = new \StdClass();

        $totalPrize = 0;
        
        if(env('PROMO_HAS_PRIZE_VALUE') === true){
            // print '--' . $amount . '--' . env('PROMO_PRIZE_VALUE_AMOUNT');
            $countPrize = floor($amount / env('PROMO_PRIZE_VALUE_AMOUNT'));
            
            $totalPrize = env('PROMO_PRIZE_VALUE_PRIZE') * 1;
        }

        $out->prize = ($totalPrize >= 10 && $totalPrize < 100) ? $totalPrize : null;

        return $out;
    }


    public static function setPrizeValue($coupon){

        $out = new \StdClass();
        $out->hasPrizeValue = false;
        $out->amount = 0;

        $prizeValue = self::calculatePrizeValue($coupon->amount);

        return $out;

        if($prizeValue != null){

            $coupon->prize_value = $prizeValue->prize;
            $coupon->prize_id = Prize::where('status', 1)->first()->id;

            if($coupon->save()){
                $out->hasPrizeValue = true;
                $out->amount = $prizeValue->prize;
            }
        }

        return $out;

    }

    public static function validateWhatsappChat($item){
        $out = new \StdClass();
        DB::transaction(function () use ($item, &$out){

            $raffle = Raffle::currentRaffle();
            
            $getWhatsappUser = DB::table('whatsapp_users')->where('id', $item->whatsappuser_id)->where('completed', 1)->first();

            if(!$getWhatsappUser){
                return $out;
            }

            if($getWhatsappUser->user_type == 'F'){

                if($getWhatsappUser->cpf != null){
                    $findUser = User::where('whatsappuser_id', $item->whatsappuser_id)
                                    ->orWhere('cpf', $getWhatsappUser->cpf)
                                    // ->orWhere('email', $getWhatsappUser->email)
                                    ->first();
                }
            } else {
                if($getWhatsappUser->cnpj != null){
                    $findUser = User::where('whatsappuser_id', $item->whatsappuser_id)
                                ->orWhere('cnpj', $getWhatsappUser->cnpj)
                                // ->orWhere('email', $getWhatsappUser->email)
                                ->first();
                }
            }

            if($findUser){
                $userID = $findUser->id;
            } else {
                $createUser = User::create([
                    'whatsappuser_id' => $item->whatsappuser_id,
                    'mobile' => $getWhatsappUser->mobile,
                    'name' => $getWhatsappUser->name,
                    'email' => $getWhatsappUser->email,
                    'user_type' => $getWhatsappUser->user_type,
                    'cpf' => $getWhatsappUser->cpf,
                    'cnpj' => $getWhatsappUser->cnpj,
                    'birth_date' => $getWhatsappUser->birth_date,
                    'state' => $getWhatsappUser->state,
                    'city' => $getWhatsappUser->city,
                    'receive_information_email' => 1,
                    'receive_information_whatsapp' => 1,
                    'agree_regulation' => 1,
                    'password' => $getWhatsappUser->password
                ]);

                $userID = $createUser->id;
            }

            $user = User::find($userID);

            $findCoupon = self::where(['company_cnpj' => $item->company_cnpj, 'coupon_number' => $item->coupon_number])->first();

            if($findCoupon){
                return $out;
            }

            $findStore = Store::where('store_cnpj', $item->company_cnpj)->first();

            if(!$findStore){
                // return $out;
            }

            $tmp = explode('/', $item->invoice_local);


            $createCoupon = self::create([
                'user_id' => $userID,
                'raffle_id' => $raffle['id'],
                'prize_id' => null, // Prize::getActivePrize()->id,
                'store_id' => null, //$findStore->id,
                'whatsappchat_id' => $item->id,
                'invoice' => 'storage/_upload/' . end($tmp),
                'company_cnpj' => $item->company_cnpj,
                'coupon_number' => $item->coupon_number,
                'buy_date' => $item->buy_date,
                'required_product' => $item->required_product,
                'cna_mini_curso' => self::isFirstCoupon($userID) == true ? 1 : 0,
                'amount' => $item->amount,
                'prize_value' => self::calculatePrizeValue($item->amount)
            ]);

            if($createCoupon){

                
                if(env('PROMO_HAS_PRODUCTS') === true){
                    $getProducts = DB::table('whatsapp_chat_products')->where('chat_id', $item->id)->where('completed', 1)->get();
                    foreach($getProducts as $itemProduct){
                        DB::table('coupon_product')->insert([
                            'user_id' => $userID,
                            'coupon_id' => $createCoupon->id,
                            'product_id' => $itemProduct->product_id,
                            'qty' => $itemProduct->qty
                            ]);
                        }
                    }
                
                $codesAndLuckyNumbers = self::calculateCodesAndLuckyNumbers($createCoupon->id);

                /*
                $createCoupon->refresh();
                $currentCoupon = Coupon::find($createCoupon->id);
                */

                Log::debug('Current Coupon: ' . json_encode($createCoupon));
                Log::debug('Current Coupon: ' . json_encode($createCoupon->products));

                $out->coupon = $createCoupon;
                $out->luckyNumbers = (env('PROMO_LUCKYNUMBERS_ADMIN_VALIDATE') === false) ? self::setLuckyNumbers($createCoupon, $codesAndLuckyNumbers) : null;
                $out->instantPrize = (env('PROMO_INSTANTPRIZE_ADMIN_VALIDATE') === false) ? self::setInstantPrize($createCoupon, $codesAndLuckyNumbers) : null;
                $out->codes = (env('PROMO_CODES_ADMIN_VALIDATE') === false) ? self::setCodes($createCoupon, $codesAndLuckyNumbers) : null;
                $out->prizeValue = (env('PROMO_PRIZE_VALUE_ADMIN_VALIDATE') === false) ? self::setPrizeValue($createCoupon) : null;
            }
        });

        return $out;
    }

    /*
    public static function luckyFriend($user){
        $getLucky = Coupon::where('friend_email', $user->email)->where('friend_status', 0)->get();

        foreach($getLucky as $item){
            $luckyNumber = LuckyNumber::generate();

            LuckyNumber::create([
                'user_id' => null,
                'coupon_id' => null,
                'raffle_id' => $item->raffle_id,
                'number' => $luckyNumber,
                'ip' => \Request::ip(),
                'final' => 1,
                'parent_coupon_id' => $item->id
            ]);
        }
    }
    */

    public static function luckyFriend($coupon){

        $searchUser = User::where('email', $coupon->friend_email)->first();

        if($coupon->friend_email !== null && $coupon->friend_status == 0){
            $luckyNumber = LuckyNumber::generate();

            LuckyNumber::create([
                'user_id' => ($searchUser != null) ? $searchUser->id : null,
                'coupon_id' => null,
                'raffle_id' => $coupon->raffle_id,
                'number' => $luckyNumber,
                'ip' => \Request::ip(),
                'final' => 1,
                'parent_coupon_id' => $coupon->id
            ]);

            $coupon->friend_status = 1;
            $coupon->save();
        }
    }

    public static function payBackFriend($coupon){

        $getCoupon = Coupon::where('friend_email', $coupon->user->email)->where('friend_payback', 0)->get();

        foreach($getCoupon as $itemCoupon){
            $luckyNumber = LuckyNumber::generate();

            LuckyNumber::create([
                'user_id' => $itemCoupon->user_id,
                'coupon_id' => $itemCoupon->id,
                'raffle_id' => $itemCoupon->raffle_id,
                'number' => $luckyNumber,
                'ip' => \Request::ip(),
                'final' => 1,
                'parent_coupon_id' => $coupon->id
            ]);

            $itemCoupon->friend_payback = 1;
            $itemCoupon->save();
        }
    }

    public static function countCounponsUser($userID){
        return Coupon::where(['user_id' => $userID])->count();
    }

    public static function countValidCounponsUser($userID){
        return Coupon::where(['user_id' => $userID, 'status' => 1])->count();
    }

    public static function sumCoupons($userID){
        return self::where(['user_id' => $userID])->sum('amount');        
    }

    public function products(){
        return $this->belongsToMany('App\Models\Product')->withPivot('qty');
    }

    public function codes(){
        return $this->belongsToMany('App\Models\Code')->withTimestamps();
    }

    public function stores(){
        return $this->belongsToMany('App\Models\Store');
    }

    public function store(){
        return $this->belongsTo('App\Models\Store');
    }

    public function code(){
        return $this->belongsTo('App\Models\Code');
    }

    public function luckyNumbers(){
        return $this->hasMany('App\Models\LuckyNumber');
    }

    public function friendLuckyNumber(){
        return $this->hasOne('App\Models\LuckyNumber', 'parent_coupon_id');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function raffle(){
        return $this->belongsTo('App\Models\Raffle');
    }

    public function prize(){
        return $this->hasOne('App\Models\Prize','id','prize_id');
    }  
    
    public static function fixLuckyNumberCouponError(){
        $coupons = Coupon::all();
        print '<table border=1><thead>
                <td>#</td>
                <td>ID do Cupom</td>
                <td>Qtd. Números da Sorte</td>
                <td>Qtd. Correta</td>
        </thead>
        <tbody>';
        $i = 1;
        foreach($coupons as $item){
            $calculateCodesAndLuckyNumbers = Coupon::calculateCodesAndLuckyNumbers($item->id);
            $totalLuckyNumbers = $calculateCodesAndLuckyNumbers->luckyNumbers;
            if($item->luckyNumbers !== NULL){
                $currentCountLuckyNumbers = count($item->luckyNumbers);

                if($currentCountLuckyNumbers != $totalLuckyNumbers){
                    print "<tr>
                        <td>{$i}</td>
                        <td>{$item->id}</td>
                        <td>{$currentCountLuckyNumbers}</td>
                        <td>{$totalLuckyNumbers}</td>
                    </tr>";
                    $i++;
                }
            }
        }

        print '</tbody></table>';
    }
}
