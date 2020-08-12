<div class="col-md-12">
    <div class="col-md-4">
        <address>
            <strong>{{ $invoice->company_detail->companyname }}<br>
            {{ $invoice->company_detail-> address1}},<br>
            {{ $invoice->company_detail->city }}, {{ $invoice->company_detail->state }} {{ $invoice->company_detail->zip }}</strong><br>
            Phone: <strong>{{ $invoice->company_detail->phone }}</strong><br>
            <strong>{{ $invoice->salesperson->firstname.' '.$invoice->salesperson->lastname }}.</strong><br>
            Rep Phone: {{ $invoice->salesperson->telephone }}<br>
            Cultivation License: <strong>{{ $invoice->company_detail->license }}</strong><br>
          </address>
    </div>
    <div class="col-md-4">
        <address>
        @if ($invoice->customer != null)
            Client:&nbsp;<strong>{{ $invoice->CName }}</strong><br>
            Address:<strong>&nbsp;{{ $invoice->customer->address1 }}<br>
            {{ $invoice->customer->city }}, {{ $invoice->customer->state_name->name }} {{ $invoice->customer->zip }}</strong><br>
            Phone:&nbsp;<strong>{{ $invoice->customer->companyphone }}<br></strong>
            Email:&nbsp;<strong>{{ $invoice->customer->companyemail }}<br></strong>
            License:&nbsp;<strong>{{ $invoice->customer->licensenumber }}</strong>
        @endif
        </address>
    </div>
    <div class="col-md-4">
        Distributor/Transporter:
        @if ($invoice->distuributor != null)
        <address>
            <strong>{{ $invoice->distuributor->companyname }} <br>
            {{ $invoice->distuributor->address1 }}, {{ $invoice->distuributor->address2 }}<br>
            {{ $invoice->distuributor->city }}, {{ $invoice->distuributor->state_name!=null?$invoice->distuributor->state_name->name:'No State' }} {{ $invoice->distuributor->zipcode }}  <br></strong>
            Phone: <strong>{{ $invoice->distuributor->phone }}</strong> <br>
            Email: <strong>{{ $invoice->distuributor->email }}</strong> <br>
            License: <strong>{{ $invoice->distuributor->license }}</strong>
        </address>
        @else
            <p>{{ Config::get('constants.order.no_distributor') }}</p>
        @endif
      </div>
</div>
<div class="col-md-12">
    <div class="col-md-4">
        Invoice: <b>{{ $invoice->number }}</b><br>
        Terms: 
        @if ($invoice->customer != null)
        <b>{{ $invoice->customer->term != null?$invoice->customer->term->term:'No Term' }}</b><br>    
        <b>Account:</b> {{ $invoice->customer->client_id }}
        @endif
    </div>
    <div class="col-md-4">
        <strong>Note:</strong> <br>{{ $invoice->note }}
    </div>
</div>