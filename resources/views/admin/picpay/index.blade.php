{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>PicPay</h1>
@stop

@section('content')
<div class="row">
        <div class="col-sm-12 col-xs-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Cupons Sem Retorno do PicPay</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-striped dataTable">
                    <thead>
                            <tr>
                                <th>ID do Cupom</th>
                                <th>CPF</th>
                                <th>Prêmio</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($coupons as $item){ ?>
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->user->cpf }}</td>
                                    <td>{{ $item->prize->prize }}</td>
                                    <td><a href="{{ route('admin.picpay.action', ['id' => encrypt($item->id, env('APP_NAME'))]) }}">Enviar</a></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>ID do Cupom</th>
                                <th>CPF</th>
                                <th>Prêmio</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                
                </div>
            </div>
        </div>
    </div>
@stop

@include('admin.include.assets')