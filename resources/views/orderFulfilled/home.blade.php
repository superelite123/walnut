@extends('adminlte::page')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title', 'Walnut to Deliver')
@section('css')
  <link rel="stylesheet" href="{{ asset('assets/css/order/index.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/css/daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/css/growl/jquery.growl.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/css/sweetalert.css') }}">
@stop
@section('content_header')
@stop

@section('content')
    <!--start edit form-->
<div class="box box-info">
    <div class="box-header with-border">
      <h1>Walnut to Deliver</h1>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
      </div>
    </div>
    <!-- /.box-header -->

    <div class="box-body">
        <div class="box-body">
            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <label>Order Period:</label>

                        <div class="input-group">
                            <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                            </div>
                            <input type="text" class="form-control pull-right" id="reservation">
                        </div>
                        <!-- /.input group -->
                    </div>
                </div>
                <div class="col-xs-3"></div>
                <div class="col-xs-3">
                    <button class="btn btn-info pull-right"  style="margin-top:1.5em" id="export_btn" class="export"><i class="fa fa-download"></i>&nbsp;Export CSV</button>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <table class="table table-bordered" id="invoice_table" style='width:100%'>
                        <thead>
                            <th></th>
                            <th>No</th>
                            <th>Sales Order</th>
                            <th>Invoice</th>
                            <th>Sales Rep</th>
                            <th>Customer</th>
                            <th>WB + Excise Total</th>
                            <th>Date</th>
                            <th>Distributor</th>
                            <th>Manifest Status</th>
                            <th>Metrc Manifest</th>
                            <th>COA in <i class="fa fa-envelope"></i></th>
                            <th>Metrc Ready</th>
                            <th>Actions</th>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="row">
                <h2>Rejected Orders</h2>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <table class="table table-bordered" id="reject_table" style='width:100%'>
                        <thead>
                            <th></th>
                            <th>No</th>
                            <th>Sales Order</th>
                            <th>Customer</th>
                            <th>WB + Excise Total</th>
                            <th>Date</th>
                            <th>Reject Type</th>
                            <th>Distributor</th>
                            <th>Actions</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<div class="modal fade" id="modal_missing_coa">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">You are missing some COAs at <span style='color:#00c0ef' id='coa_inv'></span> </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <p id='missed_coas' style='color:#d73925'></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info" data-dismiss="modal">Ok</button>
              </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-email-confirm">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span></button>
                <span class='modal-title i-num'>s</span>:
                <h4 class="modal-title">You are going to send email to client</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-3">
                            <p class='modal-stick'>client:</p>
                        </div>
                        <div class="col-md-4">
                            <p class='modal-stick-content' id='modal-client-name'></p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-3">
                            <p class='modal-stick'>sales person:</p>
                        </div>
                        <div class="col-md-4">
                            <p class='modal-stick-content' id='modal-sales-name'></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-4 m-foot-div">
                        <button class="btn btn-info m-foot-btn" onclick='_submit_email(1)'>
                            <i class="fas fa-user-tie"></i>Sales Rep
                        </button>
                    </div>
                    <div class="col-md-4 m-foot-div">
                        <button class="btn bg-navy m-foot-btn" onclick='_submit_email(2)'>
                            <i class="fas fa-users"></i>Customer
                        </button>
                    </div>
                    <div class="col-md-4 m-foot-div">
                        <button class="btn bg-olive m-foot-btn" id='modal-email-both'  onclick='_submit_email(3)'>
                            <i class="fas fa-user-tie"></i>&nbsp;<i class="fas fa-users"></i>&nbsp;Both
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id='loadingModal' id="modal-email-confirm">
    <div class="modal-dialog">
        <div class="modal-content"  style="height:150px">
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="col-md-4"></div>
                    <div class="col-md-4">
                        <img src="{{ asset('assets/loading1.gif') }}" style="width:100px;height:100px">
                    </div>
                    <div class="col-md-4"></div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-4"></div>
                    <div class="col-md-4">
                        <p>Loading CSV Data...</p>
                    </div>
                    <div class="col-md-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@include('footer')
<script>
    let distributors = {!! json_encode($distributors) !!}
    let metrc_manifests   = {!! json_encode($metrc_manifests) !!}
</script>
@section('js')
    <script type="text/javascript" src="{{ asset('assets/component/js/sweetalert.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/component/js/daterangepicker/moment.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/component/js/growl/jquery.growl.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/component/js/daterangepicker/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/harvest/table2csv.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/ajax_loader.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/orderFulfilled/home_reject.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/orderFulfilled/home.js') }}"></script>
@stop
