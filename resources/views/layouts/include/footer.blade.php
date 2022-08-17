<footer>
    <div class="container">
        <div class="row si">
            <ul class="nav">
                <li><a class="link-popup" href="#popup-fale-conosco">FALE CONOSCO</a></li>
            </ul>
        </div>
        <div class="row row-logo">
            <a target="_blank" href="https://brotolegal.com.br/"><img class="logo-footer" src="/assets/images/logo-rodape.png" alt="Broto Legal"></a>
        </div>
        <div class="row">
                <ul class="redes">
                    <li><a class="twitter" href="https://www.facebook.com/brotolegalalimentos" alt="Facebook" target="_blank">Facebook</a></li>
                    <li class="meio"><a class="youtube" href="https://www.instagram.com/brotolegal/" alt="Instagram" target="_blank">Instagram</a></li>
                    <li><a class="instagram" href="https://www.youtube.com/user/brotolegalalimentos" alt="Youtube" target="_blank">Youtube</a></li>
                </ul>
            </div>
            <div class="row last">
                <p class="col s10 push-s1 promocao font-bold">nononononononnonnonono</p>
            </div>
        </div>
    </div>
</footer>
<!-- Js -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="{{ asset('assets/js/form/jquery.form.min.js') }}"></script>
<script src="{{ asset('assets/js/form/jquery.priceFormat.min.js') }}"></script>
<script src="{{ asset('assets/js/loader/jquery.loader.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-loading/jquery-loading.min.js') }}"></script>
<script src="{{ asset('assets/js/jAlert/jAlert.min.js') }}"></script>
<script src="{{ asset('assets/js/jAlert/jAlert-functions.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.js"></script>
@push("scripts")
<script src="/assets/js/form/jquery.mask.min.js"></script>
<script src="/assets/js/tiny-slider/tiny-slider.js"></script>
<script src="/assets/js/script.js?v1"></script>
<script src="/assets/js/loading-indicator-view/loadingView.js"></script>
@endpush
@stack("scripts")
@yield("scripts")
@yield("cadastro-usuario-script")
@yield("cadastrar-cupom-scripts")
@yield("fale-conosco-scripts")
@yield("cadastrar-update-scripts")
@yield("recuperar-senha-scripts")
@yield("cadastrar-video-scripts")
<!-- Modal Structure -->
<!-- <div class="aviso" id="aviso_cookie" style=" width:100%">
    <div class="avisocookies" class="w-100 flex-wrap align-items-center text-center text-sm-left d-flex" style="display: block;">
        <div class="row">
            <div class="col s12 m9">
                <p>
                Usamos cookies para otimizar as funcionalidades do site, personalizar o conteúdo proposto e dar a você a melhor experiência possível. Acesse a nossa <a target="_blank" href="https://www.Toshiba.com.br/politica-de-privacidade">(Política de Privacidade)</a>                </p>
            </div>
            <div class="col s12 m3">
                <button class="modal-fechar botao full">Aceitar Cookies</button>
            </div>
        </div>
    </div>
</div> -->

<!-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        if(!document.cookie.includes("_accepted=true")){
            setTimeout(function(){
                $('#aviso_cookie').fadeIn();
            },1000)
            $('.modal-fechar').click(function(){
                document.cookie = "_accepted=true";
                $('#aviso_cookie').fadeOut();
            })
        }
    });
  
</script> -->
    
</body>
</html>





