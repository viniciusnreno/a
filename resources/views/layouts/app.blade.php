<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <title>Cozinha Legal é com a Broto Legal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="description" content="Cozinha Legal é com a Broto Legal"/>
    <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">
    <meta property="og:description" content="Cozinha Legal é com a Broto Legal" />
    <meta property="og:image" content="https://cartaoviptoshiba.com.br/assets/images/fb-img.jpg">
	<meta property="og:image:type" content="image/jpeg">
	<meta property="og:image:height" content="600">
	<meta property="og:image:width" content="800">
	<meta property="og:locale" content="pt_BR">
	<meta property="og:site_name" content="Toshiba">
	<meta property="og:title" content="Cartao VIP  -  Toshiba">
	<meta property="og:type" content="website">
	<meta property="og:url" content="https://cartaoviptoshiba.com.br">
	<meta name="adopt-website-id" content="c8d5a563-efd0-4801-9a1f-11f48a7ea86f" />
	<script src="//tag.goadopt.io/injector.js?website_code=c8d5a563-efd0-4801-9a1f-11f48a7ea86f" class="adopt-injector"></script>

    <!-- Csss -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.css">
    <link rel="stylesheet" href="/assets/css/style.css?v3" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/js/loader/jquery.loader.min.css?v1') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/js/jquery-loading/jquery-loading.min.css?v1') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/js/jAlert/jAlert.css') }}">
    
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-3HMSYWG1Y0"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-3HMSYWG1Y0');
    </script>

</head>
@auth
<body class="logged">
@endauth
@guest
<body>
@endguest
@yield("content")
