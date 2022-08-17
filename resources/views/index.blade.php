@extends('layouts.app')

@section("content")
   
	@include('include.menu')
	@include('include.box-principal')
	@include('include.box-participar')
	@include('include.box-premios')
	@include('include.box-produtos')
	@include('include.box-ganhador')
	@include('include.box-duvidas')
	
	@include('include.popup-celular')
	@include('include.popup-premio-instantaneo')
	@include('include.popup-sorteio')
	@include('include.popup-duvidas')
	@include('include.popup-produtos')
	@include('include.popup-regulamento')
	@include('include.popup-privacidade')
	@include('include.popup-regulamento-novo')
	@include('include.popup-sucesso')
	@include('include.popup-nao-enviar')
	@include('include.popup-enviar-nome')
	@include('include.popup-enviar-postagem')
	@include('include.popup-enviar-sucesso')
	@include('include.popup-cadastre-agora')
	@include('include.popup-fale-conosco')
	@include('include.popup-encerrado')

	@include('include.popup-cadastro')
	@include('include.popup-recuperar-senha')
	
	@auth
		@include('include.popup-logado-cadastrar-cupom')
		@include('include.popup-logado-atualizar')
		@include('include.popup-logado-minhas-participacoes')
	@endauth
	
	@include('layouts.include.footer')

@endsection("content")
