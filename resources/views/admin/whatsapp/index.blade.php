{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Whatsapp')

@section('content_header')
    <h1>Whatsapp</h1>
@stop

@section('content')
<section class="main-grid">  
  <aside class="main-side">
     <header class="common-header">
       <!--div class="common-header-start">
           <button class="u-flex js-user-nav">
             <img class="profile-image" src="" alt="Elad Shechter">
             <div class="common-header-content">
                <h1 class="common-header-title">Elad Shechter</h1>
            </div>
           </button>       
       </div-->
     </header>
    <section class="common-alerts"><!-- optional alert message --></section>
    <section class="common-search">
        <input type="search" class="text-input" placeholder="Procurar conversa">
    </section>
    <section class="chats">
      <ul class="chats-list">
        @foreach($users as $item)
          <li class="chats-item" @if(preg_replace("/^\d{2}/", "", $item->author) == $currentMobile) style="background-color:#ECECEC;" @endif)>
            <div class="chats-item-button js-chat-button" role="button" tabindex="0">
              <!--img class="profile-image" src="https://scontent.fhfa1-2.fna.fbcdn.net/v/t1.0-1/p100x100/10325799_276849335820343_269039155920934591_n.png?_nc_cat=101&_nc_sid=dbb9e7&_nc_ohc=fxci6qYcSvoAX-bZfj2&_nc_ht=scontent.fhfa1-2.fna&oh=ad1c246e7e4a52c607aafd99ed7217a2&oe=5EEAF8B0" alt="Elad Shechter"-->
              <header class="chats-item-header">
                <a href="{{ route('admin.whatsapp.index') }}/{{ preg_replace("/^\d{2}/", "", $item->author) }}">
                  <h3 class="chats-item-title" style="font-size: 16rem">{{ preg_replace("/^\d{2}/", "", $item->author) }}</h3>
                </a>
                <!--time class="chats-item-time">12:30</time-->
              </header>
              <div class="chats-item-content">
                <!--p class="chats-item-last">Beside it yo can follow my twitter account: @eladsc</p>
                <ul class="chats-item-info">
                  <li class="chats-item-info-item"><span class="icon-silent">🔇</span></li>
                  <li class="chats-item-info-item"><span class="unread-messsages"></span></li>
                </ul-->
              </div>
            </div>
          </li>
        @endforeach
      </ul>
    </section>
  </aside>  
  <main class="main-content">
    <header class="common-header">
       <div class="common-header-start">  
         <button class="common-button is-only-mobile u-margin-end js-back"><span class="icon icon-back">⬅</span></button>
         <button class="u-flex js-side-info-button">
             <!--img class="profile-image" src="" alt="CSS Masters Israel"-->
             <div class="common-header-content">
               <h2 class="common-header-title" style="margin: 0; font-size:16rem;">{{ $currentMobile }}</h2>
               <!--p class="common-header-status">Online</p-->
             </div>
           </button>       
       </div>
       <!--nav class="common-nav">
           <ul class="common-nav-list">
             <li class="common-nav-item">
               <button class="common-button">
                 <span class="icon">🔎</span>
               </button>
             </li>
             <li class="common-nav-item">
               <button class="common-button">
                 <span class="icon icon-attach">📎</span>
               </button>
             </li>
             <li class="common-nav-item">
               <button class="common-button u-animation-click js-side-info-button">
                 <span class="icon icon-menu" aria-label="menu"></span>
               </button>
             </li>
           </ul>
         </nav-->
     </header>
    <div class="messanger">
      <ol class="messanger-list">
        <!--li class="common-message is-time">
          <p class="common-message-content">
            Today
          </p>          
        </li-->
        @foreach($messages as $item)
          <li class="common-message {{ $item['type'] }}">
            <p class="common-message-content">
              {{ $item['body'] }}
            </p>
            <!--span class="status is-seen">✔️✔️</span-->
            <time datetime>{{ date_format(date_create($item['created_at']), 'd/m/Y @ H:i:s') }}</time>
          </li>
        @endforeach
    </ol>
    </div>    
    <div class="message-box">
      <!--button class="common-button"><span class="icon">😃</span></button>
      <div class="text-input" id="message-box" placeholder="Type a message" contenteditable></div>
      <button id="voice-button" class="common-button"><span class="icon">🎤</span></button>
      <button id="submit-button" class="common-button"><span class="icon">➤</span></button-->
    </div>  
  </main>
  <aside class="main-info u-hide">    
      <header class="common-header">
        <button class="common-button js-close-main-info"><span class="icon">❌</span></button>
        <div class="common-header-content">
          <h3 class="common-header-title">Info</h3>
        </div>
      </header>
      <div class="main-info-content">
        <section class="common-box">
          <img class="main-info-image" src="" alt="CSS Masters Israel">
          <h4 class="big-title">CSS Masters Israel</h4>
          <p class="info-text">Created 6/11/2013 at 22:45</p>
        </section>
        <section class="common-box">
          <h5 class="section-title">Description</h5>
          <p>Out main channel of the comunity is on Fecbook: <a href=""></a></p>
        </section>
        <section class="common-box">
          <h5 class="section-title">Other content</h5>
          <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Architecto odit voluptatem magnam sequi dolorem soluta assumenda ipsum iusto culpa velit repudiandae vitae minus minima corporis labore sit, molestias, a ut!</p>
        </section>
    </div>
  </aside>
</section>
@stop

@include('admin.include.assets')