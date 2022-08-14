{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-sm-6 col-xs-12">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-bookmark-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Usuários</span>
                    <span class="info-box-number">{{ $users['count'] }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $users['average'] }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ $users['average'] }}% com cupom
                    </span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </div><!-- /.col -->
        <div class="col-sm-6 col-xs-12">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-thumbs-o-up"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Cupons via Site</span>
                    <span class="info-box-number">{{ $siteCoupon['count'] }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $siteCoupon['percentageTotal'] }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ $siteCoupon['percentageTotal'] }}% Validados
                    </span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </div><!-- /.col -->
        <div class="col-sm-6 col-xs-12">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Números da Sorte</span>
                    <span class="info-box-number">{{ $luckyNumbers['count'] }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $luckyNumbers['average']}}%"></div>
                    </div>
                    <span class="progress-description">{{ $luckyNumbers['average']}}% de usuários com números da sorte</span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </div><!-- /.col -->
        <div class="col-sm-6 col-xs-12">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-comments-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Cupons via Whatsapp</span>
                    <span class="info-box-number">{{ $whatsappCoupon['count'] }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $whatsappCoupon['abandoned'] }}%"></div>
                    </div>
                    <span class="progress-description">{{ $whatsappCoupon['abandoned'] }}% abandonados</span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </div><!-- /.col -->
    </div>

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
                                <th>Hash do Prêmio</th>
                                <th>CPF</th>
                                <th>E-Mail</th>
                                <th>Primeiro Cupom</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($couponsToValidate as $item){ ?>
                                <tr>
                                    <td><a href="{{ route('admin.cupom.validar', ['id' => $item->id]) }}">{{ $item->user->name }}</a></td>
                                    <td><?php print $item->instant_prize_hash; ?></td>
                                    <td><?php print $item->user->cpf; ?></td>
                                    <td><?php print $item->user->email; ?></td>
                                    <td><?php print ( $item->cna_mini_curso == 1 ) ? 'Primeiro Cupom' : 'Demais Cupons'; ?></td>
                                    <td><?php print dateBR($item->created_at, false); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Usuário</th>
                                <th>Código</th>
                                <th>CPF</th>
                                <th>E-Mail</th>
                                <th>Primeiro Cupom</th>
                                <th>Data</th>
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