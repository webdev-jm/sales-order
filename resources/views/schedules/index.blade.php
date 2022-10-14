@extends('adminlte::page')

@section('title')
    Schedules
@endsection

@section('css')
<style>
    .fc-daygrid-event {
        cursor: pointer;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Schedules</h1>
    </div>
    <div class="col-md-6 text-right">
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
    
    <div class="col-lg-4">

        @can('schedule create')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Schedule</h3>
                    <div class="card-tools">
                        <button class="btn btn-success btn-block" id="btn-upload"><i class="fa fa-upload"></i> Upload</button>
                    </div>
                </div>
                <div class="card-body">
                    
                    <div class="row">

                        <div class="col-lg-6">
                            <div class="form-group">
                                {!! Form::label('user_id', 'User') !!}
                                {!! Form::select('user_id', [], null, ['class' => 'form-control'.($errors->has('user_id') ? ' is-invalid' : ''), 'form' => 'add_schedule']) !!}
                                <p class="text-danger">{{$errors->first('user_id')}}</p>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                {!! Form::label('branch_id', 'Branch') !!}
                                {!! Form::select('branch_id', [], null, ['class' => 'form-control'.($errors->has('branch_id') ? ' is-invalid' : ''), 'form' => 'add_schedule']) !!}
                                <p class="text-danger">{{$errors->first('branch_id')}}</p>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                {!! Form::label('date', 'Date') !!}
                                {!! Form::date('date', now(), ['class' => 'form-control'.($errors->has('date') ? ' is-invalid' : ''), 'form' => 'add_schedule']) !!}
                                <p class="text-danger">{{$errors->first('date')}}</p>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="card-footer text-right">
                    {!! Form::submit('Add Schedule', ['class' => 'btn btn-primary', 'form' => 'add_schedule']) !!}
                </div>
            </div>
        @endcan

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filters</h3>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('user_id', 'User') !!}
                            {!! Form::select('user_id', $users, $user_id, ['class' => 'form-control', 'form' => 'search_form', 'id' => 'user']) !!}
                        </div>
                    </div>
    
                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('branch_id', 'Branch') !!}
                            {!! Form::select('branch_id', $branches, $branch_id, ['class' => 'form-control', 'form' => 'search_form', 'id' => 'branch']) !!}
                        </div>
                    </div>

                </div>

            </div>
            <div class="card-footer text-right">
                {!! Form::submit('Filter', ['class' => 'btn btn-primary', 'form' => 'search_form']) !!}
            </div>
        </div>
        
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Calendar</h3>
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
                                {!! Form::file('upload_file', ['class' => 'custom-file-input'.($errors->has('upload_file') ? ' is-invalid' : ''), 'form' => 'upload_form']) !!}
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

@endsection

@section('plugins.Fullcalendar', true);

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
                } else if(type == 'delete') {
                    Livewire.emit('getDate', date_format);
                    $('#delete-modal').modal('show');
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

        $('#user_id').select2({
            ajax: { 
                url: '{{route("user.ajax")}}',
                type: "POST",
                dataType: 'json',
                delay: 50,
                data: function (params) {
                    return {
                        search: params.term // search term
                    };
                },
                processResults: function (response) {
                    return {
                        results: response
                    };
                },
                cache: true
            }
        });

        $('#branch_id').select2({
            ajax: { 
                url: '{{route("branch.ajax")}}',
                type: "POST",
                dataType: 'json',
                delay: 50,
                data: function (params) {
                    return {
                        search: params.term // search term
                    };
                },
                processResults: function (response) {
                    return {
                        results: response
                    };
                },
                cache: true
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