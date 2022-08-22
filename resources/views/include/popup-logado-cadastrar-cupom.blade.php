<section id="popup-cupom" class="zoom-anim-dialog cupom mfp-hide popup bg-popup">
    <img class="logo" src="/assets/images/geral-logo.png">
    <div class="topo center">
        <h1>
            Pronto! Agora é só enviar a<br>
            foto de seu cupom fiscal.
        </h1>
    </div>
    <nav class="nav-popup">
        <div class="container">
            <div class="nav-wrapper">
                <ul class="center">
                    <li><a class="link-popup active" href="#popup-cupom"><i class="material-icons prefix">receipt</i>CADASTRAR <br class="hide-on-med-and-up">CUPOM</a></li>
                    <li><a class="link-popup" onclick="validaParticipacoes()" href="#popup-minhas-participacoes"><i class="material-icons prefix">border_color</i>MINHA <br class="hide-on-med-and-up">PARTICIPAÇÃO</a></li>
                    <li><a class="link-popup" href="#popup-atualizar"><i class="material-icons prefix">account_circle</i>MEUS DADOS</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="p-wrapper logado-border">
        <div class="row">
            <form id="formCupom" method="post" action="{{ route('cadastro.cupom.action') }}" enctype="multipart/form-data">
                @csrf
                <div class="input-field col s12 file-field">
                    <i class="material-icons prefix">add_a_photo</i>
                    <div class="wrapper-file">
                        <input type="file" id="cupomFile" name="cupomFile">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" placeholder="Envie a foto de seu cupom fiscal" accept="image/png, image/jpeg"  multiple>
                        <p class="obs-valores colado"><strong>Atenção</strong>: Tamanho máximo do JPG/JPEG 5MB</p>
                    </div>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">receipt</i>
                    <input id="nota_fiscal" name="nota_fiscal" class="validate" type = "text" maxlength = "20">
                    <label for="nota_fiscal">Número do Cupom Fiscal</label>
                    <i class="material-icons prefix tooltipped" data-position="top" data-tooltip="<img src='/assets/images/cupom-fiscal-numero.png' style='max-width:100%' />">help_outline</i>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">filter_1</i>
                    <input id="cnpj" name="documento_estabelecimento" type="text" class="validate">
                    <label for="cnpj">CNPJ da Loja</label>
                    <i class="material-icons prefix tooltipped" data-position="top" data-tooltip="<img src='/assets/images/cupom-fiscal-CJPJ.png' style='max-width:100%' />">help_outline</i>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">event</i>
                    <input id="data_emissao" name="data_emissao" type="text" class="validate">
                    <label for="data_emissao">Data da Compra</label>
                    <i class="material-icons prefix tooltipped" data-position="top" data-tooltip="<img src='/assets/images/cupom-fiscal-data-da-compra.jpg' style='max-width:100%' />">help_outline</i>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">attach_money</i>
                    <input data-audience="[&quot;área-logada&quot;, &quot;cadastrar-cupom&quot;, &quot;qtd-brioches&quot;]" id="valor" name="valor" class="validate" type="text">
                    <label for="valor" class="valor-total">Informe o valor de sua compra em produtos Broto Legal participantes </label>
                </div>
                <div class="col s12 box-retirada">
                    <span>Já retirou o brinde na loja?</span>
                    <label>
                        <input type="radio" name="confirma_retirada" id="confirma_retirada" validate="checkbox" checked="checked"  value="1" />
                        <span>Sim</span>
                    </label>
                    <label>
                        <input type="radio" name="confirma_retirada" id="confirma_retirada" validate="checkbox" value="0" />
                        <span>Não</span>
                    </label>
                </div> <br>
                <!-- <div class="input-field col s12 adicionar-numero">
                    <p>Informe abaixo a quantidade dos Bolinhos e Biscoito Toshiba adquiridos</p>
                    <ul class="produtos">
                        @foreach($products as $item)
                            <li>(<input name="productQty[{{ $item->id }}]" oninput="maxLengthCheck(this)" type="number" maxlength="2" class="validate setinhas">) <span>{{ $item->product }}</span><input type="hidden" name="productName[{{ $item->id }}]" value="{{ $item->id }}" /></li>
                        @endforeach
                        
                    </ul>
                </div> -->
<!-- 
                <div class="alteracao">
                    <span>ENVIE SEU CUPOM FISCAL E SAIBA NA HORA<br>
                    SE GANHOU UMA CAMISA OU BOLA <br>
                    AUTOGRAFADA PELO CAFU!</span>
                </div> -->
                <div class="btn-enviar-cupom col s12 div-cupom">
                    <button class="waves-effect waves-light btn btn-cupom">ENVIAR</button> 
                </div>
                <div class="col s12 box-important">
                    <div class="alert">
                            <i class="material-icons">info_outline</i>
                            <span>IMPORTANTE</span>
                    </div>
                    <ul>
                        <li><i class="material-icons left">keyboard_arrow_right</i>A foto do seu cupom deve conter o <strong>CNPJ do estabelecimento, número do cupom e data da compra.</strong></li>
                        <li><i class="material-icons left">keyboard_arrow_right</i>Limite de <strong>3 brindes</strong> por CPF.</li>
                        <li><i class="material-icons left">keyboard_arrow_right</i>Limite de <strong>100 números da sorte</strong> por CPF.</li>
                        <li><i class="material-icons left">keyboard_arrow_right</i>Limite de <strong>20 cupons fiscais</strong> cadastrados por CPF.</li>
                        <li><i class="material-icons left">keyboard_arrow_right</i>Seu cupom só pode ser cadastrado 1 vez</li>
                    </ul>
                    <p class="alerta-final">GUARDE SEU CUPOM FISCAL, ELE PODERÁ SER SOLICITADO.</p>
                </div>
            </form>
        </div>
    </div> 
</section>
<img src='/assets/images/cupom-fiscal-numero.png' style='display:none' />
<img src='/assets/images/cupom-fiscal-CJPJ.png' style='display:none' />
<img src='/assets/images/cupom-fiscal-data-da-compra.jpg' style='display:none' />
<img src='/assets/images/cupom-fiscal-valor.png' style='display:none' />


@section('cadastrar-cupom-scripts')
<script>
function Desmarcar(confirma, recusa){
    if (document.getElementById(confirma).checked){
        document.getElementById(recusa).checked=false;
    }
}

function Checkfiles(){
    var fup = document.getElementById('filename');
    var fileName = fup.value;
    var ext = fileName.substring(fileName.lastIndexOf('.') + 1);

    if(ext =="jpeg" || ext=="png"){
        return true;
    }
    else{
        return false;
    }
}

function maxLengthCheck(object)
  {
    if (object.value.length > object.maxLength)
      object.value = object.value.slice(0, object.maxLength)
  }

    
$(document).ready(function(){

    $("#cnpj").mask("99.999.999/9999-99");
    $("#data_emissao").mask("99/99/9999");

    $("#nota_fiscal").on("keyup", function(){
        let val = $(this).val().replace(/^0+/, '');
        $(this).val(val);
    });

    $.validator.addMethod(
        "regex",
        function(value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        },
        "Corrija este campo"
     );

    $.validator.addMethod('positiveNumber',
    function (value) {
        if( value == "" )
        {
            return true;
        }
        else
        {
            if( Number(value) > 0 ||  Number(value) == 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }, 'Quantidade inválida.');

    $.validator.addMethod('dateRange', function(value, element) {
        if (this.optional(element)) {
            return true;
        }

        var expData = value.split( '/' );
        var setData = [ expData[2], expData[1], expData[0]].join( '-' );

       var startDate = Date.parse('2022-01-12'),
            endDate = Date.parse('2022-03-30'),
            enteredDate = Date.parse(setData);

        if (isNaN(enteredDate)) {
            return false;
        }

        return ((startDate <= enteredDate) && (enteredDate <= endDate));
    }, 'Data de emissão deve estar dentro do período de validade da Promoção');

    $.validator.addMethod('futureDate', function(value, element) {
        if (this.optional(element)) {
            return true;
        }

        var expData = value.split( '/' );
        var setData = [ expData[2], expData[1], expData[0]].join( '-' );

       var startDate = Date.parse('2022-01-12'),
            endDate = Date.parse(new Date()),
            enteredDate = Date.parse(setData);

        if (isNaN(enteredDate)) {
            return false;
        }

        return ((startDate <= enteredDate) && (enteredDate <= endDate));
    }, 'Data de emissão futura não é permitida');

    jQuery.validator.addMethod("cnpj", function(cnpj, element) {

        var numeros, digitos, soma, resultado, pos, tamanho,
            digitos_iguais = true;

        cnpj = cnpj.replace( /[^0-9]/g, "" );

        if (cnpj.length < 14 && cnpj.length > 15)
            return false;

        for (var i = 0; i < cnpj.length - 1; i++)
            if (cnpj.charAt(i) != cnpj.charAt(i + 1)) {
                digitos_iguais = false;
                break;
            }

        if (!digitos_iguais) {
            tamanho = cnpj.length - 2
            numeros = cnpj.substring(0,tamanho);
            digitos = cnpj.substring(tamanho);
            soma = 0;
            pos = tamanho - 7;

            for (i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2)
                    pos = 9;
            }

            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

            if (resultado != digitos.charAt(0))
                return false;

            tamanho = tamanho + 1;
            numeros = cnpj.substring(0,tamanho);
            soma = 0;
            pos = tamanho - 7;

            for (i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2)
                    pos = 9;
            }

            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

            if (resultado != digitos.charAt(1))
                return false;

            return true;
        }

        return false;
    }, "CNPJ inválido.");

    jQuery.validator.addMethod("notEqual", function(value, element, param) {
      return this.optional(element) || value != param;
    }, "Please specify a different (non-default) value");

    jQuery.validator.addMethod('minBR', function (value, el, param) {
        return parseFloat(value.replace('.','').replace(',','.')).toFixed(2) >= param;
    });

    $(function()
    {
        // Validation
        $("#formCupom").validate(
        {
            // Rules for form validation
            rules:
            {
                documento_estabelecimento:
                {
                    required: true,
                    cnpj: true
                },
                data_emissao:
                {
                    required: true,
                    dateRange: true,
                    futureDate: true
                },
                nota_fiscal:
                {
                    required: true
                },
                valor:
                {
                    required: true,
                    notEqual: '0,00'
                    // minBR: 20
                    //maxlength: 3,
                    //number: true,
                    //positiveNumber: true
                },
                estado:
                {
                    required: true
                },
                "productName[]": 
                {
                    required: true
                },
                "productQty[]":
                {
                    required: true,
                    min: 1
                },
                qtd: {
                    required: true,
                    number: true,
                    min: 1
                },
                cupomLogin:
                {
                    required: true
                }
            },

            // Messages for form validation
            messages:
            {
                documento_estabelecimento:
                {
                    required: 'Preencha o CNPJ',
                    cnpj: 'CNPJ inválido'
                },
                data_emissao:
                {
                    required: 'Preencha a data de emissão'
                },
                nota_fiscal:
                {
                    required: 'Preencha o número do cupom'
                },
                valor:
                {
                    required: "Preencha o valor total em produtos",
                    notEqual: 'Valor inválido',
                    // minBR: "Valor mínimo para cadastro do cupom é de R$ 20,00"
                    //maxlength: "Valor mínimo: R$ 20,00",
                    //number: "Deve ser um número"
                },
                estado:
                {
                    required: 'Preencha o Estado'
                },
                "productName[]": 
                {
                    required: 'Escolha um produto'
                },
                "productQty[]":
                {
                    required: 'Campo Obrigatório',
                    min: 'Qtd. Mín. = 1'
                },
                qtd: {
                    required: 'Escolha uma imagem',
                    number: 'Quantidade inválida',
                    min: 'Quantidade mínima de produtos: 1'
                },
                cupomLogin: {
                    required: 'Escolha uma imagem'
                }
            },

            // Do not change code below
            errorPlacement: function(error, element)
            {
                error.appendTo(element.parent("div"));
                return false;
            },
            wrapper: "small",
            submitHandler: function(form)
            {
                $(form).ajaxSubmit(
                {

                    beforeSubmit: function(arr, $form, options)
                    {
                        $('body').loading({
							message: '{!! config('custom.loader.message') !!}',
							zIndex: '{{ config('custom.loader.zIndex') }}'
						});

                        $.magnificPopup.close();
                    },
                    success: function(data)
                    {

                        if( data.status === true )
                        {
                            
                            showAlerts('aposEnviarCupom');
                            /*
                            setTimeout(function(){
                                window.location = data.redirect;
                                }, 1500);
                                */

                   
                            if(data.data.cna_mini_curso === 1){

                                if(data.instantPrize.hasPrize === true){
                                    $("#popup-foi-dinheiro span#instant_prize_value").html(`R$ ${data.instantPrize.prize.text_value},00`);
                                    $("#popup-foi-dinheiro small#instant_prize_key").html(`${data.instantPrize.hash}`);
                                    $("#link_picpay").attr('href', '#popup-foi-dinheiro');
                                }

                                $.magnificPopup.open({
                                    items: {
                                        src: '#popup-foi-cna'
                                    },
                                    type: 'inline',
                                    callbacks: {
                                        open: function() { 
                                            $('.mfp-close').on('click',function(event){
                                                event.preventDefault();
                                                setTimeout(function(){
                                                    window.location = data.redirect;
                                                    }, 300);

                                                $.magnificPopup.close();
                                            }); 
                                        }
                                    }
                                });
                            } else {

                                if(data.instantPrize.hasPrize === true){

                                    $("#popup-foi-dinheiro span#instant_prize_value").html(`R$ ${data.instantPrize.prize.text_value},00`);
                                    $("#popup-foi-dinheiro small#instant_prize_key").html(`${data.instantPrize.hash}`);


                                    $.magnificPopup.open({
                                        items: {
                                            src: '#popup-foi-dinheiro'
                                        },
                                        type: 'inline',
                                        callbacks: {
                                            open: function() { 
                                                $('.mfp-close').on('click',function(event){
                                                    event.preventDefault();
                                                    setTimeout(function(){
                                                        window.location = data.redirect;
                                                        }, 300);

                                                    $.magnificPopup.close();
                                                }); 
                                            }
                                        }
                                    });
                                } else {
                                    $.magnificPopup.open({
                                        items: {
                                            src: '#popup-nao-foi'
                                        },
                                        type: 'inline',
                                        callbacks: {
                                            open: function() { 
                                                $('.mfp-close').on('click',function(event){
                                                    event.preventDefault();
                                                    setTimeout(function(){
                                                        window.location = data.redirect;
                                                        }, 300);

                                                    $.magnificPopup.close();
                                                }); 
                                            }
                                        }
                                    });
                                }                                
                            }
                        }
                        else
                        {
                            $.loader('close');
                            // errorAlert('Erro', data.error);
                            $.jAlert({
                                title: 'Erro',
                                content: data.error,
                                closeOnEsc: false,
                                closeBtn: false,
                                btns: [{'text': (!data.btn ? 'OK' : data.btn)}],
                                onClose: function(alert){
                                    if(data.error_type == 'E_NOT_ACCEPTED_NEW_RULES'){
                                        $.magnificPopup.open({
                                            items: {
                                                src: '#popup-regulamento-novo'
                                            },
                                            type: 'inline',
                                            callbacks: {
                                                open: function() { 
                                                    $('.mfp-close').on('click',function(event){
                                                        event.preventDefault();

                                                        /*
                                                        setTimeout(function(){
                                                            window.location = "{{ url('/') }}";
                                                            }, 300);
                                                        */

                                                        $.magnificPopup.close();
                                                    }); 
                                                }
                                            }
                                        });
                                    } else {
                                        $("#menuCadastrarCupom").click();
                                    }
                                }
                            });
                        }
                    },
                    error: function(response, status, err){
						let errors = [];
                        /*
						if(response.responseJSON.errors){
							let json = response.responseJSON.errors;
							Object.keys(json).forEach( function(key){
								if(key != 'message'){
									if($.isArray(json[key])){
										errors.push(...json[key]);
									} else {
										errors.push(json[key]);
									}
								}
							});
						} else {
							errors.push('Houve um erro desconhecido. Tente novamente');
                        }
                        */
                        if(response.responseJSON){
                            if(response.responseJSON.errors){
                                let json = response.responseJSON.errors;
                                Object.keys(json).forEach( function(key){
                                    if(key != 'message'){
                                        if($.isArray(json[key])){
                                            errors.push(...json[key]);
                                        } else {
                                            errors.push(json[key]);
                                        }
                                    }
                                });
                            } else if (response.responseJSON.message){
                                errors.push(response.responseJSON.message);
                            } else {
                                errors.push('Houve um erro desconhecido. Tente novamente');
                            }
                        } else {
                            errors.push('Houve um erro desconhecido. Tente novamente');
                        }
						errorAlert('Erro', errors.join("<br />"));
					},
                    complete: function(){
                        $('body').loading('stop');
                    },
                    dataType:'json'
                });
            }
        });
    });

    $("input[name=valor]").priceFormat({
      prefix: 'R$',
      centsSeparator: ',',
      thousandsSeparator: '.'
    });
});

if(window.location.href.indexOf("#historico") !== -1){
    console.log("opa", window.location.href.indexOf("#historico"));
    $("#btn-historico").click();
}
function gaProdutos(val) {
  if(!val)return;
  gaSendEvent('[promocao-asus-preenchimento-cupom-'+val.value+']');
}

function gaEstado(val) {
  if(!val)return;
  gaSendEvent('[promocao-asus-preenchimento-cupom-'+val.value+']');
}
</script>
<script>
	setTimeout(function(){ 
		// ga.getAll()[0].send('pageview','/cadastro-cupom');
		// activeMenu('#menu-cupons');
	}, 900);
</script>
@endsection('cadastrar-cupom-scripts')
