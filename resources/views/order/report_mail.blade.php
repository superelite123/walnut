<html>
    
    <body>
    <h3>Invoice {{ $invoice->number }} has been approved. Attachment below:</h3>
    --------------------------------------------------------------------------------
<br>
<br>
    <!-- Table row -->
    <!-- Table row -->
    <div class="row">
    <div class="col-xs-12 table-responsive">
        <table class="table table-responsive table-striped">
        <thead>
        <tr>
            <th>Strain</th>
            <th>Product Type</th>
            <th>Qty</th>
            <th>Units</th>
            <th>Base Price</th>
            <th>CPU</th>
            <th>Discount</th>
            <th>Discount Type</th>
            <th>Sub Total</th>
            <th>Extended</th>
            <th>Line Note</th>
            <th>Adjust Total</th>
        </tr>
        </thead>
        <tbody>
            @foreach ($invoice->itemAP as $item)
                <tr>
                        <td>{{ $item->StrainLabel }}</td>
                        <td>{{ $item->PTypeLabel }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ $item->units }}</td>
                        <td>${{ $item->unit_price }}</td>
                        <td>${{ $item->CPU }}</td>
                        <td>${{ $item->discount }}</td>
                        <td>{{ $item->DisType }}</td>
                        <td>${{ $item->BasePrice }}</td>
                        <td>${{ $item->Extended }}</td>
                        <td>{{ $item->TNote }}</td>
                        <td>${{ $item->AdjustPrice }}</td>
                </tr>
            @endforeach
        </tbody>
        </table>
    </div>
    <!-- /.col -->
    </div>
    <!-- /.row -->
    </body>
</html>