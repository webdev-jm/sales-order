@extends('adminlte::page')

@section('title')
    Schedules
@endsection

@section('css')
<style>
    .fc-daygrid-event {
        cursor: pointer;
    }

    /* @media (min-width: 768px) {
        .fc-event-time, .fc-event-title {
            padding: 0 1px;
            white-space: normal;
        }
    } */

    .fc-event-time, .fc-event-title {
        padding: 0 1px;
        white-space: normal;
    }

    .schedule {
        color: rgb(37, 184, 181);
    }
    .reschedule {
        color: rgb(243, 114, 6);
    }
    .delete-request {
        color: rgb(201, 5, 24);
    }
    .schedule-request {
        color: rgb(50, 168, 82);
    }
    .deviation-request {
        color: rgb(14, 22, 173);
    }

    .w100 {
        width: 100px !important;
    }
    
    .trip-icon {
        font-size: 60px !important;
        margin-bottom: 0;
    }
    .middle {
        line-height: 100% !important;
    }
    .w-100 {
        width: 100% !important;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Schedules</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('schedule.deviations')}}" class="btn btn-warning"><i class="fa fa-calendar mr-1"></i>Deviations</a>
        @can('schedule list')
        <a href="{{route('schedule.list')}}" class="btn btn-primary"><i class="fa fa-list mr-1"></i>Schedule Requests</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['schedule.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

{!! Form::open(['method' => 'POST', 'route' => ['schedule.store'], 'id' => 'add_schedule']) !!}
{!! Form::close() !!}

<div class="row">

    {{-- Filter --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter</h3>
            </div>
            <div class="card-header">
                
                <div class="row">

                    {{-- account --}}
                    <div class="col-lg-3">
                        <div class="form-group">
                            {!! Form::label('account_id', 'Account') !!}
                            {!! Form::select('account_id', $accounts, $account_id, ['class' => 'form-control', 'form' => 'search_form']) !!}
                        </div>
                    </div>

                    {{-- user --}}
                    <div class="col-lg-3">
                        <div class="form-group">
                            {!! Form::label('user_id', 'User') !!}
                            {!! Form::select('user_id', $users, $user_id, ['class' => 'form-control', 'form' => 'search_form', 'id' => 'user_filter']) !!}
                        </div>
                    </div>

                </div>

            </div>
            <div class="card-footer text-right">
                {!! Form::submit('Filter', ['class' => 'btn btn-primary', 'form' => 'search_form']) !!}
            </div>
        </div>
    </div>

    {{-- color codes --}}
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Color Codes</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-2 col-md-3">
                        <i class="fa fa-square schedule mr-1"></i>
                        <span>Schedules</span>
                    </div>
                    {{-- <div class="col-lg-2 col-md-3">
                        <i class="fa fa-square reschedule"></i>
                        <span>Reschedule Request</span>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <i class="fa fa-square delete-request"></i>
                        <span>Delete Request</span>
                    </div> --}}
                    <div class="col-lg-2 col-md-3">
                        <i class="fa fa-square schedule-request mr-1"></i>
                        <span>Schedule Request</span>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <i class="fa fa-square deviation-request mr-1"></i>
                        <span>Deviation Request</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Calendar --}}
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Calendar</h3>
                <div class="card-tools">
                    <button class="btn btn-success" id="btn-request-schedule" type="button"><i class="fas fa-calendar-check mr-1"></i>Schedule Request</button>
                </div>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-upload">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Schedule</h4>
            </div>
            <div class="modal-body">
                {!! Form::open(['method' => 'POST', 'route' => ['schedule.upload'], 'id' => 'upload_form', 'enctype' => 'multipart/form-data']) !!}
                {!! Form::close() !!}

                <label>Columns are:</label>
                <ol>
                    <li><b>User Name</b> - Required</li>
                    <li><b>Branch Code</b> - Required</li>
                    <li><b>Date</b> - Required, Date format can be YYYY-MM-DD or DD/MM/YYYY</li>
                </ol>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="custom-file">
                                {!! Form::file('upload_file', ['class' => 'custom-file-input'.($errors->has('upload_file') ? ' is-invalid' : ''), 'form' => 'upload_form', 'accept' => '.xlsx, .xls, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']) !!}
                                {!! Form::label('upload_file', 'Upload File', ['class' => 'custom-file-label']) !!}
                            </div>
                            <p class="text-danger">{{$errors->first('upload_file')}}</p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                {!! Form::submit('Upload', ['class' => 'btn btn-primary', 'form' => 'upload_form']) !!}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loadingModal" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog" aria-labelledby="loadingModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center" id="exampleModalLongTitle">UPLOADING......</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <i class="fa fa-spinner fa-spin fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
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

<div class="modal fade" id="delete-modal">
    <div class="modal-dialog modal-lg">
        <livewire:schedules.schedule-delete/>
    </div>
</div>

<div class="modal fade" id="request-modal">
    <div class="modal-dialog modal-lg">
        <livewire:schedules.schedule-request/>
    </div>
</div>


<div class="modal fade" id="add-modal">
    <div class="modal-dialog">
        <livewire:schedules.schedule-add/>
    </div>
</div>

<div class="modal fade" id="deviation-modal">
    <div class="modal-dialog modal-xl">
        <livewire:schedules.schedule-deviation/>
    </div>
</div>

<div class="modal fade" id="deviation-approval-modal">
    <div class="modal-dialog modal-xl">
        <livewire:schedules.schedule-deviation-approval/>
    </div>
</div>

@endsection

@section('plugins.Fullcalendar', true)

@section('js')
<script>
    $(function () {

        $('#btn-upload').on('click', function(e){
            e.preventDefault();
            $('#modal-upload').modal('show');
        });

        $('#upload_form').on('submit', function() {
            $('#modal-upload').modal('hide');
            $('#loadingModal').modal('show');
        });

        $('#btn-request-schedule').on('click', function(e) {
            e.preventDefault();
            $('#add-modal').modal('show');
        });

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
                var id = eventObj.id;

                var year = date.getFullYear();
                var month = date.getMonth() + 1;
                var day = date.getDate();
                var date_format = year+'-'+(month < 10 ? '0' : '')+month+'-'+(day < 10 ? '0' : '')+day;

                if(type == 'schedule') {
                    Livewire.emit('showEvents', date_format, id);
                    $('#event-modal').modal('show');
                } else if(type == 'reschedule') {
                    Livewire.emit('setDate', date_format, id);
                    $('#reschedule-modal').modal('show');
                } else if(type == 'delete') {
                    Livewire.emit('getDate', date_format, id);
                    $('#delete-modal').modal('show');
                } else if(type == 'request') {
                    Livewire.emit('setRequestDate', date_format, id);
                    $('#request-modal').modal('show');
                } else if(type == 'deviation') {
                    Livewire.emit('setDeviationApproval', id);
                    $('#deviation-approval-modal').modal('show');
                }
                
            },
            dateClick: function(info) {
                var date = info.dateStr;
                Livewire.emit('setDeviation', date);
                $('#deviation-modal').modal('show');
            },
            eventContent: function(arg) {
                return {
                    html: '<div class="fc-event-main-frame">'+
                            '<div class="fc-event-title-container">'+
                                '<div class="fc-event-title fc-sticky">'+
                                    (arg.event.extendedProps.icon ? '<i class="fa ' + arg.event.extendedProps.icon + ' text-lime mx-1"></i> '+arg.event.title : arg.event.title) +
                                '</div>'+
                            '</div>'+
                        '</div>' 
                }
            },
            themeSystem: 'bootstrap',
            events: @php echo json_encode($schedule_data); @endphp
        });
        calendar.render();
    });

    // select options
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection