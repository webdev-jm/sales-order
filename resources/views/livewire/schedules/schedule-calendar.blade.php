<div>
    <div class="card">
        {{-- <form wire:submit.prevent="filter"> --}}
            <div class="card-header">
                <h3 class="card-title">Filters</h3>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">User</label>
                            <select class="form-control" wire:model="user_id" wire:change="filter">
                                @foreach($users as $key => $user)
                                <option value="{{$key}}">{{$user}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Branch</label>
                            <select class="form-control" wire:model="branch_id" wire:change="filter">
                                @foreach($branches as $key => $branch)
                                <option value="{{$key}}">{{$branch}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

            </div>
        {{-- </form> --}}
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Calendar</h3>
        </div>
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>

    <div class="modal fade" id="event-modal">
        <div class="modal-dialog modal-lg">
            <livewire:schedules.schedule-event/>
        </div>
    </div>
    
    <div class="modal fade" id="reschedule-modal">
        <div class="modal-dialog modal-lg">
            <livewire:schedules.schedule-change/>
        </div>
    </div>

    @section('plugins.Fullcalendar', true)

    <script>
        document.addEventListener('livewire:load', function() {

            var events = @this.schedule_data;

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
                    var type = eventObj.extendedProps.type;

                    var year = date.getFullYear();
                    var month = date.getMonth() + 1;
                    var day = date.getDate();
                    var date_format = year+'-'+(month < 10 ? '0' : '')+month+'-'+(day < 10 ? '0' : '')+day;

                    if(type == 'schedule') {
                        Livewire.emit('showEvents', date_format);
                        $('#event-modal').modal('show');
                    } else if(type == 'reschedule') {
                        Livewire.emit('setDate', date_format);
                        $('#reschedule-modal').modal('show');
                    }
                },
                themeSystem: 'bootstrap',
                events: events
            });
            calendar.render();
        });
    </script>
</div>
