@extends('adminlte::page')

@section('title')
    Calendar
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Calendar</h1>
    </div>
    <div class="col-md-6 text-right">
    </div>
</div>
@endsection

@section('content')
<div class="row">
    @if(auth()->user()->can('calendar create'))
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-lg-6">
                        <button class="btn btn-success btn-block" id="btn-upload"><i class="fa fa-upload"></i> Upload</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif
    <div class="{{auth()->user()->can('calendar create') ? 'col-lg-8' : 'col-12'}}">
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
                {!! Form::open(['method' => 'POST', 'route' => ['calendar.upload'], 'id' => 'upload_form', 'enctype' => 'multipart/form-data']) !!}
                {!! Form::close() !!}

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
            themeSystem: 'bootstrap',
            //Random default events
            events: @php echo json_encode($schedule_data); @endphp
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