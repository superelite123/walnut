@extends('adminlte::page')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title', 'Walnut to Deliver')
@section('css')
  <link rel="stylesheet" href="{{ asset('assets/css/order/index.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/inventory/import.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/css/sweetalert.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/dropzone/normalize.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/dropzone/component.css') }}">
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
            <div class="alert">

            </div>
            <form action="importInventory" method="POST" enctype="multipart/form-data">
                @csrf
            <div class="row">
                <div class="col-md-12 file-upload-content">
                    <input type="file" accept=".csv" name="inventoryFile" id="file-1" class="inputfile inputfile-1" />
                    <label for="file-1"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg> <span>Choose a file&hellip;</span></label>
                </div>
                <div class="col-md-12" style='text-align:center;'>
                    <button id="df" class="btn btn-info btn-lg"><i class="fas fa-save"></i>&nbsp;&nbsp;&nbsp;Import</button>
                </div>
                <input type="submit" value="asd">
            </div>
            </form>
        </div>
        <!-- /.box-body -->
    </div>
@stop
@section('js')
  <script type="text/javascript" src="{{ asset('assets/component/js/sweetalert.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/component/js/growl/jquery.growl.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/component/dropzone/custom-file-input.js') }}"></script>
@stop
<script>
    $("#df").click(() => {
        console.log($('#file-1').val())
    })
</script>
