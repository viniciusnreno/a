<section id="popup-recuperar-senha" class="zoom-anim-dialog mfp-hide popup bg-popup">
    <img class="logo" src="/assets/images/logo-home.png">
    <div class="topo center">
    </div>
    <div class="p-wrapper fluid formLogin">
        <div class="row">
            <div class="col s12">
                <div class="row">
                    <div class="col s12">
                        <h3 class="titulo">Para recuperar sua senha, por favor, informe <br />o email de cadastro que iremos um enviar um link de recuperação :)</h3>
                        <form id="formReset" method="post" action="{{ route('password.email') }}">
                            @csrf
                            <div class="input-field col s12">
                                <i class="material-icons prefix">email</i>
                                <input id="esqueci_email" name="email" type="email" class="validate" placeholder="E-Mail">
                            </div>
                            <div class="input-field col s12">
                                <button type="submit" class="waves-effect waves-light btn">ENVIAR</button>
                            </div>
                            <div id="feedback" style="display: none;">
                                <h4>Prontinho!</h4>
                                <p>Um e-mail foi enviado para o seu endereço eletrônico com as instrução para recuperação de senha.</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@section("recuperar-senha-scripts")
<script>

	// $("#documento").mask("999.999.999-99",{});
	    
    let SPMaskBehavior = function (val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    },
    spOptions = {
        onKeyPress: function(val, e, field, options) {
            field.mask(SPMaskBehavior.apply({}, arguments), options);
        }
    };


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

	jQuery.validator.addMethod("notEqual", function(value, element, param) {
	  return this.optional(element) || value != param;
    }, "Escolha um opção válida");
    
	$(function()
    {   
       
        // Validation
        $("#formEsqueciSenha").validate(
        {
            // Rules for form validation
            rules:
            {
                email:
                {
                	required: true,
                	email: true
                }
            },

            // Messages for form validationconfirmed
            messages:
            {
                email:
                {
                	required: 'Prencha o seu E-Mail',
                	email: 'E-Mail inválido'
                }
            },
            errorPlacement: function(error, element)
            {                
                if (element.attr("name") == "termos"){
                    error.appendTo(element.parent("div"));
                } else {
                    error.appendTo(element.parent("div"));
                }
                return false;
            },
            wrapper: "small",
            submitHandler: function(form)
            {
                $(form).ajaxSubmit(
                {
                    headers: { 
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') 
					},
                    beforeSend: function()
                    {
                        $('body').loading({
							message: '{!! config('custom.loader.message') !!}',
							zIndex: '{{ config('custom.loader.zIndex') }}'
						});
                    },
                    success: function(data)
                    {
                        console.log(data);
                        if( data.status === true )
                        {
                            /*
                            setTimeout(function(){
                                // window.location = data.redirect;
                            }, 900);
                            */
                            $("#feedback").show();
                        }
                        else
                        {
                            $.loader('close');
                            errorAlert('Erro', data.error);
                            // alert( data.msg );
                        }
                    },
					error: function(response, status, err){
						let errors = [];
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
</script>
@endsection("recuperar-senha-scripts")

