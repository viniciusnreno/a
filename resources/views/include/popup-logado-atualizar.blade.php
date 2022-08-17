<section id="popup-atualizar" class="zoom-anim-dialog mfp-hide popup bg-popup">
    <img class="logo" src="/assets/images/geral-logo.png">   
    <div class="topo center faleconosco">
        <h1>
            Se precisar corrigir seus dados abaixo,<br>
            informe através de nosso <a href="#popup-fale-conosco" class="link-popup">fale conosco</a><br>
            que a gente troca rapidinho!
        </h1>
    </div>
    <nav class="nav-popup">
        <div class="container">
            <div class="nav-wrapper">
                <ul class="center">
                    <li><a class="link-popup" href="#popup-cupom"><i class="material-icons prefix">receipt</i>CADASTRAR <br class="hide-on-med-and-up">CUPOM</a></li>
                    <li><a class="link-popup" onclick="validaParticipacoes()" href="#popup-minhas-participacoes"><i class="material-icons prefix">border_color</i>MINHA <br class="hide-on-med-and-up">PARTICIPAÇÃO</a></li>
                    <li><a class="link-popup active" href="#popup-atualizar"><i class="material-icons prefix">account_circle</i>MEUS DADOS</a></li>
                    <li><a class="link-popup" href=""><i class="material-icons prefix">assignment</i></a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="p-wrapper">
        <div class="row">
            <form id="formCadastrarUpdate" method="post" action="{{ route('cadastro.update.action') }}">
                <div class="input-field col s12">
                    <i class="material-icons prefix">face</i>
                    <input id="md_nome" name="md_nome" type="text" class="validate" value="<?php print Auth::user()->name; ?>">
                    <label for="md_nome">Nome Completo</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">phone</i>
                    <label for="md_celular">Celular</label>
                    <input id="md_celular" type="text" name="md_celular" class="validate" value="<?php print Auth::user()->mobile; ?>">
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">mail_outline</i>
                    <label for="md_email">E-mail</label>
                    <input id="md_email" type="email" name="md_email" class="validate" required data-error="Informe o e-mail" value="<?php print Auth::user()->email; ?>">
                </div>
                <!--div class="input-field col s12">
                    <i class="material-icons prefix">cake</i>
                    <input id="nascimento" name="nascimento" type="text" class="validate" value="<?php print dateBR(Auth::user()->birth_date, false); ?>">
                    <label for="nascimento">Nascimento</label>
                </!--div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">map</i>
                    <select id="md_estado" name="md_estado">
                    <option value="#" selected>Selecione</option>
                        <option value="MG" {{ selectedValue('MG', Auth::user()->state) }}>MG</option>
                        <option value="RJ" {{ selectedValue('PR', Auth::user()->state) }}>PR</option>
                        <option value="RJ" {{ selectedValue('RJ', Auth::user()->state) }}>RJ</option>
                        <option value="SP" {{ selectedValue('SP', Auth::user()->state) }}>SP</option>
                    </select>
                    <label>Estado</label>
                </div>
                <div class="input-field col s12">
                    <i class="material-icons prefix">location_city</i>
                    <input id="md_cidade" name="md_cidade" type="text" class="validate" value="<?php print Auth::user()->city; ?>">
                    <label for="md_cidade">Cidade</label>
                </div-->

                <!--div class="btn-enviar col s12">
                    <button class="waves-effect waves-light btn">ATUALIZAR</button>
                </!--div-->
            </form>
        </div>
    </div>
</section>

@section('cadastrar-update-scripts')
<script>
window.onload = function(){

	$("#md_cep").mask("99999-999",{});
    $("#md_nascimento").mask("99/99/9999",{});
    
	
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
        $("#formCadastrarUpdate").validate(
        {
            // Rules for form validation
            rules:
            {
                md_nome:
                {
                	required: true
                },
                md_email:
                {
                	required: true
                },
                md_nascimento: {
                    required: true,
                    dateBirth: true
                },
                md_celular:
                {
                	required: true
                },
                md_cep: 
                {
                    required: true
                },              
                md_endereco: 
                {
                    required: true
                },
                md_numero: 
                {
                    required: true
                },
                md_complemento: 
                {
                    required: false
                },
                md_bairro: 
                {
                    required: true
                },
                md_estado: 
                {
                    required: true,
                    notEqual: '#'
                },
                md_cidade: 
                {
                    required: true
                }
            },

            // Messages for form validationconfirmed
            messages:
            {
                md_nome:
                {
                	required: 'Preencha o seu nome'
                },
                md_nascimento: {
                    required: 'Preencha a sua data de nascimento',
                    dateBirth: 'Você precisa ser maior de 18 anos para participar'
                },
                md_cep: {
					required: 'Preencha o seu CEP'
				},
                md_endereco: {
					required: 'Preencha o seu endereço'
				},
                md_numero: {
					required: 'Preencha o seu número'
				},
                md_complemento: {
					required: 'Preencha o complemento'
				},
                md_bairro: {
					required: 'Preencha o seu bairro'
				},
                md_cidade: {
					required: 'Preencha a sua cidade'
				},
                md_estado: {
					required: 'Preencha o seu estado'
				}
            },
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
};
</script>
@endsection('cadastrar-update-scripts')
