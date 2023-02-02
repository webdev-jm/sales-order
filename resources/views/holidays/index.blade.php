@extends('adminlte::page')

@section('title')
    Holidays
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Holidays</h1>
    </div>
    <div class="col-md-6 text-right">
    </div>
</div>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-body p-0">
        <div id="calendar-container"></div>
    </div>
</div>

<div class="modal fade" id="modal-add">
    <div class="modal-dialog">
        <livewire:holidays.holiday-add/>
    </div>
</div>
@endsection

@section('plugins.Fullcalendar', true);

@section('js')
<script>
    $(function() {
        var holidays = @php echo json_encode($calendar_data); @endphp;
        // calendar
        var calendarEl = document.getElementById('calendar-container');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left  : 'prev,next',
                center: 'title',
                right : 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            dateClick: function(info) {
                var date = info.dateStr;
                Livewire.emit('setHolidayAdd', date);
                $('#modal-add').modal('show');
            },
            themeSystem: 'bootstrap',
            events: holidays,
            dayRender: function(date, cell) {
                // Get all events
                var events = $('#calendar').fullCalendar('clientEvents').length ? $('#calendar').fullCalendar('clientEvents') : holidays;

                // Start of a day timestamp
                var dateTimestamp = date.hour(0).minutes(0);
                var recurringEvents = new Array();
                
                // find all events with yearly repeating flag, having id, repeating at that day few years ago  
                var yearlyEvents = events.filter(function (event) {
                return event.repeat === 1 &&
                    event.id &&
                    moment(event.start).hour(0).minutes(0).diff(dateTimestamp, 'years', true) % 1 == 0
                });

                recurringEvents = yearlyEvents;

                $.each(recurringEvents, function(key, event) {
                    var timeStart = moment(event.start);

                    // Refething event fields for event rendering 
                    var eventData = {
                        id: event.id,
                        allDay: event.allDay,
                        title: event.title,
                        description: event.description,
                        start: date.hour(timeStart.hour()).minutes(timeStart.minutes()).format("YYYY-MM-DD"),
                        end: event.end ? event.end.format("YYYY-MM-DD") : "",
                        url: event.url,
                        className: 'scheduler_basic_event',
                        repeat: event.repeat
                    };
                            
                    // Removing events to avoid duplication
                    $('#calendar').fullCalendar( 'removeEvents', function (event) {
                        return eventData.id === event.id &&
                        moment(event.start).isSame(date, 'day');      
                    });
                    // Render event
                    $('#calendar').fullCalendar('renderEvent', eventData, true);

                });
            }
        });
        calendar.render();
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection