<section id="popup-fale-conosco" class="zoom-anim-dialog mfp-hide popup bg-popup"> 
    <img class="logo" src="/assets/images/geral-logo.png"> 
    <div class="topo center">
        <h1>
            Alguma dúvida sobre<br class="hide-on-med-and-up"> a promoção?<br class="hide-on-small-only"/>
            Deixe sua mensagem e fale conosco!
        </h1>
    </div>
    <div class="p-wrapper">
        <div class="row">
            <form id="formContato" method="post" action="<?php print route('fale-conosco.action'); ?>">
                @csrf
                <div class="input-field col s12">
                    <i class="material-icons prefix">face</i>
                    <input id="fc_nome" name="fc_nome" type="text" class="validate">
                    <label for="fc_nome">Nome Completo</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">contact_mail</i>
                    <input id="fc_documento" name="fc_documento" type="text" class="validate" inputmode="numeric">
                    <label for="fc_documento">CPF</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">phone</i>
                    <input id="fc_telefone" name="fc_telefone" type="tel" class="validate">
                    <label for="fc_telefone">Telefone</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">mail_outline</i>
                    <input id="fc_email" name="fc_email" type="text" class="validate">
                    <label for="fc_email">E-mail</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">error_outline</i>
                    <input id="fc_assunto" name="fc_assunto" type="text" class="validate">
                    <label for="fc_assunto">Assunto</label>
                </div>
                <div class="input-field col s12 file-field">
                    <i class="material-icons prefix">attachment</i>
                    <div class="wrapper-file">
                        <input type="file" id="cupomFile" name="cupomFile">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" placeholder="Problemas para participar? Envie a foto do cupom fiscal" accept="image/png, image/jpeg"  multiple>
                    </div>
                    <p class="obs-valores colado"><strong>Opcional</strong></p>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">message</i>
                    <textarea id="fc_mensagem" name="fc_mensagem" class="materialize-textarea"></textarea>
                    <label for="fc_mensagem">Mensagem</label>
                </div>
                <div class="col s12">
                    <button type="submit" class="waves-effect waves-light btn btn-fale-conosco btn-popup">ENVIAR</button>
                </div>
            </form>
        </div>
    </div>
</section>

@section('fale-conosco-scripts')
<script>
$(document).ready(function(){
    $("#fc_cpf").mask("999.999.999-99",{});

    let SPMaskBehaviorFC = function (val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    },
    spOptionsFC = {
        onKeyPress: function(val, e, field, options) {
            field.mask(SPMaskBehaviorFC.apply({}, arguments), options);
        }
    };
    $('#fc_telefone').mask(SPMaskBehaviorFC, spOptionsFC);

    jQuery.validator.addMethod("cpf", function(value, element) {
	   value = jQuery.trim(value);

	    value = value.replace('.','');
	    value = value.replace('.','');
	    cpf = value.replace('-','');
	    while(cpf.length < 11) cpf = "0"+ cpf;
	    var expReg = /^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/;
	    var a = [];
	    var b = new Number;
	    var c = 11;
	    for (i=0; i<11; i++){
	        a[i] = cpf.charAt(i);
	        if (i < 9) b += (a[i] * --c);
	    }
	    if ((x = b % 11) < 2) { a[9] = 0 } else { a[9] = 11-x }
	    b = 0;
	    c = 11;
	    for (y=0; y<10; y++) b += (a[y] * c--);
	    if ((x = b % 11) < 2) { a[10] = 0; } else { a[10] = 11-x; }

	    var retorno = true;
	    if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]) || cpf.match(expReg)) retorno = false;

	    return this.optional(element) || retorno;

	}, "Informe um CPF válido");

    // Validation		
    $("#formContato").validate(
    {					
        // Rules for form validation
        rules:
        {
            fc_nome:
            {
                required: true
            },
            fc_cpf:
            {
                required: true,
                cpf: true
            },
            fc_telefone:
            {
                required: true
            },
            fc_email:
            {
                required: true,
                email: true
            },
            fc_assunto:
            {
                required: true
            },
            fc_mensagem:
            {
                required: true
            }
        },

        // Messages for form validation
        messages:
        {
            fc_nome:
            {
                required: 'Preencha o seu nome'
            },
            fc_cpf:
            {
                required: 'Preencha o seu CPF'
            },
            fc_telefone:
            {
                required: 'Preencha o seu telefone'
            },
            fc_email:
            {
                required: 'Preencha o seu e-mail',
                email: 'E-Mail inválido'
            },
            fc_assunto:
            {
                required: 'Preencha o assunto'
            },
            fc_mensagem:
            {
                required: 'Preencha a sua mensagem'
            }
        },					

        // Do not change code below
        errorPlacement: function(error, element)
        {
            console.log('opa');
            error.appendTo(element.parent("div"));
            return false;
        },
        wrapper: "small",
        submitHandler: function(form)
        {
            $(form).ajaxSubmit(
            {
                beforeSend: function()
                {
                    $('body').loading({
                        message: '{!! config('custom.loader.message') !!}',
                        zIndex: '{{ config('custom.loader.zIndex') }}'
                    });
                },
                success: function(data)
                {   
                    if( data.status === true )
                    {
                        $.jAlert({
                            title: 'Informação',
                            content: 'Mensagem enviada com sucesso',
                            theme: 'yellow',
                            closeOnEsc: false,
                            closeBtn: false,
                            btns: [{'text': 'Fechar'}],
                            onClose: function(alert){
                                if(data.redirect){
                                    window.location = data.redirect;
                                }
                            }
                        })
                    }
                    else
                    {
                        errorAlert( data.error );
                    }
                    $('body').loading('stop');
                },
                dataType:'json'
            });
        }
    });
});
</script>
@endsection('fale-conosco-scripts')