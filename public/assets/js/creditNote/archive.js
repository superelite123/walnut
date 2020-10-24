let invoice_table = null
var createTable = (date_range) => {
    $('#invoice_table').dataTable().fnDestroy()
    invoice_table = $('#invoice_table').DataTable({
        "processing":true,
        "serverSide":true,
        "ajax":{
            "url":"credit_notes/archives",
            "dataType":"json",
            "type":"POST",
            "data":{date_range:date_range},
        },
        "columns":
        [
            {
                "className":      'details-control',
                "orderable":      false,
                "data":           null,
                "defaultContent": '<button class="btn btn-info btn-xs"><i class="fas fa-plus"></i></button>'
            },
            { "data": "no" },
            { "data": "name" },
            { "data": "total_price" },
        ],
        "columnDefs": [
            { "orderable": false, "width": "5px", "targets": 0 },
            { "orderable": true, "width": "10px", "targets": 1 },
            { "orderable": false, "targets": 2 },
            { "orderable": false, "targets": 3 },
        ],
    })
}
$('#invoice_table tbody').on('click', 'td.details-control', function () {
    var tr = $(this).closest('tr');
    var row = invoice_table.row( tr );

    if ( row.child.isShown() ) {
        // This row is already open - close it
        row.child.hide();
        tr.removeClass('shown');
        $(this).html('<button class="btn btn-info btn-xs"><i class="fas fa-plus"></i></button>')
    }
    else {
        // Open this row
        row.child( row_details_format(row.data()) ).show();
        tr.addClass('shown');
        $(this).html('<button class="btn btn-info btn-xs"><i class="fas fa-minus"></i></button>')
    }
})

var row_details_format = (d) => {
    // `d` is the original data object for the row
    var data = d.items;
    var html = '<table class="table table-bordered  table-striped childTable" cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
    html += '<thead>'
    html += '<th style="width:10px">No</th>'
    html += '<th>SO</th>'
    html += '<th>Credit Note Value</th>'
    html += '</thead>'

    html += "<tbody>"
    for(var i = 0; i < data.length; i ++)
    {
        html += '<tr>';
        html += '<td>' + (i + 1) + '</td>';
        html += '<td>' + data[i].so + '</td>';
        html += '<td>' + data[i].total_price + '</td>';
        html += '</tr>';
    }
    if(d.pDiscount != null)
    {
        html += '<tr>'
        html += '<td colspan=5>' + d.pDiscount.note + '</td>'
        html += '<td colspan=6>' + d.pDiscount.value + '</td></tr>'
    }
    html += "</tbody></table>";
    return html;
}
$(function(){

    $("#reservation").daterangepicker({
        format: 'dd.mm.yyyy',
        startDate: s_date,
        endDate: e_date
      }).on("change", function() {
        createTable($("#reservation").val());
    })
    createTable($("#reservation").val());
})

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
