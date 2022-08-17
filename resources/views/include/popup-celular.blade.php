<section id="popup-telefone" class="zoom-anim-dialog mfp-hide popup bg-popup"> 
    <img class="logo" src="/assets/images/geral-logo.png">
    <div class="topo center topo-telefone">
        <h1>Login/cadastro</h1>
    </div>
    <div class="p-wrapper fluid formLogin">
        <div class="row">
            <div class="col s12">
                <div class="row">
                    <div class="col m6 s12">
                        <h3 class="titulo"> 
                            Já é cadastrado?<br class="hide-on-med-and-up"> <br class="hide-on-small-only">
                            Faça o seu login :)
                        </h3>
                        <form id="formLogin" method="post" action="{{ route('login.auth') }}">
                            @csrf
                            <div class="input-field col s12">
                                <i class="material-icons prefix">phone</i>
                                <input id="login_celular" name="login_celular" type="tel" class="validate" placeholder="Celular">
                                
                            </div>
                            <div class="input-field col s12">
                                <i class="material-icons prefix">lock</i>
                                <input id="login_senha" name="login_senha" type="password" class="validate" placeholder="Senha">
                                
                            </div>
                            <div class="input-field col s12 center">
                                <button type="submit" class="waves-effect waves-light btn btn-entrar btn-popup ">ENTRAR</button>
                                <br/><a href="#popup-recuperar-senha" class="senha link-popup">Esqueci minha senha.</a>
                            </div>
                        </form>
                    </div>
                    <div class="col m6 s12 cadastro">
                        <p class="align-middle">Não é cadastrado? ;)</p>
                        <a href="#popup-cadastro" class="link-popup waves-effect waves-light btn btn-popup btn-nao-cadastrado">CLIQUE AQUI</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@section("scripts")
<script>
$(function()
{   
    let SPMaskBehaviorB = function (val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    },
    spOptionsB = {
        onKeyPress: function(val, e, field, options) {
            field.mask(SPMaskBehaviorB.apply({}, arguments), options);
        }
    };
    $('#login_celular').mask(SPMaskBehaviorB, spOptionsB);

    $("#formLogin").validate(
    {
        rules:
        {
            login_celular:
            {
                required: true
            },
            login_senha:
            {
                required: true
            }
        },
        messages:
        {
            login_celular:
            {
                required: 'Preencha o seu celular'
            },
            login_senha:
            {
                required: 'Preencha a sua senha'
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
                    if( data.status === true )
                    {
                        setTimeout(function(){
                            window.location = data.redirect;
                        }, 900);
                    }
                    else
                    {
                        $.loader('close');
                        errorAlert('Erro', data.msg);
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
@endsection("scripts")

