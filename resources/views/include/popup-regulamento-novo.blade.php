<section id="popup-regulamento-novo" class="zoom-anim-dialog regulamento mfp-hide popup bg-popup">
    <img class="logo" src="/assets/images/geral-logo.png"> 
    <div class="topo center">
        <h1>Regulamento</h1>
    </div>
    <div class="p-wrapper">
        <!-- <form id="formNewRules" method="post" action="<?php print route('cadastro.accept.new.rules.action'); ?>">
            @csrf
            <i class="material-icons">insert_drive_file</i>

            <div class="regulamento-wrapper col s12">
                <button id="botao-download" type="submit" class="waves-effect waves-light btn"><a href="https://drive.google.com/file/d/1una4cbaiL5MgG1LfOOWv-HTLE3yq3ZQB/view?usp=sharing">Download Regulamento</a></button>
                <br/>
                
                <p style="text-align: center;">
                    <iframe id="pdfView" src="https://drive.google.com/file/d/1una4cbaiL5MgG1LfOOWv-HTLE3yq3ZQB/preview" width="100%" height="100%" style="border:none;"></iframe>
                    
                </p>
            </div>

            <div class="check-regulamento col s12 pad-left">
                <label>
                    <input name="regulamento-novo" id="regulamento_novo" type="checkbox" validate="checkbox" class="validate" value="On" />
                    <span>Li e aceito os termos do <b class="reg-novo">regulamento.</b></span>
                </label> 
            </div>
        </form> -->

    </div>
</section>
@section('new-rules-scripts')
<script>
$(document).ready(function(){

    $("#regulamento_novo").on('click', function(){
        $("#formNewRules").submit();
    });

    // Validation		
    $("#formNewRules").validate(
    {					
        // Rules for form validation
        rules:
        {
            'regulamento-novo':
            {
                required: true
            }
        },

        // Messages for form validation
        messages:
        {
            'regulamento-novo':
            {
                required: 'Aceite o novo regulamento'
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
                            content: 'Regulamento aceito com sucesso!',
                            theme: 'yellow',
                            closeOnEsc: false,
                            closeBtn: false,
                            btns: [{'text': 'Fechar'}],
                            onClose: function(alert){
                                $("#menuCadastrarCupom").click();
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
@endsection('new-rules-scripts')
