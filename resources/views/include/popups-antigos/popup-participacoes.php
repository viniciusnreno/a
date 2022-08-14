<section id="popup-participacoes" class="zoom-anim-dialog minhas-participacoes mfp-hide popup bg-popup">
    <div class="topo center">
        <img class="logo" src="/assets/images/logo-home.png" />
        <h1>
        PRONTO! É SÓ ENVIAR A FOTO DE SEU CUPOM FISCAL E COMEÇAR A TREINAR!
        </h1>
    </div>
    <nav class="nav-popup">
        <div class="container">
            <div class="nav-wrapper">
                <ul class="center">
                    <li><a class="link-popup" href="#popup-cupom"><i class="material-icons prefix">playlist_add</i> CADASTRAR CUPOM</a></li>
                    <li class="active"><a class="link-popup" onclick="validaParticipacoes()" href="#popup-minhas-participacoes"><i class="material-icons prefix">playlist_add_check</i>MINHAS PARTICIPAÇÕES</a></li>
                    <li><a class="link-popup" href="#popup-atualizar"><i class="material-icons prefix">person</i>MEUS DADOS</a></li>
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
                                {{ dateBR($item->created_at, false) }}
                            </div>
                            <div class="col s2">
                                <i class="tiny material-icons">receipt</i>
                                <span>{{ timeFromDate($item->created_at) }}</span>
                            </div>
                            <div class="col s5">
                                <i class="tiny material-icons">receipt</i>
                                @if($item->status === NULL)
                                    <span>O seu cupom ainda está em validação</span>
                                @elseif($item->status == 0)
                                    <span>O seu cupom é <strong>inválido</strong></span>
                                @else
                                    <span>Você gerou <strong>{{ count($item->codes) }}</strong> número de voucher</span>
                                @endif
                            </div>
                            <div class="col s3">
                                <i class="tiny material-icons">receipt</i>
                                <span>Veja os detalhes</span>
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
                                    <li>
                                        <table>
                                            <tr>
                                                <th style="width: 20%">Quantidade</th>
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
                                </ul>
                            </div>
                            <div class="col s4">
                                    <ul>
                                        <li>Nº do Voucher:</li>
                                        @if($item->status === NULL)
                                            <li>O seu cupom ainda não foi validado.</li>
                                        @elseif($item->status == 0)
                                            <li>O seu cupom é inválido.</li>
                                            <li>{{ $item->reason }}</li>
                                        @else
                                            @foreach($item->codes as $itemCode)
                                                <li>{{ $itemCode->code }}</li>
                                            @endforeach
                                        @endif
                                    </ul>
                            </div>
                        </div>    
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>
