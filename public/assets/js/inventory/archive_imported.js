console.log(harvests_inventory)
let tbl_inventory = $('#tbl_inventory').DataTable({
    data:harvests_inventory,
    columns:
        [
            {
                "className":      'details-control',
                "orderable":      false,
                "data":           null,
                "defaultContent": '<button class="btn btn-info btn-xs flat"><i class="glyphicon glyphicon-plus"></i></button>'
            },
            { "data": "batch_id" },
            { "data": "strain_label" },
            { "data": "type" },
            { "data": "harvested_date" },
            { "data": "btn_approve" },
        ],
});
$('#tbl_inventory tbody').on('click', 'td.details-control', function () {
    var tr = $(this).closest('tr');
    var row = tbl_inventory.row( tr );

    if ( row.child.isShown() ) {
        // This row is already open - close it
        row.child.hide();
        tr.removeClass('shown');
        $(this).html('<button class="btn btn-info btn-xs btn-edit flat"><i class="fas fa-plus"></i></button>')
    }
    else {
        // Open this row
        row.child( row_details_format(row.data()) ).show();
        tr.addClass('shown');
        $(this).html('<button class="btn btn-info btn-xs btn-edit flat"><i class="fas fa-minus"></i></button>')
    }
})
var row_details_format = (d) => 
{
    // `d` is the original data object for the row
    var data = d.inventory
    var html = '<table class="table table-bordered" cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
    html += '<thead>';
    html += '<th>No</th>'
    html += '<th>Metrc Tag</th>';
    html += '<th>Weight</th>';
    html += '<th>Inventory</th>';
    html += '</thead>';

    html += "<tbody>";
    for(var i = 0; i < data.length; i ++)
    {
        html += '<tr>';
        html += '<td>' + (i + 1) + '</td>';
        html += '<td>' + data[i].metrc_tag + '</td>';
        html += '<td>' + data[i].weight + '</td>';
        html += '<td>' + data[i].i_type_label + '</td>';
        html += '</tr>';
    }
    return html
}
$('#tbl_inventory tbody').on('click', '.btn_approve', function () {
    const tr = $(this).closest('tr')
    const row = tbl_inventory.row( tr )
    const harvest_id = row.data().id
    swal({
        title: "Confirm",
        text: "You are about to approve Inventory",
        type: "info",
        showCancelButton: true,
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function () {
        $.ajax({
            url:'_approve_imported',
            data:'id=' + harvest_id,
            type:'post',
            success:(res) => {
                swal("Successfully imported", "", "success")
            },
            error:(e) => {
                swal(e.statusText, e.responseJSON.message, "error")
            }
        })
    })
})
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});