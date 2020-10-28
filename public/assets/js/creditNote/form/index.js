let items = [];
//export CSV
$("#export_btn").on('click', function(event) {
    $.ajax({
        url:'../credit_notes/archives',
        type:'post',
        data: {who:customerID},
        success:(res) => {
            convertToCSV(res.items).then(function(result){
                let filename = res.name + "'s Credit Note";
                exportCSVfile(filename,result);
            })
        },
        error:(e) => {
            $('#loadingModal').modal('hide')
            swal(e.statusText, e.responseJSON.message, "error")
        }
    })

});

var convertToCSV = (objArray) => {

    return new Promise(function(next_operation){
console.log(objArray)
        var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray
        let str = "No,SO,Credit Note Value\r\n"

        array.forEach((element,index) => {
          str += (index + 1) + ','
          str += element.so + ','
          str += element.total_price + '\r\n'
        });
        next_operation(str);
    });
}

var exportCSVfile = (filename,csv) =>{
    var exportedFilenmae = filename + '.csv' || 'export.csv';

    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    if (navigator.msSaveBlob) { // IE 10+
        navigator.msSaveBlob(blob, exportedFilenmae);
    } else {
        var link = document.createElement("a");
        if (link.download !== undefined) { // feature detection
            // Browsers that support HTML5 download attribute
            var url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", exportedFilenmae);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
}
$('.addBtn').on('click',() => {
    const item = {
        strain:parseInt($('#strain').val()),
        p_type:parseInt($('#pType').val()),
        price:parseInt($('#price').val()),
        qty:parseInt($('#qty').val()),
    }

    if(item.strain == 0 || item.pType == 0 || item.price < 0.00001 || item.qty < 1)
    {
        alert('Enter correct value');
        return false
    }
    let bDuplicated = false
    items.forEach(element => {
        if(element.strain == item.strain && element.p_type == item.p_type)
        {
            bDuplicated = true
        }
    });
    if(bDuplicated)
    {
        alert('Duplicated Item')
        return false
    }
    items.push(item)
    createTable()
})
const createTable = () => {
    let html = ''
    let total = 0
    if(items.length > 0)
    {

        items.forEach((element,index) => {
            html += '<tr>'
            html += '<td>' + (index + 1) + '</td>'
            html += '<td>' + getInventoryLabel(element) + '</td>'
            html += '<td>' + element.price + '</td>'
            html += '</tr>'

            total += element.price
        });
    }
    else
    {
        html += '<tr><td colspan=3 style="text-align:center"><h4>No Data</h4></td></tr>'
    }
    $('#tblInventory > tbody').html(html)
    $('#totalPrice').val(total)
}
$('#btnSubmit').on('click',() => {
    const postData = {
        invoice_id:orderID,
        customer_id:customerID,
        total_price:parseInt($('#totalPrice').val()),
        items:items,
    }
    if(postData.total_price < 0.0001 || postData.items.length < 1)
    {
        alert('Enter correct value')
        return false
    }
    swal({
        title: "New Credit Note",
        text: "You are about to add credit Note",
        type: "info",
        showCancelButton: true,
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function () {
        $.ajax({
            url:'../_add_credit_note',
            headers:{"content-type" : "application/json"},
            data: JSON.stringify(postData),
            type:'post',
            success:(res) => {
                swal('Thanks!', 'Credit Note is addedd Successfully', "success")
                location.reload()
            },
            error:(e) => {
                swal(e.statusText, e.responseJSON.message, "error")
            }
        })
    })

})
const getInventoryLabel = (item) =>
{
    let strainLabel = "",pTypeLabel = ""
    strains.forEach(element => {
        if(element.itemname_id == item.strain)
        {
            strainLabel = element.strain
        }
    });
    pTypes.forEach(element => {
        if(element.producttype_id == item.p_type)
        {
            pTypeLabel = element.producttype
        }
    });
    return strainLabel + ',' + pTypeLabel;
}
$(() => {
    $('.select2').select2();
    createTable()
})
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
