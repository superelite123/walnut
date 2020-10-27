<table class="table table-bordered">
    <tr>
      <th style="width:50%">Total Base Price:</th>
      <td>${{ $invoice->TotalInfo['base_price'] }}</td>
    </tr>
    <tr>
      <th style="width:50%">Discount Amount:</th>
      <td>${{ $invoice->TotalInfo['discount'] }}</td>
    </tr>
    <tr>
        <th style="width:50%">Total Extra Discount:</th>
        <td>${{ $invoice->TotalInfo['e_discount'] }}</td>
    </tr>
    <tr>
      <th style="width:50%">Promotion Value:</th>
      <td>${{ $invoice->TotalInfo['promotion'] }}</td>
    </tr>
    <tr>
      <th style="width:50%">Credit Note:</th>
      <td>${{ $invoice->TotalInfo['credit_amount'] }}</td>
    </tr>
    <tr>
      <th style="width:50%">Sub Total:</th>
      <td>${{ $invoice->TotalInfo['extended'] }}</td>
    </tr>
    <tr>
      <th>CA Excise Tax Based On Total Base Price @27%:</th>
      <td>${{ $invoice->TotalInfo['tax'] }}</td>
    </tr>
    <tr>
      <th>Total Due:</th>
      <td>${{ $invoice->TotalInfo['adjust_price'] }}</td>
    </tr>
</table>
