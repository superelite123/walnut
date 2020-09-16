
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
