<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Calendar</h3>
        </div>
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>

    <script>
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left  : 'prev,next today',
                center: 'title',
                right : 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            dayMaxEvents: 4,
            eventClick: function(info) {
                var eventObj = info.event;
                var date = eventObj.start;
                var year = date.getFullYear();
                var month = date.getMonth() + 1;
                var day = date.getDate();
                var date_format = year+'-'+(month < 10 ? '0' : '')+month+'-'+(day < 10 ? '0' : '')+day;
                
                Livewire.emit('showEvents', date_format);
                $('#event-modal').modal('show');
            },
            themeSystem: 'bootstrap',
            events: @php echo json_encode($schedule_data); @endphp
        });
        calendar.render();
    </script>
</div>
