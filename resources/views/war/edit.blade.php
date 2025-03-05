@extends('adminlte::page')

@section('title')
    Weekly Productivity Reports - Form
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
    .bg-editable {
        background-color: rgb(242, 253, 255);
    }
    .sub-line-row {
        background-color:rgb(216, 215, 215);
        color: black;
        text-align: center;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>Weekly Productivity Reports / Edit <span class="badge badge-{{$status_arr[$weekly_activity_report->status]}}">{{$weekly_activity_report->status}}</span></h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('war.list', $weekly_activity_report->user_id)}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['war.update', $weekly_activity_report->id], 'id' => 'update_war', 'enctype' => 'multipart/form-data']) !!}
{!! Form::hidden('status', $weekly_activity_report->status, ['id' => 'status', 'form' => 'update_war']) !!}
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
        <h3 class="card-title">Weekly Productivity Report Form</h3>
        <div class="card-tools">
            {!! Form::submit('Save as Draft', ['class' => 'btn btn-secondary btn-submit', 'form' => 'update_war']) !!}
            {!! Form::submit('Submit for Approval', ['class' => 'btn btn-primary btn-submit', 'form' => 'update_war']) !!}
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <livewire:war.war-form :user_id="$weekly_activity_report->user_id" :war="$weekly_activity_report"/>
    </div>
</div>

<div class="modal fade" id="area-activity-modal">
    <div class="modal-dialog modal-lg">
        <livewire:war.war-area-detail :user_id="$weekly_activity_report->user_id"/>
    </div>
</div>

<div class="modal fade" id="detail-modal">
    <div class="modal-dialog modal-lg">
        <livewire:reports.mcp.login-detail/>
    </div>
</div>
@endsection

@section('js')
<script>
    window.addEventListener('showDetail', event => {
        $('#detail-modal').modal('show');
    });
</script>
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
            var status = (stat_string === 'Save as Draft') ? 'draft' : 'submitted';
            
            $('#status').val(status);

            $('.btn-submit').prop('disabled', true);

            $('#'+$(this).attr('form')).submit();
        });

        // get week number
        $('input[name="date_from"]').on('change', function(e) {
            var date = $(this).val();
            var d = new Date(date);
            var week = d.getWeekOfMonth(true);
            $('input[name="week"]').val(week);
        });

        $('body').on('click', '.btn-add-line', function(e) {
            e.preventDefault();
            var line = $(this).closest('tr').nextAll('.line-row:first');
            var row = line.clone(true);
            row.find('input').val('');
            row.find('input[type="date"]').val('{{date("Y-m-d")}}');
            line.after(row);
        });

        $('body').on('click', '.btn-remove-row', function(e) {
            e.preventDefault();
            var row = $(this).closest('tr');
            var classes = row.prop('class');
            var classes_arr = classes.split(' ');
            var part = classes_arr.pop();
            if(($('.'+part).length) > 1) {
                if(confirm('Are you sure to delete this line?')) {
                    row.remove();
                }
            }
        });

        $('body').on('keyup', '.days-weekly, .days-mtd, .days-ytd', function() {
            computeTotal();
        });

        function computeTotal() {
            // weekly
            var total_weekly = 0;
            $('.days-weekly').each(function() {
                var weekly = $(this).val();
                total_weekly += weekly * 1;
            });
            $('#total-weekly').text(total_weekly);

            // mtd
            var total_mtd = 0;
            $('.days-mtd').each(function() {
                var mtd = $(this).val();
                total_mtd += mtd * 1;
            });
            $('#total-mtd').text(total_mtd);

            // ytd
            var total_ytd = 0;
            $('.days-ytd').each(function() {
                var ytd = $(this).val();
                total_ytd += ytd * 1;
            });
            $('#total-ytd').text(total_ytd);

            // compute percentage
            $('.days-percent').each(function() {
                var ytd = $(this).closest('tr').find('.days-ytd').val();
                if(ytd != '') {
                    var percent = (ytd / total_ytd) * 100;
                    $(this).val(percent.toFixed(2)+'%');
                }
            });
        }

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
