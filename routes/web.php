<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ZenviaController;
use App\Http\Controllers\WhatsappController;

use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes(['verify' => false]);

Route::get('/', [SiteController::class, 'index'])->name('start');
Route::get('cadastro', [SiteController::class, 'cadastro'])->name('cadastro');
Route::get('produtos', [SiteController::class, 'produtos'])->name('produtos');
Route::get('lojas', [SiteController::class, 'lojas'])->name('lojas');
Route::get('comprar', [SiteController::class, 'comprar'])->name('comprar');
Route::get('duvidas', [SiteController::class, 'duvidas'])->name('duvidas');
Route::get('sorteios', [SiteController::class, 'sorteios'])->name('sorteios');
Route::get('regulamento', [SiteController::class, 'regulamento'])->name('regulamento');
Route::get('fale-conosco', [SiteController::class, 'faleConosco'])->name('fale-conosco');
Route::post('fale-conosco/action', [SiteController::class, 'faleConoscoAction'])->name('fale-conosco.action');

Route::get('minha-conta/historico', [SiteController::class, 'historico'])->name('historico')->middleware('auth');
Route::get('minha-conta/cadastro-sucesso', [SiteController::class, 'cadastroSucesso'])->name('cadastro.sucesso')->middleware('auth');
Route::get('minha-conta/cupom-sucesso', [SiteController::class, 'cupomSucesso'])->name('cupom.sucesso')->middleware('auth');
Route::get('minha-conta/cadastro/cupom', [CouponController::class, 'registerCoupon'])->name('cadastro.cupom')->middleware('auth');
Route::post('minha-conta/cadastro/cupom/action', [CouponController::class, 'registerCouponAction'])->name('cadastro.cupom.action')->middleware('auth');
Route::post('minha-conta/cadastro/cupom/friend', [CouponController::class, 'registerLuckyFriendAction'])->name('cadastro.cupom.friend.action')->middleware('auth');
Route::post('minha-conta/cadastro/cupom/video/action', [CouponController::class, 'registerVideoAction'])->name('cadastro.video.action')->middleware('auth');
Route::get('minha-conta/whatsapp/cupom/importar/{id}', [CouponController::class, 'importCoupon'])->name('whtasapp.cupom.importar')->middleware('auth');
Route::post('minha-conta/whatsapp/cupom/importar/action', [CouponController::class, '@importCouponAction'])->name('whtasapp.cupom.importar.action')->middleware('auth');
Route::post('minha-conta/cadastro/update/action', [CouponController::class, 'updateProfileAction'])->name('cadastro.update.action')->middleware('auth');
Route::post('minha-conta/cadastro/novo-regulamento/accept', [CouponController::class, 'acceptNewRulesAction'])->name('cadastro.accept.new.rules.action')->middleware('auth');


Route::middleware('can:accessAdmin')->group(function() {
    Route::get('admin', [AdminController::class, 'index'])->name('admin');
    Route::get('admin/teste', [AdminController::class, 'testeWhatsapp'])->name('adminTeste');
    Route::get('admin/instant-prize-coupons', [AdminController::class, 'instantPrizeCoupons'])->name('instantPrizeCoupons');
    Route::get('admin/cupons/com-video', [AdminController::class, 'videoCoupons'])->name('videoCupons');
    Route::get('admin/cupons/first', [AdminController::class, 'firstCoupon'])->name('firstCoupon');
    Route::get('admin/valid-coupons', [AdminController::class, 'validCoupons'])->name('validCoupons');
    Route::get('admin/invalid-coupons', [AdminController::class, 'invalidCoupons'])->name('invalidCoupons');
    Route::get('admin/cupom/validacao', [AdminController::class, 'index'])->name('admin.cupom.validacao');
    Route::get('admin/cupom/todos/', [AdminController::class, 'index'])->name('admin.cupom.validar');
    Route::get('admin/cupom/validar/{id}', [AdminController::class, 'validateCoupon'])->name('admin.cupom.validar');
    Route::post('admin/cupom/validar/action', [AdminController::class, 'validateCouponAction'])->name('admin.cupom.validar.action');
    // Route::get('admin/whatsapp', [AdminController::class, 'whatsapp'])->name('admin.whatsapp.index');
    Route::get('admin/whatsapp/{mobile?}', [AdminController::class, 'whatsapp'])->name('admin.whatsapp.index');
    Route::get('admin/codes', [AdminController::class, 'saveCodes'])->name('admin.codes.index');
    Route::post('admin/codes/action', [AdminController::class, 'saveCodesAction'])->name('admin.codes.action');
    Route::get('admin/picpay', [AdminController::class, 'picpay'])->name('admin.picpay.index');
    Route::get('admin/picpay/action', [AdminController::class, 'picpayAction'])->name('admin.picpay.action');
    Route::get('admin/export/luckynumbers', [AdminController::class, 'exportLuckyNumbers'])->name('admin.exportLuckyNumbers');
});



Route::get('model', 'Example@model')->middleware('verified');
Route::get('read', 'Example@read');
Route::get('mail', 'Example@mail');
Route::get('copy', 'Example@copy');
Route::get('user', 'Example@user');
Route::get('hash', function(){
    return Hash::make('12345');
});
Route::post('whatsapp', [WhatsappController::class, 'receive']);
Route::get('whatsapp/send', [WhatsappController::class, 'send']);

Route::post('whatsapp/zenvia/message', [ZenviaController::class, 'webhookMessage']);
Route::post('whatsapp/zenvia/status', [ZenviaController::class, 'webhookStatus']);
Route::post('whatsapp/zenvia/send/message', [ZenviaController::class, 'sendMessage']);
// Route::view('/admin', 'admin/index');

Route::post('login/authenticate', [LoginController::class, 'authenticate'])->name('login.auth');

Route::get('home', 'HomeController@index')->name('home');

Route::get('/r/{type}/{influencer}', 'SiteController@influencers');

Route::get('picpay', [SiteController::class, 'picpay'])->name('picpay');
Route::get('picpay/project', [SiteController::class, 'PicPayCreateProject'])->name('picpay.create.project');
Route::get('picpay/projects', [SiteController::class, 'PicPayProjects'])->name('picpay.projects');




Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/queueMail', [CouponController::class, 'send'])->name('queueMail');
Route::get('/registerCoupon', [CouponController::class, 'registerCouponAction'])->name('coupon.register.action');

require __DIR__.'/auth.php';

Auth::routes();
Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:4|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) use ($request) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->save();

            $user->setRememberToken(Str::random(60));

            event(new PasswordReset($user));
        }
    );

    return $status == Password::PASSWORD_RESET
                ? redirect()->route('start')->with('status', __($status))
                : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/fixError', [App\Http\Controllers\SiteController::class, 'fixError'])->name('fixError');


Route::get('/cloudinary', [HomeController::class, 'cloudinary'])->name('cloudinary');

Route::get('/mail', function () {
    return new App\Mail\CNAMini([]);
});