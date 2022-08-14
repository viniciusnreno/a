@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/js/jquery-loading/jquery-loading.min.css?v1') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/js/jAlert/jAlert.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/js/jquery-magnific-popup/magnific-popup.css') }}">
    @if(Route::currentRouteName() == 'admin.whatsapp.index')
        <link rel="stylesheet" href="{{ asset('assets/css/whatsapp.css') }}">
    @endif
@stop

@section('js')
    <script src="{{ asset('assets/js/form/jquery.form.min.js') }}"></script>
    <script src="{{ asset('assets/js/form/jquery.maskedinput.min.js') }}"></script>
    <script src="{{ asset('assets/js/form/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/form/jquery.priceFormat.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-loading/jquery-loading.min.js') }}"></script>
    <script src="{{ asset('assets/js/jAlert/jAlert.min.js') }}"></script>
    <script src="{{ asset('assets/js/jAlert/jAlert-functions.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-magnific-popup/jquery.magnific.popup.min.js') }}"></script>
    <script>
        $(function () {

            if (window.location.href.indexOf("whatsapp") > -1) {
                $(".main-header").remove();
                $(".main-sidebar").remove();
            }

            let table = $('.dataTable').DataTable({
            dom: 'Bfrtip',
            'paging'      : true,
            'lengthChange': false,
            'searching'   : true,
            'ordering'    : true,
            'info'        : true,
            'autoWidth'   : false,
            'buttons'     : ['excel']
            });

            $('.img-cupom').magnificPopup({
                type: 'image',
                closeOnContentClick: true,
                mainClass: 'mfp-img-mobile',
                image: {
                    verticalFit: true
                }
            });

            $('.ajax-popup').magnificPopup({
                type: 'ajax',
                alignTop: true,
                overflowY: 'scroll' 
            });

            $("#validate").on("change", function(){
                if($(this).val() == 1){
                    $(".reasonContainer").addClass("d-none");
                    // $(".passwordPicPay").removeClass("d-none");
                } else {
                    $(".reasonContainer").removeClass("d-none");
                    // $(".passwordPicPay").addClass("d-none");
                }
            });

        });


        /* FORM VALIDACAO CUPOM */

        $(function()
        {
            jQuery.validator.addMethod("shouldValidate", function(value, element, param) {
                if($("#validate").val() == 1){
                    return true;
                } else {
                    if(value != ""){
                        return true;
                    } else {
                        return false;
                    }
                }
            }, "Preencha este campo");
            // Validation
            $("#adminValidateCoupon").validate(
            {
                // Rules for form validation
                rules:
                {
                    validate:
                    {
                        required: true
                    },
                    reason: {
                        shouldValidate: true
                    },
                    cinemaType: {
                        required: function(element) {
                            return $("#hiddenPrizeType").val() == 1;
                        }
                    }
                },

                // Messages for form validation
                messages:
                {
                    validate:
                    {
                        required: 'Preencha este campo'
                    },
                    reason: {
                        required: "Preencha este campo"
                    },
                    cinemaType: {
                        required: "Preencha este campo"
                    }
                },

                // Do not change code below
                errorPlacement: function(error, element)
                {
                    if($(element).attr('name') != 'cinemaType'){
                        error.insertAfter(element);
                    } else {
                        window.teste = element;
                        element.closest("div").find("div#customError").append(error);
                    }
                    return false;
                },
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
                        },
                        success: function(data)
                        {
                            if( data.status === true )
                            {
                                setTimeout(function(){
                                    window.location = data.redirect;
                                }, 900);

                                /*
                                $.jAlert({
                                    title: 'Informação',
                                    content: 'Tarefa con',
                                    theme: 'yellow',
                                    closeOnEsc: false,
                                    closeBtn: false,
                                    btns: [{'text': 'OK'}],
                                    onClose: function(alert){
                                        if(data.redirect){  
                                        // ga.getAll()[0].send('event','cadastro-cupom','sucesso-ok','clique');
                                        setTimeout(function(){
                                            window.location = data.redirect;
                                            }, 900);
                                        }
                                    }
                                });
                                */
                            }
                            else
                            {
                                // $.loader('close');
                                errorAlert('Erro', data.error);
                            }
                        },
                        error: function(response, status, err){
                            console.log(response);
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
                                if(response.responseJSON.message){
                                    errors.push(response.responseJSON.message);
                                } else {
                                    errors.push('Houve um erro desconhecido. Tente novamente');
                                }
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

            $("#adminSaveCodes").validate(
            {
                // Rules for form validation
                rules:
                {
                    prize:
                    {
                        required: true
                    },
                    codes:
                    {
                        required: true
                    }
                },

                // Messages for form validation
                messages:
                {
                    prize: {
                        required: "Preencha este campo"
                    },
                    codes: {
                        required: "Preencha este campo"
                    }
                },

                // Do not change code below
                errorPlacement: function(error, element)
                {
                    error.insertAfter(element);
                    return false;
                },
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
                        },
                        success: function(data)
                        {
                            if( data.status === true )
                            {
                                setTimeout(function(){
                                    window.location = data.redirect;
                                }, 900);

                                /*
                                $.jAlert({
                                    title: 'Informação',
                                    content: 'Tarefa con',
                                    theme: 'yellow',
                                    closeOnEsc: false,
                                    closeBtn: false,
                                    btns: [{'text': 'OK'}],
                                    onClose: function(alert){
                                        if(data.redirect){  
                                        // ga.getAll()[0].send('event','cadastro-cupom','sucesso-ok','clique');
                                        setTimeout(function(){
                                            window.location = data.redirect;
                                            }, 900);
                                        }
                                    }
                                });
                                */
                            }
                            else
                            {
                                // $.loader('close');
                                errorAlert('Erro', data.error);
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
                                if(response.responseJSON.message){
                                    errors.push(response.responseJSON.message);
                                } else {
                                    errors.push('Houve um erro desconhecido. Tente novamente');
                                }
                            }
                            errorAlert('Erro', errors.join("<br />"));
                        },
                        complete: function(){
                            $('body').loading('stop');
                        },
                        dataType:'json'
                    })        ;
                }
            });
        });
    </script>

    @cloudinaryJS
    
@stop