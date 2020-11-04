@extends('adminlte::page')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title', 'New Credit Note')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/component/css/bootstrap-tagsinput.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/creditNote/form.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/component/css/datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/component/css/sweetalert.css') }}">
@stop
@section('content')
<div class="box box-info main-panel">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fas fa-file-invoice"></i>&nbsp;&nbsp;&nbsp;Credit Note {{ $order->number }}</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
        </div>
    </div>
    <!-- /.box-header -->

    <div class="box-body" style="">
        <!--Customer Part-->
        <div class="row head-row">
            <div class="col-md-4">
                <label for="">Customer:</label>
                <span>{{ $order->CName }}</span>
            </div>
            <div class="col-md-2" style="display:flex;justify-content:center">
                <button class="btn btn-info pull-right"  id="export_btn" class="export"><i class="fa fa-download"></i>&nbsp;Export CSV</button>
            </div>
            <div class="col-md-3">
                <label for="">Term:</label>
                <span>{{ $order->TermLabel }}</span>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Total:</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fas fa-comment-dollar"></i>
                        </div>
                        <input type="number" class="form-control" id="totalPrice" name="totalPrice" value="0">
                    </div>
                    <!-- /.input group -->
                </div>
            </div>
        </div>
        <!--./Customer Part-->
        <!--Order Part-->
        <div class="row head-row">
            <div class="col-md-3">
                <label for="">Sales Person:</label>
                <span>{{ $order->SalesPersonName }}</span>
            </div>
            <div class="col-md-3">
                <label for="">Sales Order:</label>
                <span>{{ $order->number }}</span>
            </div>
            <div class="col-md-3">
                <label for="">Creation Date:</label>
                <span>{{ $order->date }}</span>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Add Credit Reason:</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <select class="form-control select2" style="width: 100%;" name="reason_note" id="reason_note">
                            <option value="0" disabled selected>--Select Reason--</option>
                            @foreach($reasons as $reason)
                                <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- /.input group -->
                </div>
                <!-- /.form-group -->
            </div>
        </div>
        <div class="row">
            <!--Strain-->
            <div class="col-md-4">
                <div class="form-group">
                <label>Strain</label>
                <div class="input-group">
                    <div class="input-group-addon">
                    <i class="fas fa-sliders-h"></i>
                    </div>
                    <select class="form-control select2" style="width: 100%;" name="strain" id="strain">
                    <option value="0" selected>--Select Strain--</option>
                    @foreach($strains as $strain)
                        <option value="{{ $strain->itemname_id }}">{{ $strain->strain }}</option>
                    @endforeach
                    </select>
                </div>
                <!-- /.input group -->
                <span class="error"><p id="strain_error" style='color:red'></p></span>
                </div>
            </div>
            <!--./Strain-->
            <!--Type-->
            <div class="col-md-4">
                <div class="form-group">
                <label>Product Type</label>
                <div class="input-group">
                    <div class="input-group-addon">
                    <i class="fas fa-sliders-h"></i>
                    </div>
                    <select class="form-control select2" style="width: 100%;" id="pType">
                    <option value="0" selected>--Select Product Type--</option>
                    @foreach($producttypes as $producttype)
                        <option value="{{ $producttype->producttype_id }}">{{ $producttype->producttype}}</option>
                    @endforeach
                    </select>
                </div>
                <!-- /.input group -->
                <span class="error"><p id="p_type_error" style='color:red'></p></span>
                </div>


            </div>
            <!--./Type-->
        </div>
        <div class="row">
            <!--Qty-->
            <div class="col-md-2">
                <div class="form-group">
                    <label>Qty:</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fas fa-bars"></i>
                        </div>
                        <input type="number" class="form-control" id="qty" name="qty" value="1" min=1>
                    </div>
                    <!-- /.input group -->
                </div>
            </div>
            <!--Qty-->
            <!--Price-->
            <div class="col-md-2">
                <div class="form-group">
                    <label>Price:</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fas fa-comment-dollar"></i>
                        </div>
                        <input type="number" class="form-control" id="price" name="price" value="0">
                    </div>
                    <!-- /.input group -->
                </div>
            </div>
            <!--./Price-->
            <!--/Add Item Button-->
            <div class="col-md-2">
                <button class="btn btn-info addBtn"><i class="fa fa-fw fa-plus"></i>&nbsp;Add Row</button>
            </div>
            <!--./Add Item Button-->
        </div>
        <!--Added Table-->
        <div class="row">
            <div class="col-md-12">
                <table class='table table-bordered' id="tblInventory">
                    <thead>
                        <th>No</th>
                        <th>Item</th>
                        <th>Price</th>
                        <th></th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <!--./Added Table-->
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-success btn-lg pull-right btnSubmit" id="btnSubmit"><i class="fa fa-upload"></i>&nbspADD Credit Note</button>
            </div>
        </div>
    </div>
</div>

@stop
@include('footer')
<script>
    const strains = JSON.parse('<?php echo json_encode($strains)?>')
    const pTypes = JSON.parse('<?php echo json_encode($producttypes)?>')
    const orderID = '<?php echo $order->id;?>'
    const customerID = '<?php echo $order->customer_id;?>'
</script>
@section('js')
  <script type="text/javascript" src="{{ asset('assets/component/js/sweetalert.js') }}"></script>
  <script type="module" src="{{ asset('assets/js/creditNote/form/index.js') }}"></script>
@stop
