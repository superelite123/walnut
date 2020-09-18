$("#export_btn").on('click', function() {
    console.log('hi')
    var array = typeof calendarData != 'object' ? JSON.parse(calendarData) : calendarData;
    var str     = "Invoice,Delivery Date,Transported Via,Time,Customer,Amount\r\n"

    for (var i = 0; i < array.length; i++) {

        var line1 = ''
        line1 += array[i].number + ','
        line1 += array[i].dDate + ','
        line1 += array[i].deliveryer + ','
        line1 += array[i].time + ','
        line1 += '\"' + array[i].cName + '\",'
        line1 += array[i].amount + '\r\n'
        str += line1
    }
    const filename = 'Scheduled Deliveries'
    exportCSVfile(filename,str)
});

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
$(function(){
    /* initialize the calendar
    -----------------------------------------------------------------*/
    $('#calendar').fullCalendar({
        header    : {
            left  : 'prev,next today',
            center: 'title',
            right : 'month,agendaWeek,agendaDay'
        },
        buttonText: {
            today: 'today',
            month: 'month',
            week : 'week',
            day  : 'day'
        },
        //Random default events
        events    : calendarData,
        editable  : false,
        height:500,
        eventRender: function(event, element) {
            const title =   event.title1 + '<br>' +
                            event.title2 + '<br>' +
                            event.title3 + '<br>' +
                            event.title4 + '<br>'
            element.find(".fc-title").prepend(title);
        },
        eventClick:  function(event, jsEvent, view) {
            window.open('view/' + event.id + '/0');
        },
    })
    $('#invoice_table').DataTable()
    $("body").addClass('fixed')
})
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
