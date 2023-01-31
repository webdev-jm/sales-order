@extends('adminlte::page')

@section('title')
    Activity Plan - Details
@endsection

@section('css')
<style>
    .fc-event-time, .fc-event-title {
        padding: 0 1px;
        white-space: normal;
    }
    .fc-daygrid-event {
        cursor: pointer;
    }
    .fc-daygrid-event:hover {
        border-color: #00fffb !important;
    }

    .sticky-top {
        top: 58px;
    }

    pre {
        font-family: "Source Sans Pro",
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            Roboto,
            "Helvetica Neue"
            ,Arial
            ,sans-serif,
            "Apple Color Emoji",
            "Segoe UI Emoji",
            "Segoe UI Symbol";
        font-size: 1rem;
        padding: 0 0 0 5px;
        margin-bottom: 0;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>Activity Plan / Details <span class="badge badge-{{$status_arr[$activity_plan->status]}}">{{$activity_plan->status}}</span></h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('mcp.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        <a href="{{route('mcp.print-pdf', $activity_plan->id)}}" class="btn btn-primary" target="_blank"><i class="fa fa-print mr-1"></i>Print</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            Activity Plan for the Month of: 
            <span class="font-weight-bold text-uppercase">{{date('F', strtotime($activity_plan->year.'-'.$activity_plan->month.'-01'))}} {{$activity_plan->year}}</span>
        </h3>
        @if($activity_plan->status == 'submitted' && (
            in_array($activity_plan->user_id, $subordinate_ids) ||
            auth()->user()->hasRole('superadmin') ||
            auth()->user()->hasRole('admin') || 
            auth()->user()->can('mcp approval')
        ))
            <div class="card-tools">
                <button class="btn btn-danger" id="btn-reject">Reject</button>
                <button class="btn btn-success" id="btn-approve">Approve</button>
            </div>
        @endif
    </div>
    <div class="card-body">
        <p>
            <b>NAME:</b> {{$activity_plan->user->fullName()}}<br>
            @if(!empty($position))
            <b>POSITION:</b> {{implode(', ', $position)}}
            @endif
        </p>
        
        <div class="row">
            <div class="col-lg-12">
                <label class="mb-0">Objectives for the month:</label>
                <pre>{{$activity_plan->objectives}}</pre>
            </div>
        </div>
    </div>
</div>

{{-- calendar --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Calendar</h3>
    </div>
    <div class="card-body">
        <div id="calendar-container"></div>
    </div>
</div>

<div class="modal fade" id="modal-detail">
    <div class="modal-dialog">
        <livewire:activity-plan.schedule-detail/>
    </div>
</div>

<div class="modal fade" id="modal-approval">
    <div class="modal-dialog modal-lg">
        <livewire:activity-plan.approval/>
    </div>
</div>
@endsection

@section('plugins.Fullcalendar', true);

@section('js')
<script>
$(function() {

    // approvals
    // reject
    $('#btn-reject').on('click', function(e) {
        e.preventDefault();
        Livewire.emit('setApproval', 'reject', {{$activity_plan->id}});
        $('#modal-approval').modal('show');
    });
    // approve
    $('#btn-approve').on('click', function(e) {
        e.preventDefault();
        Livewire.emit('setApproval', 'approve', {{$activity_plan->id}});
        $('#modal-approval').modal('show');
    });
    
    // calendar
    var calendarEl = document.getElementById('calendar-container');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left  : 'prev,next',
            center: 'title',
            right : 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        // dayMaxEvents: 5,
        eventClick: function(info) {
            var eventObj = info.event;
            var date = eventObj.start;
            var id = eventObj.id;

            console.log(eventObj);

            var year = date.getFullYear();
            var month = date.getMonth() + 1;
            var day = date.getDate();
            var date_format = year+'-'+(month < 10 ? '0' : '')+month+'-'+(day < 10 ? '0' : '')+day;
            

            Livewire.emit('setDetail', id);
            $('#modal-detail').modal('show');
        },
        themeSystem: 'bootstrap',
        events: @php echo json_encode($schedule_data); @endphp,
        initialDate: '{{$activity_plan->year}}-{{$activity_plan->month}}-01'
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
