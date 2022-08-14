/**
 * Instancia os links que podem abrir um lightbox
 *
 * @param {string} link Elementos que serão instanciados
 * @param {object} obj Sobrescreve as opções padrão
 * @return {void}
 */
function openPopup(link, obj){
    // merge das opções básicas + específicas
    var options = Object.assign({
        type: 'inline',
        fixedContentPos: true,
        fixedBgPos: true,
        overflowY: 'auto',
        closeBtnInside: true,
        preloader: false,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-slide-bottom',
        modal: false,
        closeOnBgClick: true,
        enableEscapeKey: true,
        showCloseBtn: true,
        alignTop: true
    }, obj)
    // instanciando o plugin
    $(link).magnificPopup(options);
}

/**
 * Função para exibir os alertas
 * Usado nos popups de Cadastrar dados/cupom
 * @param {string} item Indica qual deverá ser exibido
 * @return {void}
 */
function showAlerts(item){
    var options = {
        aposEnviarCupom: {
            html: 'Cupom enviado com sucesso!',
            classes: 'green accent-4'
        },
        aposEnviarVideo: {
            html: 'Amigo indicado com sucesso!',
            classes: 'green accent-4'
        },
        cupomNaoEnviado: {
            html: 'Ops! Você ainda não enviou o seu cupom.<br>Participar é muito fácil, não fique de fora.',
            classes: 'blue accent-4'
        },
        erroCupom: {
            html: 'Ops!<br>Faça um upload da imagem do seu cupom para ler os dados.',
            classes: 'red accent-2'
        }
    } 
    M.toast(options[item])
}

/**
 * Bindando cliques dos popups
 * @return {void}
 */
function bindPopupsClicks(){
    openPopup('.link-popup')
    openPopup('.link-popup-telefone',{
        focus: '#telefone',
        alignTop: false
    })
    openPopup('.popup-youtube',{
        type: 'iframe',
        alignTop: false
    })
}

/**
 * Valida se o usuário já participou
 * Exibe um alerta caso ainda não tenha participado
 * @return {void}
 */
function validaParticipacoes(){
    if($('.minhas-participacoes .collapsible > li').length == 0){
        showAlerts('cupomNaoEnviado');
    }
}

// Inicializando o "tooltip" da página (Materialize)
document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.tooltipped');
    var instances = M.Tooltip.init(elems, {
        html: true
    });
});

/**
 * Binda os cliques e executa funções na inicialização
 *
 * @return {void}
 */
$(document).ready(function(){
    
    // Bindando os links que serão clicáveis para abrir os popups
    bindPopupsClicks();
    
    // Inicializando os "selects" da página (Materialize)
    $('select').formSelect();

    var element = document.getElementById('estado');
    element.onchange = function(item){
        var selected = item.target.selectedOptions[0].text;
     
        var cityElement = document.getElementById('cidade');
        cityElement.value = '';
        cityElement.disabled = false;
        if(selected.indexOf('(') != -1){
            var name = selected.replace(/.*Apenas (.*)?\)/g, '$1')
            cityElement.value = name;
            cityElement.focus();
            cityElement.readOnly = true;
        }
    }
    

    // Inicializando a seleção do menu
    $('.scrollspy').scrollSpy(
        {
            scrollOffset:55
        }
    );

    // Inicializando o menu
    $('.sidenav').sidenav();

    // Fechando os links Links
    $('.sidenav li a').click(function(){
        $('.sidenav').sidenav('close');
    })

    $("#menu .dropdown-trigger").dropdown({hover:true});

    // Toggle do ícone (pra cima e pra baixo) nos accordions
    // Usado no popup de "Dúvida" e "Minhas Participações"
    function toggleText(str){ return (str == 'keyboard_arrow_down') ? 'keyboard_arrow_up' : 'keyboard_arrow_down' }
    $('.collapsible').collapsible({
        onOpenStart: function(el) {
            var text = $(el).find('i.seta').text();
            $(el).find('i.seta').text(toggleText(text));
        },
        onCloseStart: function(el) {
            var text = $(el).find('i.seta').text();
            $(el).find('i.seta').text(toggleText(text));
        }
    })

    // Tratamento para usuário informar produtos
    // Usado no popup "Cadastrar cupom"
    $('.adicionar-numero li input').change(function(e) {
        var num = $(e.target).val() || '';
        if(num.length > 0 && num.match(/\d+/g) && num.match(/\d+/g).length > 0){
            num = num.match(/\d+/g).join('');
            if(num == 0) num = '';
        }else{
            num = '';
        }
        $(e.target).val(num);
        if(num.length > 0){
            $(e.target).parent().find('i').show();
        }else{
            $(e.target).parent().find('i').hide();
        }
    });
  
    // Carrossel do box de produtos da Home
    $('.carousel').each(function(){
        var slider = tns({
            container: this,
            slideBy: 'page',
            speed: 400,
            autoplayButtonOutput: false,
            autoplay: true,
            controls: false,
            navPosition: 'bottom'
        })
    })
    $('#corona-id').trigger('click');

    
    $("[data-count]").on('change', soma);
    var result = document.getElementById('resultado')
    
    function soma(){
        var items = $("[data-count]");
        var sum = 0;
        items.each(function(){
            sum += parseInt($(this).val() || 0);
        })

        result.innerHTML = sum
    }
    
    // envio de dados para o google analytics 
    $('#menuCadastrarCupom').click(function(){
        console.log('[GA]','Menu Cadastro')
        /*
        gtag('event', 'click', {
            'event_category' : 'Cadastro',
            'event_label' : 'click-menu'
            }
        );
        */
    })
    $('#btn-cadastrar').click(function(){
        console.log('[GA]','Botão Cadastro')
        /*
        gtag('event', 'click', {
            'event_category' : 'Cadastro',
            'event_label' : 'click-botão'
            }
        );
        */
    })

    var elem = document.querySelector('.collapsible.expandable');
    var instance = M.Collapsible.init(elem, {
      accordion: false
    });

    $(document).ready(function(){
        $('.collapsible').collapsible();
      });

    $(function () {
        x=3;
        $('#myList1 li').slice(0, 3).show();
        $('#myList2 li').slice(0, 3).show();
        $('#loadMore').on('click', function (e) {
            e.preventDefault();
            if(x == 12){
                x += 8;
            }else{
                x += 3;
            }

            $('#myList1 li').slice(0, x).slideDown();
            $('#myList2 li').slice(0, x).slideDown();
            console.log(x)
        });
    });

    $(function () {
        window.onload = function() {
            var canvas = document.getElementById("post-canvas");
            var img = document.getElementById("post");
            var btn = document.getElementById("btn-post");
            var nome1 = document.getElementById("social_network1");
            var nome2 = document.getElementById("social_network2");
            var ctx = canvas.getContext("2d");
            
            
            btn.onclick = function(){
                canvas.style.letterSpacing = 2.5 + "px";
                ctx.font = "bold 30px Gilroy";
                ctx.fillStyle = "#81BC7F";
                ctx.fillStyle = "bold";
                ctx.drawImage(img, 10, 10);
                ctx.textAlign = "center";
                ctx.fillText(nome1.value, 370, 460);
                ctx.fillText(nome2.value, 370, 540);
                download(canvas, 'promocao-cartao-vip-toshiba.jpg');
            }
        };
        /* Canvas Donwload */
        function download(canvas, filename) {
            var lnk = document.createElement('a'), e;
        
            lnk.download = filename;
        
            lnk.href = canvas.toDataURL("image/png;base64");
        
            if (document.createEvent) {
            e = document.createEvent("MouseEvents");
            e.initMouseEvent("click", true, true, window,
                            0, 0, 0, 0, 0, false, false, false,
                            false, 0, null);
        
            lnk.dispatchEvent(e);
            } else if (lnk.fireEvent) {
                lnk.fireEvent("onclick");
            }
        }
    });

    
    
})

