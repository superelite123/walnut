@extends('adminlte::page')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title', 'Walnut to Deliver')
@section('css')
  <link rel="stylesheet" href="{{ asset('assets/css/order/index.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/css/sweetalert.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/css/growl/jquery.growl.css') }}">
@stop
@section('content_header')
@stop

@section('content')
    <!--start edit form-->
    <div class="box box-info">
        <div class="box-header with-border">
        <h1>Inventory Import</h1>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
        </div>
        </div>
        <!-- /.box-header -->

        <div class="box-body">
            <div class="row">

            </div>
        </div>
        <!-- /.box-body -->
    </div>
@stop
@section('js')
  <script type="text/javascript" src="{{ asset('assets/component/js/sweetalert.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/component/js/growl/jquery.growl.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/js/inventory/combine_panel.js') }}"></script>
@stop
