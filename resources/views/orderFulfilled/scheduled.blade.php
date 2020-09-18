@extends('adminlte::page')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title', 'Payment Verification')
@section('css')
  <link rel="stylesheet" href="{{ asset('assets/css/order/index.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/css/growl/jquery.growl.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/fullcalendar/fullcalendar.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/component/fullcalendar/fullcalendar.print.min.css') }}" media="print">
  <link rel="stylesheet" href="{{ asset('assets/component/css/sweetalert.css') }}">
@stop
@section('content')
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class='box-title'>Scheduled Deliveries</h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
        </div>
    </div>
    <!-- /.box-header -->

    <div class="box-body" style='margin-bottom:0px'>
        <div class="row">
            <div class="col-md-12">
            <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!--TablePart-->
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class='box-title'>Invoices</h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="row" style="margin-bottom:30px;">
            <div class="col-md-12">
                <button class="btn btn-info pull-right"  style="margin-top:1.5em" id="export_btn" class="export"><i class="fa fa-download"></i>&nbsp;Export CSV</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class='table table-bordered nowrap' id='invoice_table'>
                <thead>
                    <th>No</th>
                    <th>Invoice</th>
                    <th>Delivery Date</th>
                    <th>Transported Via</th>
                    <th>Time</th>
                    <th>Customer</th>
                    <th>Amount</th>
                </thead>
                <tbody>
                    @foreach ($cData as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $item['number'] }}</td>
                            <td>{{ $item['dDate'] }}</td>
                            <td>{{ $item['deliveryer'] }}</td>
                            <td>{{ $item['time'] }}</td>
                            <td>{{ $item['cName'] }}</td>
                            <td>{{ $item['amount'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
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
    </div>
    <!--/.table row-->
</div>
@stop
@include('footer')
<script>
  let calendarData  = {!! json_encode($cData) !!}
</script>
@section('js')
  <script type="text/javascript" src="{{ asset('assets/component/js/sweetalert.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/component/js/growl/jquery.growl.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/component/js/daterangepicker/moment.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/component/fullcalendar/fullcalendar.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/js/orderFulfilled/scheduled.js') }}"></script>
@stop
