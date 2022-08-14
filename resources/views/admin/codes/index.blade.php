{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Enviar Códigos</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-sm-12 col-xs-12">
            <form id="adminSaveCodes" action="{{ route('admin.codes.action') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Dados da Planilha</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <!--div class="box-body">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="store">Qual a Loja?</label>
                                    <select class="form-control" id="store" name="store">
                                        <option value="">Escolha</option>
                                        @foreach($stores as $item)
                                            <option value="{{ $item->id }}">{{ $item->store_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div-->

                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="prize">Qual o Prêmio?</label>
                                    <select class="form-control" id="prize" name="prize">
                                        <option value="">Escolha</option>
                                        @foreach($prizes as $item)
                                            <option value="{{ $item->id }}">{{ $item->prize }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="store">Arquivo</label>
                                    <input type="file" id="codes" name="codes">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12 text-right">
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@include('admin.include.assets')