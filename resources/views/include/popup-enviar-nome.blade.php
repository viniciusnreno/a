<section id="popup-enviar-nome" class="zoom-anim-dialog mfp-hide popup bg-popup popup-status popup-enviar-nome">
    <img class="logo-status" src="/assets/images/geral-logo.png">
        <h1>ESCREVA ABAIXO O<br>
        NOME DE SEUS 2 AMIGOS</h1>
        <p>até 20 caracteres</p>

        <div class="bg-cafu">
            <div class="box-nomes">
                <div class="redes">
                    <div class="input-field">
                        <input id="social_network1" name="social_network1" type="text" class="validate midia">
                        <label for="social_network1">AMIGO 1</label>
                    </div>
                </div>
                <div class="redes">
                    <div class="input-field">
                        <input id="social_network2" name="social_network2" type="text" class="validate midia">
                        <label for="social_network2">AMIGO 2</label>
                    </div>
                </div>
            </div>
            <button href="#popup-enviar-postagem" class="link-popup waves-button-input"><img src="/assets/images/convocar-btn.png" alt=""></button>
        </div>
</section>

@section('cadastrar-video-scripts')
<script>
function sendVideoForm(hasFriends = false){
    let valueFriends = hasFriends === false ? 0 : 1;
    
    $("#hasFriends").val(valueFriends);
    $("#formVideo").submit();
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
    $(function()
    {
        // Validation
        $("#formVideo").validate(
        {
            // Rules for form validation
            rules:
            {
                video:
                {
                    required: true
                },
                social_network:
                {
                    required: true
                }
            },
            // Messages for form validation
            messages:
            {
                video:
                {
                    required: 'Preencha o Vídeo',
                },
                social_network:
                {
                    required: 'Preencha a rede social'
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
                            
                            showAlerts('aposEnviarVideo');
                            /*
                            setTimeout(function(){
                                window.location = data.redirect;
                                }, 1500);
                                */
                            if(data.status === true){
                                $("#popup-enviar-sucesso span#videoFilePath").html(`${data.data.video_url}`);
                                $("#popup-enviar-sucesso span#videoSocialNetwork").html(`${data.data.video_social_network}`);
                                
                                if(data.data.video_friends == 1){
                                    $("#popup-enviar-sucesso span#videoFriends").html('2 amigos marcados. Obrigado');
                                } else {
                                    $("#popup-enviar-sucesso span#videoFriends").html('Você não marcou os amigos!');
                                }
                                $.magnificPopup.open({
                                    items: {
                                        src: '#popup-enviar-sucesso'
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
                                alert('Erro');
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
                        console.log(response);
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
                        
                        $.magnificPopup.open({
                            items: {
                                src: '#popup-enviar'
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
@endsection('cadastrar-video-scripts')