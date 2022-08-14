<section id="popup-enviar-postagem" class="zoom-anim-dialog mfp-hide popup popup-canvas bg-popup popup-status popup-enviar-postagem">
    <form id="formPost" method="post" action="{{ route('cadastro.video.action') }}" enctype="multipart/form-data">
        @csrf
        <h1>BAIXE O POST <a style="cursor:pointer;" id="btn-post">AQUI</a>, <br>
        COMPARTILHE E MARQUE <br>
        SEUS 2 AMIGOS</h1>
        <div class="box-midias">
            <div class="instagram">
                <img src="/assets/images/instagram-logo.png" alt="">
                <h2>POST NO INSTAGRAM</h2>
                <p>Compartilhe no feed <br>
                e deixe seu <span>perfil<br>
                aberto</span> até o final<br>
                da promoção</p>
            </div>
            <div class="facebook">
                <img src="/assets/images/facebook-logo.png" alt="">
                <h2>POST NO FACEBOOK</h2>
                <p>Compartilhe em sua<br>
                linha do tempo na<br>
                opção <span>público</span> até o<br>
                final da promoção</p>
            </div>
        </div>

        <hr>

            <p class="txt-placa">Não se esqueça de incluir na legenda:<br>
            <strong>#Cartao<span class="vip">Vip</span>Toshiba<br>
            <span>CARTÃO<span class="vip">VIP</span>TOSHIBA</span><small><span class="vip">.COM</span>.BR</small></strong></p>

        <div class="redes">
            <div class="input-field">
                <input id="social_network_link" name="social_network_link" type="text" class="validate midia">
                <label for="social_network_link">COLE AQUI O LINK DA POSTAGEM DE SUA REDE SOCIAL</label>
            </div>
        </div>

        <button type="submit" class="waves-button-input"><img src="/assets/images/enviar-btn.png" alt="ENVIAR"></button><br>
        <img src="/assets/images/cafu-cima.png" alt="">
    </form>
    <img id="post" src="/assets/images/post-imagem-canva.jpg" style="display: none; " >
    <canvas style="" id="post-canvas" width="730" height="730"></canvas>
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