{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Cupom</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-sm-12 col-xs-12">
            <form id="adminValidateCoupon" action="{{ route('admin.cupom.validar.action') }}" method="post">
                @csrf
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Dados do Cupom</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="company_cnpj">CNPJ</label>
                                    <input type="text" class="form-control" id="company_cnpj" name="company_cnpj" value="{{ $coupon->company_cnpj }}" disabled="disabled">
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="coupon_number">Número do Cupom (COO)</label>
                                    <input type="text" class="form-control" id="coupon_number" name="coupon_number" value="{{ $coupon->coupon_number }}" disabled="disabled">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="buy_date">Data da Compra</label>
                                    <input type="text" class="form-control" id="buy_date" name="buy_date" value="{{ dateBR($coupon->buy_date, false) }}" disabled="disabled">
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="amount">Valor</label>
                                    <input type="text" class="form-control" id="amount" name="amount" value="{{ $coupon->amount }}" disabled="disabled">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @if(gettype($coupon->status) === "NULL")
                                <div class="col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <label for="validate">Validado</label>
                                        <select class="form-control" id="validate" name="validate">
                                            <option value="" selected="selected">Escolha uma opção</option>
                                            <option value="1">Sim</option>
                                            <option value="0">Não</option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="col-sm-12 col-xs-12 d-none reasonContainer">
                                <div class="form-group">
                                    <label for="reason">Motivo para invalidar</label>
                                    <input type="text" class="form-control" id="reason" name="reason" placeholder="Preencha o motivo para invalidar o cupom">
                                </div>
                            </div>
                            
                            @if($coupon->prize_id !== null)
                                <div class="col-sm-12 col-xs-12 d-none passwordPicPay">
                                    <div class="form-group">
                                        <label for="pass_picpay">Senha PicPay</label>
                                        <input type="text" class="form-control" id="pass_picpay" name="pass_picpay" placeholder="Preencha a senha para transferência no PicPay">
                                    </div>
                                </div>
                            @endif

                            <div class="col-sm-12 col-xs-12">
                                <h4>Dados do Usuário</h4>
                                <div class="col-sm-4 col-xs-12">
                                    <div class="form-group">
                                        <label for="reason">E-Mail</label>
                                        <input type="text" class="form-control" id="user_email" name="user_email" value="{{ $user->email }}" disabled="disabled">
                                    </div>
                                </div>

                                <div class="col-sm-4 col-xs-12">
                                    <div class="form-group">
                                        <label for="reason">Cidade</label>
                                        <input type="text" class="form-control" id="user_city" name="user_city" value="{{ $user->city }}" disabled="disabled">
                                    </div>
                                </div>

                                <div class="col-sm-4 col-xs-12">
                                    <div class="form-group">
                                        <label for="reason">Telefone</label>
                                        <input type="text" class="form-control" id="user_mobile" name="user_mobile" value="({{ substr($user->mobile, 0, 2) }}) {{ substr($user->mobile, 2) }}" disabled="disabled">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if(env('PROMO_HAS_PRODUCTS') === true)
                            <div class="row">
                                <div class="col-sm-12 col-xs-12">
                                    <h4>Produtos Informados</h4>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th style="width: 90%">Produto</th>
                                                <th class="text-center">Quantidade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($coupon->products as $item)
                                                <tr>
                                                    <td>{{ $item->product }}</td>
                                                    <td class="text-center">{{ $item->pivot->qty }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if(env('PROMO_HAS_CODE') === true)
                        <div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <h4>Simulação - Códigos</h4>
                                <p class="text-info">Este cupom irá gerar <strong>{{ $codesAndLuckyNumbers->codes }}</strong> {{ Str::plural('código', $codesAndLuckyNumbers->codes) }}</p>
                                <p class="text-warning">Este usuário possui, no momento, <strong>{{ $currentCountCodes }}</strong> {{ Str::plural('código', $currentCountCodes) }} @if($currentCountCodes >= 10). Esse usuário atingiu o número máximo de participações.@endif</p>
                                <br />
                                @foreach($avaliablePrizes['code'] as $itemPrize)
                                <p style="color: #c54343;"><strong>Há {{ $itemPrize['qty'] }} códigos {{ $itemPrize['prize'] }} disponíveis</strong><p>
                                @endforeach
                                @isset($codesAndLuckyNumbers->errors)
                                    <p class="text-danger">{!! implode("<br />", $codesAndLuckyNumbers->errors) !!}</p>
                                @endisset
                            </div>
                        </div>
                        @endif


                        @if(env('PROMO_HAS_INSTANT_PRIZE') === true)
                            <div class="row">
                                <div class="col-sm-12 col-xs-12">
                                    <h4>Prêmio Instantâneo</h4>
                                    @if($coupon->prize_id != null)
                                        <p class="text-info">{{ $coupon->prize->prize}}</p>
                                    @else 
                                        @if($coupon->status === 0)
                                            <p class="text-danger"><strong>Cupom Inválido</strong></p>
                                        @else
                                            <p class="text-warning"><strong>Cupom não premiado</strong></p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if(env('PROMO_HAS_PRIZE_VALUE') === true)
                            <div class="row">
                                <div class="col-sm-12 col-xs-12">
                                    <h4>Valor do Prêmio</h4>
                                    @if($coupon->prize_id != null)
                                        <p class="text-info">R$ {{ str_replace('.', ',', $coupon->prize_value) }}</p>
                                    @else 
                                        @if($coupon->status === 0)
                                            <p class="text-danger"><strong>Cupom Inválido</strong></p>
                                        @else
                                            <p class="text-warning"><strong>Cupom não premiado</strong></p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($coupon->video_url !== null)
                            <div class="row">
                                <div class="col-sm-12 col-xs-12">
                                    <h4>Vídeo</h4>
                                    <x-cld-video public-id="{{$coupon->video_public_id}}"></x-cld-video>
                                </div>
                            </div>
                        @endif

                        @if(env('PROMO_HAS_LUCKY_NUMBERS') === true)
                            <div class="row">
                                <div class="col-sm-12 col-xs-12">   
                                    <h4>Números da Sorte</h4>
                                    @if($coupon->status === 0)
                                        <p class="text-danger"><strong>Cupom Inválido</strong></p>
                                    @endif

                                    @foreach($coupon->luckyNumbers as $itemLuckyNumber)
                                        <p class="text-info">{{ $itemLuckyNumber->number }} @if($itemLuckyNumber->final == 1) (Sorteio Final) @endif</p>
                                    @endforeach

                                    @if($coupon->video_url != null)
                                        <p><input type="checkbox" name="has_link_social" value="1" @if($coupon->admin_video_valid_social_network == 1) checked="checked" @endif @if($coupon->status == 1) disabled="disabled" @endif> Validar link nas redes sociais: ({{ $coupon->video_social_network }})</p>
                                        <p><input type="checkbox" name="has_friends" value="1" @if($coupon->admin_video_valid_friends == 1) checked="checked" @endif @if($coupon->status == 1) disabled="disabled" @endif> Validar Amigos</p>
                                    @endif

                                    </div>
                            </div>

                            @if(env('PROMO_HAS_FRIEND') === true) 
                                <div class="row">
                                    <div class="col-sm-12 col-xs-12">   
                                        <h4>Indicação de Amigo</h4>
                                        <small>Este número da sorte é da pessoa que ele indicou.</small>
                                        @if($coupon->status === 0)
                                            <p class="text-danger"><strong>Cupom Inválido</strong></p>
                                        @endif

                                        @if($luckyFriend != null)
                                            <p class="text-info @if($coupon->friend_status == 1) text-green @else text-red @endif">{{ $luckyFriend->number }} ({{$coupon->friend_name}} - {{$coupon->friend_email}})</p>
                                        @else 
                                            <p class="text-gray">Sem indicação</p>
                                        @endif


                                        @if($coupon->video_url != null)
                                            <p><input type="checkbox" name="has_link_social" value="1" @if($coupon->admin_video_valid_social_network == 1) checked="checked" @endif @if($coupon->status == 1) disabled="disabled" @endif> Validar link nas redes sociais: ({{ $coupon->video_social_network }})</p>
                                            <p><input type="checkbox" name="has_friends" value="1" @if($coupon->admin_video_valid_friends == 1) checked="checked" @endif @if($coupon->status == 1) disabled="disabled" @endif> Validar Amigos</p>
                                        @endif

                                        </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12 text-right">
                                <input type="hidden" name="coupon_id" value="{{ $coupon->id }}" />
                                @if(gettype($coupon->status) === "NULL")
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-xs-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Imagem do Cupom</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12 col-xs-12 text-center">
                            <img src="{{ url($coupon->invoice) }}" />
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                
                </div>
            </div>
        </div>
    </div>
@stop

@include('admin.include.assets')