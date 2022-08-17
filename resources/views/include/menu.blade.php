<nav id="menu">
    <div class="container">
        <div class="nav-wrapper">
            <a href="#" data-target="mobile-nav" class="sidenav-trigger icon-menu"><i class="material-icons">menu</i></a>
            <ul class="center hide-on-med-and-down">
                <li><a href="#box-principal">HOME</a></li>
                <li><a href="#box-participar">COMO PARTICIPAR</a></li>
                <li><a href="#box-premios1">POTES COLECIONÁVEIS</a></li>
                <li><a href="#box-premios2">SORTEIO</a></li>
                <li><a href="#box-produtos">PRODUTOS PARTICIPANTES</a></li>
                <!-- <li><a class="link-popup" href="#popup-sorteio">GANHADORES</a></li> -->
                <li><a>GANHADORES</a></li>
                <li><a href="#box-duvidas">DÚVIDAS FREQUENTES</a></li>
                <li><a class="link-popup" href="#popup-regulamento">REGULAMENTO</a></li>
                <!-- <li><a class="link-popup" href="#popup-enviar-postagem">p</a></li>
                <li><a class="link-popup" href="#popup-enviar">enviar</a></li>
                <li><a class="link-popup" href="#popup-enviar-postagem">enviar postagem</a></li>
                <li><a class="link-popup" href="#popup-enviar-sucesso">sucesso</a></li> -->
                @guest
                <li><a class="link-popup-telefone" href="#popup-cadastro">CADASTRE-SE</a></li>
                <li class="login"><a class="btn waves-effect waves-light link-popup-telefone" href="#popup-telefone"><i class="material-icons">person</i>LOGIN</a></li>
                @endguest
                @auth
                <li><a id="menuCadastrarCupom" class="link-popup" href="#popup-cupom" id="menu-cadastrar">CADASTRAR CUPOM</a></li>
                <li><a class="link-popup" href="#popup-minhas-participacoes">MINHA PARTICIPAÇÃO</a></li>
                <li class="right"><a href="#" onclick="document.getElementById('logout-form').submit();">SAIR</a></li>
                <li class="right"><a class="link-popup nome-logado" href="#popup-atualizar">Olá <?php print Auth::user()->name; ?></a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<div class="sidenav" id="mobile-nav">
    <ul>
        <li><a href="#box-principal">HOME</a></li>
        <li><a href="#box-participar">COMO PARTICIPAR</a></li>
        <li><a class="dropdown-trigger" href="#!" data-target="dropdown1">PRÊMIOS</a></li>
        <li><a href="#box-produtos">PRODUTOS PARTICIPANTES</a></li>
        <li><a href="#box-aumente">AUMENTE SUAS CHANCES</a></li>
        <li><a class="link-popup" href="#popup-sorteio">GANHADORES</a></li>
        <li><a href="#box-duvidas">DÚVIDAS FREQUENTES</a></li>
        <li><a class="link-popup" href="#popup-regulamento">REGULAMENTO</a></li>
        @guest
        <li class="login"><a class="link-popup" href="#popup-telefone">LOGIN</a></li>
        @endguest
        @auth
        <li><a id="menuCadastrarCupom" class="link-popup" href="#popup-cupom" id="menu-cadastrar">CADASTRAR CUPOM</a></li>
        <li><a class="link-popup" href="#popup-minhas-participacoes">MINHA PARTICIPAÇÃO</a></li>
        <li><a href="#" onclick="document.getElementById('logout-form').submit();">SAIR</a></li>
        <li><a class="link-popup" href="#popup-atualizar">Olá <?php print Auth::user()->name; ?></a></li>
        @endauth
        <img class="logo-menu" src="/assets/images/logo-home.png" alt="Promoção Broto Legal">
        
    </ul>
</div>

<ul id="dropdown1" class="dropdown-content">
    <li><a class="dpdown2" href="#box-premios1">NA HORA</a></li>
    <li><a class="dpdown2" href="#box-premios2">SORTEIO</a></li>
</ul>


<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

