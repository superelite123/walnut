<div class="col-md-12 panel-close-btn">
    <a class='pull-right' style=',' href="javascript:onClose()"><i class="fas fa-times"></i>&nbsp;close</a>
</div>
<div class="col-xs-12">
<h2 class="page-header">
    {{ $invoice->company_detail->companyname }}
    <small class="pull-right">Invoice Date: {{ $invoice->date }}</small>
</h2>
</div>
<script>
    function onClose()
    {
        if(confirm('close Finish?'))
        {
            window.close()
        }
    }
</script>
