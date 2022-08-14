{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Whatsapp')

@section('content_header')
    <h1>Whatsapp</h1>
@stop

@section('content')
<div class="container app">
    <div class="row app-one">

      <div class="col-sm-4 side">
        <div class="side-one">
          <!-- Heading -->
          <div class="row heading">
            <div class="col-sm-3 col-xs-3 heading-avatar">
              <div class="heading-avatar-icon">
                <!--img src="http://shurl.esy.es/y"-->
              </div>
            </div>
            <!--div class="col-sm-1 col-xs-1  heading-dot  pull-right">
              <i class="fa fa-ellipsis-v fa-2x  pull-right" aria-hidden="true"></i>
            </div>
            <div class="col-sm-2 col-xs-2 heading-compose  pull-right">
              <i class="fa fa-comments fa-2x  pull-right" aria-hidden="true"></i>
            </div-->
          </div>
          <!-- Heading End -->

          <!-- SearchBox -->
          <div class="row searchBox">
            <div class="col-sm-12 searchBox-inner">
              <div class="form-group has-feedback">
                <input id="searchText" type="text" class="form-control" name="searchText" placeholder="Procurar">
                <span class="glyphicon glyphicon-search form-control-feedback"></span>
              </div>
            </div>
          </div>

          <!-- Search Box End -->
          <!-- sideBar -->
          <div class="row sideBar">
            @foreach($users as $item)
                <div class="row sideBar-body">
                <div class="col-sm-3 col-xs-3 sideBar-avatar">
                    <div class="avatar-icon">
                    <!--img src="http://shurl.esy.es/y"-->
                    </div>
                </div>
                <div class="col-sm-9 col-xs-9 sideBar-main">
                    <div class="row">
                    <div class="col-sm-8 col-xs-8 sideBar-name">
                        @if($item->name != NULL)
                            <span class="name-meta">{{ $item->name }} ({{ $item->mobile }})</span>
                        @else
                            <span class="name-meta">{{ $item->mobile }}</span>
                        @endif
                    </div>
                    <div class="col-sm-4 col-xs-4 pull-right sideBar-time">
                        <span class="time-meta pull-right">18:18
                    </span>
                    </div>
                    </div>
                </div>
                </div>
            @endforeach
          </div>
          <!-- Sidebar End -->
        </div>
        <div class="side-two">

          <!-- Heading -->
          <div class="row newMessage-heading">
            <div class="row newMessage-main">
              <div class="col-sm-2 col-xs-2 newMessage-back">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
              </div>
              <div class="col-sm-10 col-xs-10 newMessage-title">
                New Chat
              </div>
            </div>
          </div>
          <!-- Heading End -->

          <!-- ComposeBox -->
          <div class="row composeBox">
            <div class="col-sm-12 composeBox-inner">
              <div class="form-group has-feedback">
                <input id="composeText" type="text" class="form-control" name="searchText" placeholder="Search People">
                <span class="glyphicon glyphicon-search form-control-feedback"></span>
              </div>
            </div>
          </div>
          <!-- ComposeBox End -->

          <!-- sideBar -->
          <div class="row compose-sideBar">
            <div class="row sideBar-body">
              <div class="col-sm-3 col-xs-3 sideBar-avatar">
                <div class="avatar-icon">
                  <!--img src="http://shurl.esy.es/y"-->
                </div>
              </div>
              <div class="col-sm-9 col-xs-9 sideBar-main">
                <div class="row">
                  <div class="col-sm-8 col-xs-8 sideBar-name">
                    <span class="name-meta">(11) 99639-3535
                  </span>
                  </div>
                  <div class="col-sm-4 col-xs-4 pull-right sideBar-time">
                    <span class="time-meta pull-right">18:18
                  </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="row sideBar-body">
              <div class="col-sm-3 col-xs-3 sideBar-avatar">
                <div class="avatar-icon">
                  <!--img src="http://shurl.esy.es/y"-->
                </div>
              </div>
              <div class="col-sm-9 col-xs-9 sideBar-main">
                <div class="row">
                  <div class="col-sm-8 col-xs-8 sideBar-name">
                    <span class="name-meta">Ankit Jain
                  </span>
                  </div>
                  <div class="col-sm-4 col-xs-4 pull-right sideBar-time">
                    <span class="time-meta pull-right">18:18
                  </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Sidebar End -->
      </div>


      <!-- New Message Sidebar End -->

      <!-- Conversation Start -->
      <div class="col-sm-8 conversation">
        <!-- Heading -->
        <div class="row heading">
          <div class="col-sm-2 col-md-1 col-xs-3 heading-avatar">
            <div class="heading-avatar-icon">
              <!--img src="http://shurl.esy.es/y"-->
            </div>
          </div>
          <div class="col-sm-8 col-xs-7 heading-name">
            <a class="heading-name-meta">(11) 99639-3535
            </a>
            <!--span class="heading-online">Online</span-->
          </div>
          <!--div class="col-sm-1 col-xs-1  heading-dot pull-right">
            <i class="fa fa-ellipsis-v fa-2x  pull-right" aria-hidden="true"></i>
          </div-->
        </div>
        <!-- Heading End -->

        <!-- Message Box -->
        <div class="row message" id="conversation">

          <div class="row message-previous">
            <div class="col-sm-12 previous">
              <!--a onclick="previous(this)" id="ankitjain28" name="20">
              Show Previous Message!
              </a-->
            </div>
          </div>
          @foreach($messages as $item)

          <div class="row message-body">
            <div class="col-sm-12 message-main-receiver">
              <div class="{{ $item['type'] }}">
                <div class="message-text">
                    {{ $item['body'] }}
                </div>
                <span class="message-time pull-right">
                  {{ date_format(date_create($item['created_at']), 'd/m/Y @ H:i:s') }}
                </span>
              </div>
            </div>
          </div>
        </div>
        @endforeach
        <!-- Message Box End -->

        <!-- Reply Box -->
        <!--
        <div class="row reply">
          <div class="col-sm-1 col-xs-1 reply-emojis">
            <i class="fa fa-smile-o fa-2x"></i>
          </div>
          <div class="col-sm-9 col-xs-9 reply-main">
            <textarea class="form-control" rows="1" id="comment"></textarea>
          </div>
          <div class="col-sm-1 col-xs-1 reply-recording">
            <i class="fa fa-microphone fa-2x" aria-hidden="true"></i>
          </div>
          <div class="col-sm-1 col-xs-1 reply-send">
            <i class="fa fa-send fa-2x" aria-hidden="true"></i>
          </div>
        </div>
        -->
        <!-- Reply Box End -->
      </div>
      <!-- Conversation End -->
    </div>
    <!-- App One End -->
  </div>

  <!-- App End -->
@stop

@include('admin.include.assets')