{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-sm-12 col-xs-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Cupons para validação</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-striped dataTable">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Código</th>
                                <th>CPF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($couponsToValidate as $item){ ?>
                                <tr>
                                    <td><a href="{{ url($item->invoice) }}" class="img-cupom"><img src="{{ url($item->invoice) }}" width="30" height="30" /></a></td>
                                    <td></td>
                                    <td><?php print $item->documento; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Usuário</th>
                                <th>Código</th>
                                <th>CPF</th>
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