<section id="popup-minhas-participacoes" class="zoom-anim-dialog minhas-participacoes mfp-hide popup bg-popup">
    <img class="logo" src="/assets/images/geral-logo.png">
    <div class="topo center">
        <h1>
            Aqui você confere o histórico<br> de suas participações.
        </h1>
    </div>
    <nav class="nav-popup">
        <div class="container">
            <div class="nav-wrapper">
                <ul class="center">
                    <li><a class="link-popup" href="#popup-cupom"><i class="material-icons prefix">receipt</i>CADASTRAR <br class="hide-on-med-and-up">CUPOM</a></li>
                    <li><a class="link-popup active" onclick="validaParticipacoes()" href="#popup-minhas-participacoes"><i class="material-icons prefix">border_color</i>MINHA <br class="hide-on-med-and-up">PARTICIPAÇÃO</a></li>
                    <li><a class="link-popup" href="#popup-atualizar"><i class="material-icons prefix">account_circle</i>MEUS DADOS</a></li>
                    <li><a class="link-popup" href=""><i class="material-icons prefix">assignment</i></a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="p-wrapper">
        @if(count($coupons) == 0)
            <p>Você ainda não possui participações.</p>
        @endif
        <ul class="collapsible">
            @foreach($coupons as $item)
                <li>
                    <div class="collapsible-header">
                        <div class="row">
                            <div class="col s2">
                                <i class="tiny material-icons">receipt</i>
                                <span class="date-time">{{ dateBR($item->created_at, false) }}</span>
                            </div>
                            <div class="col s2">
                                <i class="tiny material-icons">receipt</i>
                                <span class="date-time">{{ timeFromDate($item->created_at) }}</span>
                            </div>
                            <div class="col s5">
                                <i class="tiny material-icons">receipt</i>
                                <span>Veja os detalhes da sua participação</span>
                                {{-- @if($item->status === NULL)
                                    <span>O seu cupom ainda não foi validado</span>
                                @else
                                    <span>Veja os <strong>detalhes</strong> da sua participação</span>
                                @endif --}}
                            </div>
                            <div class="col s3">
                                <i class="tiny material-icons">receipt</i>
                                <span>Clique aqui</span>
                                <i class="material-icons seta secondary-content">keyboard_arrow_down</i>
                            </div>
                        </div>
                    </div>
                    <div class="collapsible-body">
                        <div class="row">
                            <div class="col s8">
                                <ul>
                                    <li>Comprovante Fiscal:</li>
                                    <li>Número do COO: {{ $item->coupon_number }}</li>
                                    <li>CNPJ da loja: {{ $item->company_cnpj }}</li>
                                    <li>Data da Compra: {{ $item->buy_date }}</li>
                                    <li>Valor total em produtos participantes: R$ {{ $item->amount }}</li>

                                    @if(env('PROMO_HAS_INSTANT_PRIZE'))
                                        @if($item->prize_id !== NULL)
                                            <li>Prêmio Instantâneo: <span style="background: yellow; padding: 4px; font-weight: bold;">Vale compra {{ $item->prize->prize }}</span></li>
                                            <li><br /><strong>Chave para o prêmio instantâneo: </strong><br />{{ $item->instant_prize_hash }}</li>
                                        @endif
                                    @endif

                                    @if($item->status === 0)
                                        <li>
                                            <p><span style="color: red;">Cupom Inválido:</span> {{ $item->reason }}</p>
                                        </li>
                                    @endif

                                    <!--li>
                                        <br />
                                        @if($item->friend_email === null)
                                            Você ainda não indicou um amigo. <a class="link-popup" href="#popup-enviar" onClick="$('form#formVideo input#couponID').val({{ $item->id }});">Clique aqui</a> para aumentar a sua chance.
                                        @else
                                            @if($item->friend_payback == 0)
                                                <p class="text-success">Você já indicou o seu amigo e o número da sorte dele é {{ $item->friendLuckyNumber->number }}.</p>
                                            @else
                                                <p class="text-success">Você já indicou o seu amigo e o número da sorte dele é {{ $item->friendLuckyNumber->number }}. Além disso, você também ganhou mais um número da sorte.</p>
                                            @endif
                                        @endif
                                    </li-->

                                    @if(env('PROMO_HAS_PRODUCTS') === true)
                                        <li>
                                            <table>
                                                <tr>
                                                    <th style="width: 20%">Qtd</th>
                                                    <th>Produto</th>
                                                </tr>
                                                @foreach($item->products as $itemProduct)
                                                    <tr>
                                                        <td>{{ $itemProduct->pivot->qty }}</td>
                                                        <td>{{ $itemProduct->product }}</td>
                                                    </tr>
                                                    <p> </p>
                                                @endforeach
                                            </table>
                                        </li>
                                    @endif
                                </ul>
                            </div>

                            @if(env('PROMO_HAS_CODE') === true)
                                <div class="col s4">
                                        <ul>
                                            <li>Nº do Voucher {{ $item->prize->prize}}:</li>
                                            <li>Veja os detalhes da sua participação</li>
                                            
                                            {{-- @if($item->status === NULL)
                                                <li>O seu cupom ainda não foi validado.</li>
                                            @elseif($item->status == 0)
                                                <li>O seu cupom é inválido.</li>
                                                <li>{{ $item->reason }}</li>
                                            @else
                                                @foreach($item->codes as $itemCode)
                                                    <li>{{ $itemCode->code }}</li>
                                                @endforeach
                                            @endif --}}
                                        </ul>
                                </div>
                            @endif

                            @if(env('PROMO_HAS_LUCKY_NUMBERS') === true)
                                <div class="col s4">
                                        <ul>
                                            <li>Nº da Sorte:</li>
                                                @foreach($item->luckyNumbers as $itemCode)
                                                    <li>{{ $itemCode->number }} @if($itemCode->final == 1) (Sorteio Final) @endif</li>
                                                @endforeach
                                        </ul>
                                </div>
                            @endif
                        </div>    
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
    <a href="#popup-cupom" id="menuCadastrarCupom" class="btn-minhas link-popup btn btn-link-popup">PARTICIPE AGORA</a>
</section>
