@extends('adminlte::page')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title', 'Walnut to Deliver')
@section('css')
  <link rel="stylesheet" href="{{ asset('assets/css/order/index.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/css/daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/css/sweetalert.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/css/datatable-fixedWidthColumn.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/css/growl/jquery.growl.css') }}">
@stop
@section('content_header')
@stop

@section('content')
    <!--start edit form-->
    <div class="box box-info">
        <div class="box-header with-border">
        <h1>Awaing Approval</h1>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
        </div>
        </div>
        <!-- /.box-header -->
        
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12">
                    <table class="table table-bordered nowrap" id="tbl_inventory">
                        <thead>
                            <th></th>
                            <th>Harvest Batch ID</th>
                            <th>Strain</th>
                            <th>Type</th>
                            <th>Harvested Date</th>
                            <th></th>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>

    @include('layouts.modal_alert')
@stop
@section('js')
  <script type="text/javascript" src="{{ asset('assets/component/js/sweetalert.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/component/js/growl/jquery.growl.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/js/inventory/archive_imported.js') }}"></script>
@stop   

<script>
    let harvests_inventory = JSON.parse('{!! json_encode($data) !!}');
    harvests_inventory.forEach(element => {
            element.btn_approve = '<button class="btn btn-info btn-xs btn_approve"><i class="fas fa-envelope-square">&nbsp;</i>Approve</button>'
        });
</script>
