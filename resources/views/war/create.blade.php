@extends('adminlte::page')

@section('title')
    Weekly Activity Reports - Form
@endsection

@section('css')
<style>
    .w200 {
        width: 200px !important; 
    }
    .w300 {
        width: 300px !important;
    }
    .war-title {
        font-size: 25px;
    }
    .war-label {
        background-color: rgb(202, 202, 202);
    }

    th, td {
        border: 1.5px solid black !important;
    }
    .section-header {
        background-color: black;
        color: white;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>Weekly Activity Reports / Add</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('war.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['war.store'], 'id' => 'add_war', 'autocomplete' => 'off']) !!}
{!! Form::hidden('status', 'draft', ['id' => 'status', 'form' => 'add_war']) !!}
{!! Form::close() !!}

@if(!empty($errors->all()))
<div class="alert alert-danger">
    <ul>
    @foreach($errors->all() as $error)
        <li>{{$error}}</li>
    @endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Weekly Activity Report Form</h3>
        <div class="card-tools">
            {!! Form::submit('Save as Draft', ['class' => 'btn btn-secondary btn-submit', 'form' => 'add_war']) !!}
            {!! Form::submit('Submit for Approval', ['class' => 'btn btn-primary btn-submit', 'form' => 'add_war']) !!}
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <livewire:war.war-form :user_id="auth()->user()->id"/>
    </div>
</div>

<div class="modal fade" id="area-activity-modal">
    <div class="modal-dialog modal-lg">
        <livewire:war.war-area-detail :user_id="auth()->user()->id"/>
    </div>
</div>
@endsection

@section('js')
<script>
    // get week number in month
    Date.prototype.getWeekOfMonth = function(exact) {
        var month = this.getMonth()
            , year = this.getFullYear()
            , firstWeekday = new Date(year, month, 1).getDay()
            , lastDateOfMonth = new Date(year, month + 1, 0).getDate()
            , offsetDate = this.getDate() + firstWeekday - 1
            , index = 1 // start index at 0 or 1, your choice
            , weeksInMonth = index + Math.ceil((lastDateOfMonth + firstWeekday - 7) / 7)
            , week = index + Math.floor(offsetDate / 7)
        ;
        if (exact || week < 2 + index) return week;
        return week === weeksInMonth ? index + 5 : week;
    };

    $(function() {
        // set status
        $('body').on('click', '.btn-submit', function(e) {
            e.preventDefault();
            var stat_string = $(this).val();
            var status = 'draft';
            if(stat_string == 'Save as Draft') {
                status = 'draft';
            } else {
                status = 'submitted';
            }
            
            $('#status').val(status);
            $('#'+$(this).attr('form')).submit();
        });

        // get week number
        $('input[name="date_from"]').on('change', function(e) {
            var date = $(this).val();
            var d = new Date(date);
            var week = d.getWeekOfMonth(true);
            $('input[name="week"]').val(week);
        });

        let days_arr = [];
        days_arr[0] = 'Sunday';
        days_arr[1] = 'Monday';
        days_arr[2] = 'Tuesday';
        days_arr[3] = 'Wednesday';
        days_arr[4] = 'Thursday';
        days_arr[5] = 'Friday';
        days_arr[6] = 'Saturday';

        // set day on selected date
        $('body').find('.area-date').each(function() {
            var date = $(this).val();
            const d = new Date(date);
            let day = d.getDay(); 

            $(this).closest('tr').find('.area-day').val(days_arr[day]);
        });

        // 2022-11-25 14:17:12 // KS09945
        
        $('body').on('change', '.area-date', function() {
            var date = $(this).val();
            const d = new Date(date);
            let day = d.getDay(); 

            $(this).closest('tr').find('.area-day').val(days_arr[day]);
        });

        // branch login details
        $('body').on('click', '.btn-area-modal', function(e) {
            e.preventDefault();
            var date = $(this).data('date');
            Livewire.emit('setDate', date);
            $('#area-activity-modal').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
