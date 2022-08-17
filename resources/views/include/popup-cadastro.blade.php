<section id="popup-cadastro" class="zoom-anim-dialog mfp-hide popup bg-popup cadastro">
    <img class="logo" src="/assets/images/geral-logo.png">
    <div class="h1-cadastro topo center">
        <h1>Vamos ao seu cadastro?<br>
        É rapidinho!</h1>
        </div>
    <div class="p-wrapper">
        <div class="row">
            <form id="formCadastro" method="post" action="{{ route('register') }}">
                @csrf
                <div class="input-field col s12">
                    <i class="material-icons prefix">phone</i>
                    <label for="celular">Celular*</label>
                    <input id="celular" type="text" name="celular" class="validate">
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">mail_outline</i>
                    <label for="email">E-mail*</label>
                    <input id="email" type="email" name="email" class="validate" required data-error="Informe o e-mail">
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">mail_outline</i>
                    <input id="email_confirmation" type="email" name="email_confirmation" class="validate" required data-error="Informe o e-mail">
                    <label for="email_confirmation">Confirme seu E-mail*</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">lock_outline</i>
                    <input id="senha" name="senha" type="password" class="validate">
                    <label for="senha">Crie uma Senha*</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">lock_outline</i>
                    <input id="senha_confirmation" name="senha_confirmation" type="password" class="validate">
                    <label for="senha_confirmation">Confirme sua Senha*</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">face</i>
                    <input id="nome" name="nome" type="text" class="validate">
                    <label for="nome">Nome Completo*</label>
                    <span></span>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">cake</i>
                    <input id="dt_nascimento" name="dt_nascimento" type="text" class="validate">
                    <label for="dt_nascimento">Nascimento*</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">contact_mail</i>
                    <input id="documento" name="documento" type="text" class="validate" inputmode="numeric" maxlength="14">
                    <label for="documento">CPF</label>
                </div>
                <div class="input-field col s12 selectEstado">
                    <i class="material-icons prefix">map</i>
                    <select id="estado" name="estado" class="validate">
                        <option value="#" selected>Selecione</option>
                        <option value="SP"><span>SP</span></li>
                        <option value="PR"><span>PR</span></li>
                    </select>
                    <label>Estado*</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">home</i>
                    <input id="endereco" name="endereco" type="text" class="validate" data-error="Informe o endereço">
                    <label for="endereco">Endereço*</label>
                    <p class="obs-valores colado"><strong>Atenção</strong>: Esse será o endereço usado para envio do brinde. </p>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">add</i>
                    <input id="numero" name="numero" type="text" class="validate" data-error="Informe o número">
                    <label for="numero">Número*</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">add</i>
                    <input id="bairro" name="bairro" type="text">
                    <label for="bairro">Bairro</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">location_city</i>
                    <input id="cidade" name="cidade" type="text" class="validate">
                    <label for="cidade">Cidade</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">location_on</i>
                    <input id="cep" name="cep" type="text" class="validate">
                    <label for="cep">CEP</label>
                </div>

                <div class="check-regulamento col s12 pad-left">
                    <label>
                        <input name="termos" id="termos" type="checkbox" validate="checkbox" class="validate" value="On" />
                        <span>Li e aceito os termos deste <a class="link-popup reg" href="#popup-regulamento">regulamento.</a></span>
                    </label> 
                </div>
                <div class="col s12 pad-left">
                    <label>
                        <input type="checkbox" validate="checkbox" class="validate" name="receive_information_email" id="receive_information_email" value="On" />
                        <span>Aceito receber informações exclusivas da <span class="cadastro-txt-check">Broto Legal</span> por e-mail e telefone.</span>
                    </label>
                </div>
                <div class="col s12 pad-left">
                    <label>
                        <input type="checkbox" name="keep_data" id="keep_data" value="On" validate="checkbox"  />
                        <span>Aceito receber informações exclusivas da <span class="cadastro-txt-check">Broto Legal</span> pelo WhatsApp.</span>
                    </label>
                </div>
                <div class="col s12 pad-left btns-cadastro">
                    <i class="waves-effect waves-light waves-input-wrapper" style=""><input class="btn btn-popup waves-button-input" type="submit" value="ENVIAR"></i>
                    <button class="waves-effect waves-teal btn-flat link-popup-telefone voltar-cadastro" href="#popup-telefone">VOLTAR</button>
                </div>
            </form>
        </div>
    </div> 
</section>
@section("cadastro-usuario-script")
<script>
$( document ).ready(function() {
    $("#documento").mask("999.999.999-99",{});
    $("#cep").mask("99999-999",{});
    $("#dt_nascimento").mask("99/99/9999",{});
    
    let SPMaskBehavior = function (val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    },
    spOptions = {
        onKeyPress: function(val, e, field, options) {
            field.mask(SPMaskBehavior.apply({}, arguments), options);
        }
    };

    $('#celular').mask(SPMaskBehavior, spOptions);

    function validarCPF(cpf) {	
        cpf = cpf.replace(/[^\d]+/g,'');	
        if(cpf == '') return false;	
        
        // Valida 1o digito	
        add = 0;	
        for (i=0; i < 9; i ++)		
            add += parseInt(cpf.charAt(i)) * (10 - i);	
            rev = 11 - (add % 11);	
            if (rev == 10 || rev == 11)		
                rev = 0;	
            if (rev != parseInt(cpf.charAt(9)))		
                return false;		
        // Valida 2o digito	
        add = 0;	
        for (i = 0; i < 10; i ++)		
            add += parseInt(cpf.charAt(i)) * (11 - i);	
        rev = 11 - (add % 11);	
        if (rev == 10 || rev == 11)	
            rev = 0;	
        if (rev != parseInt(cpf.charAt(10)))
            return false;		
        return true;   
    }

    function validarCNPJ(cnpj) {
 
        cnpj = cnpj.replace(/[^\d]+/g,'');

        if(cnpj == '') return false;
        
        if (cnpj.length != 14)
            return false;
            
        // Valida DVs
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

    /*
    $( "#documento" ).keyup(function() {
        let doc = $(this).val();
        
        if(validarCPF(doc) || doc.replace(/\D/g, "").length < 11){
            $("#divCompanyName").addClass("hide");
            $("#divCompanyCPF").addClass("hide");
            $("#userType").val('F');
            $(this).mask("999.999.999-99",{});
        } else {
            $("#divCompanyName").removeClass("hide");
            $("#divCompanyCPF").removeClass("hide");
            $("#userType").val('J');
            $(this).mask("00.000.000/0000-00",{});
        }
    });
    */

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

    $.validator.addMethod( "cnpj", function( value, element ) {
        "use strict";

        if ( this.optional( element ) ) {
            return true;
        }

        // Removing no number
        value = value.replace( /[^\d]+/g, "" );

        // Checking value to have 14 digits only
        if ( value.length !== 14 ) {
            return false;
        }

        // Valida DVs
        var tamanho = ( value.length - 2 );
        var numeros = value.substring( 0, tamanho );
        var digitos = value.substring( tamanho );
        var soma = 0;
        var pos = tamanho - 7;

        for ( var i = tamanho; i >= 1; i-- ) {
            soma += numeros.charAt( tamanho - i ) * pos--;
            if ( pos < 2 ) {
                pos = 9;
            }
        }

        var resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

        if ( resultado !== parseInt( digitos.charAt( 0 ), 10 ) ) {
            return false;
        }

        tamanho = tamanho + 1;
        numeros = value.substring( 0, tamanho );
        soma = 0;
        pos = tamanho - 7;

        for ( var il = tamanho; il >= 1; il-- ) {
            soma += numeros.charAt( tamanho - il ) * pos--;
            if ( pos < 2 ) {
                pos = 9;
            }
        }

        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

        if ( resultado !== parseInt( digitos.charAt( 1 ), 10 ) ) {
            return false;
        }

        return true;

    }, "CNPJ Inválido" );

	jQuery.validator.addMethod("notEqual", function(value, element, param) {
	  return this.optional(element) || value != param;
    }, "Escolha um opção válida");
    
    $.validator.addMethod("dateBirth", function(value, element) {

        var t = value.split('/');

        var today = new Date();
        var birthDate = new Date(t[2] + '-' + t[1] + '-' + t[0]);
        var age = today.getFullYear() - birthDate.getFullYear();
     
        if (age > 18) {
            return true;
        }
     
        var m = today.getMonth() - birthDate.getMonth();
     
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {    
            age--;
        }
        var birthDate = new Date(t[2] + '-' + t[1] + '-' + t[0]);
        var age = today.getFullYear() - birthDate.getFullY
        return age >= 18;
    }, "Você precisa ser maior de 18 anos para participar!");

    $(function()
    {   
       
        // Validation
        $("#formCadastro").validate(
        {
            // Rules for form validation
            rules:
            {
                celular:
                {
                	required: true
                },
                email:
                {
                	required: true,
                	email: true
                },
                'email_confirmation':
                {
                	required: true,
                    email: true,
                    equalTo: '#email'
                },
                senha:
                {
                    required: true,
                    minlength: 4
                },
                senha_confirmation:
                {
                    required: true,
                    equalTo: '#senha'
                },
                nome:
                {
                	required: true
                },
                dt_nascimento: {
                    required: true,
                    dateBirth: true
                },
                documento:
                {
                	required: true,
                	cpf: {
                        depends: function(element){
                            return $(element).val().replace(/\D/g, '').length <= 11
                        }
                    },
                    cnpj: {
                        depends: function(element){
                            return $(element).val().replace(/\D/g, '').length > 11
                        }
                    }
                },
                cep: 
                {
                    required: true
                },              
                endereco: 
                {
                    required: true
                },
                numero: 
                {
                    required: true
                },
                bairro: 
                {
                    required: false
                },
                bairro: 
                {
                    required: true
                },
                estado: 
                {
                    required: true,
                    notEqual: '#'
                },
                cidade: 
                {
                    required: true
                },                
                termos:
                {
                    required: true
                },
                receive_information_email:
                {
                    required: true
                },
                ciente:
                {
                    required: true,
                    maxlength: 1
                },
                keep_data:
                {
                    required: true,
                    maxlength: 1
                }
            },

            // Messages for form validationconfirmed
            messages:
            {
                celular:
                {
                	required: 'Preencha o seu celular'
                },
                email:
                {
                	required: 'Preencha o seu e-mail',
                	email: 'E-Mail inválido'
                },
                'email_confirmation':
                {
                	required: 'Confirme o seu e-mail',
                    email: 'E-Mail inválido',
                    equalTo: 'A confirmação do e-mail deve ser igual ao e-mail preenchido'
                },
                senha:
                {
                    required: 'Preencha a sua senha',
                    minlength: 'A senha deve ter no mínimo 4 caracteres',
                    maxlength: 'A senha deve ter até 12 caracteres'
                },
                senha_confirmation:
                {
                    required: 'Confirme a sua senha',
                    equalTo: 'A confirmação de senha deve ser igual a sua senha'
                },
                nome:
                {
                	required: 'Preencha o seu nome'
                },
                dt_nascimento: {
                    required: 'Preencha a sua data de nascimento',
                    dateBirth: 'Você precisa ser maior de 18 anos para participar'
                },
                documento:
                {
                	required: 'Prencha o seu CPF',
                	cpf: 'CPF inválido'
                },
                cep: {
					required: 'Preencha o seu CEP'
				},
				endereco: {
					required: 'Preencha o seu endereço'
				},
				numero: {
					required: 'Preencha o seu número'
				},
				bairro: {
					required: 'Preencha o bairro'
				},
				bairro: {
					required: 'Preencha o seu bairro'
				},
				cidade: {
					required: 'Preencha a sua cidade'
				},
				estado: {
					required: 'Preencha o seu estado'
				},                
                termos:
                {
                    required: 'Preencha este campo'
                },
                receive_information_email:
                {
                    required: 'Preencha este campo'
                },
                ciente:
                {
                    required: 'Preencha este campo'
                },
                keep_data:
                {
                    required: 'Preencha este campo'
                }
            },
            errorPlacement: function(error, element)
            {                
                if (element.attr("validate") == "checkbox"){
                    error.appendTo(element.parent("label").parent("div"));
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
                        if( data.status === true )
                        {
                            @if(session('influencer'))
                                ga.getAll()[0].send('event','Influencer', 'Cadastro', '{{ session('influencer') }}'); 
                            @endif

                            setTimeout(function(){
                                window.location = data.redirect;
                            }, 900);
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
});
</script>
@endsection("cadastro-usuario-script")
