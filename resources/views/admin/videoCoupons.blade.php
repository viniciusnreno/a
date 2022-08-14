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
                    <h3 class="box-title">Cupons com Vídeo</h3>

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
                                <th>URL do Vídeo</th>
                                <th>CPF</th>
                                <th>E-Mail</th>
                                <th>Data</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($coupons as $item){ ?>
                                <tr>
                                    <td><a href="{{ route('admin.cupom.validar', ['id' => $item->id]) }}">{{ $item->user->name }}</a></td>
                                    <td><?php print $item->video_url; ?></td>
                                    <td><?php print $item->user->cpf; ?></td>
                                    <td><?php print $item->user->email; ?></td>
                                    <td><?php print dateBR($item->created_at, false); ?></td>
                                    @if($item->status == 1)
                                        <td class="text-success">Validado</td>
                                    @elseif($item->status === 0)
                                        <td class="text-danger">Inválido</td>
                                    @else
                                        <td class="text-info">Em Validação</td>
                                    @endif

                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Usuário</th>
                                <th>URL do Vídeo</th>
                                <th>CPF</th>
                                <th>E-Mail</th>
                                <th>Data</th>
                                <th>Status</th>
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