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
        <h1>Weekly Activity Reports / Edit <span class="badge badge-{{$status_arr[$weekly_activity_report->status]}}">{{$weekly_activity_report->status}}</span></h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('war.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['war.update', $weekly_activity_report->id], 'id' => 'update_war']) !!}
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
        <h3 class="card-title">Weekly Activity Report Form</h3>
        <div class="card-tools">
            {!! Form::submit('Save as Draft', ['class' => 'btn btn-secondary btn-submit', 'form' => 'update_war']) !!}
            {!! Form::submit('Submit for Approval', ['class' => 'btn btn-primary btn-submit', 'form' => 'update_war']) !!}
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th class="w200 text-center align-middle px-0">
                        <img src="{{asset('/assets/images/bevi-logo.png')}}" alt="bevi logo">
                    </th>
                    <th class="text-center align-middle war-title" colspan="10">WEEKLY ACTIVITY REPORT</th>
                    <th class="w300 align-top" colspan="3">
                        DATE SUBMITTED: <br>
                        <p class="text-center mb-0 mt-2">{{$weekly_activity_report->date_submitted}}</p>
                    </th>
                </tr>
                {{-- space --}}
                <tr>
                    <th class="border-0" colspan="12"></th>
                </tr>
            </thead>
            <tbody>
                {{-- header --}}
                    <tr>
                        <th class="war-label">NAME:</th>
                        <td colspan="6" class="px-3">{{$weekly_activity_report->user->fullName()}}</td>
    
                        {{-- space --}}
                        <td class="border-0" colspan="3"></td>
    
                        <th class="war-label">DATE:</th>
                        <td class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::date('date_from', $weekly_activity_report->date_from, ['class' => 'form-control border-0'.($errors->has('date_to') ? ' is-invalid' : ''), 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td>to</td>
                        <td class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::date('date_to', $weekly_activity_report->date_to, ['class' => 'form-control border-0'.($errors->has('date_to') ? ' is-invalid' : ''), 'form' => 'update_war']) !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="war-label">AREA:</th>
                        <td colspan="6" class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::select('area_id', $areas, $weekly_activity_report->area_id, ['class' => 'form-control border-0'.($errors->has('area_id') ? ' is-invalid' : ''), 'form' => 'update_war']) !!}
                            </div>
                        </td>
    
                        {{-- space --}}
                        <td class="border-0" colspan="3"></td>
    
                        <th class="war-label">WEEK:</th>
                        <td colspan="3" class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::number('week', $weekly_activity_report->week_number, ['class' => 'form-control border-0'.($errors->has('week') ? ' is-invalid' : ''), 'form' => 'update_war']) !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="war-label">AREA VISITED:</th>
                        <td colspan="6" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::select('area_visited_id', $areas, $weekly_activity_report->area_id, ['class' => 'form-control border-0'.($errors->has('area_visited_id') ? ' is-invalid' : ''), 'form' => 'update_war']) !!}
                            </div>
                        </td>
    
                        {{-- space --}}
                        <td class="border-0" colspan="7"></td>
                    </tr>
                {{-- spacing --}}
                    <tr>
                        <th class="border-0" colspan="14"></th>
                    </tr>
                {{-- objectives --}}
                    <tr>
                        <th class="align-middle war-label" colspan="14">I. OBJECTIVE/S</th>
                    </tr>
                    <tr>
                        <td class="p-0" colspan="14">
                            {!! Form::textarea('objective', $weekly_activity_report->objectives()->first()->objective, ['class' => 'form-control border-0'.($errors->has('objective') ? ' is-invalid' : ''), 'form' => 'update_war', 'rows' => 5]) !!}
                        </td>
                    </tr>
                {{-- areas --}}
                    <tr>
                        <th class="align-middle war-label pr-1" colspan="14">
                            II. AREAS
                            <button class="btn btn-primary btn-xs float-right btn-add-line"><i class="fa fa-plus mr-1"></i>Add Line</button>
                        </th>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="2">DATE</th>
                        <th colspan="2">DAY</th>
                        <th colspan="3">AREA COVERED</th>
                        <th colspan="3">IN/OUT BASE</th>
                        <th colspan="4">ACTIVITIES/REMARKS</th>
                    </tr>
                    @if(!empty($weekly_activity_report->areas))
                        @foreach($weekly_activity_report->areas as $area)
                        <tr class="line-row areas">
                            <td colspan="2" class="p-0 align-middle">
                                <div class="input-group input-group-sm">
                                    {!! Form::date('area_date[]', $area->date, ['class' => 'form-control border-0 text-center area-date', 'form' => 'update_war']) !!}
                                </div>
                            </td>
                            <td colspan="2" class="p-0 align-middle">
                                <div class="input-group input-group-sm">
                                    {!! Form::text('area_day[]', $area->day, ['class' => 'form-control border-0 text-center area-day', 'form' => 'update_war']) !!}
                                </div>
                            </td>
                            <td colspan="3" class="p-0">
                                <div class="input-group input-group-sm">
                                    {!! Form::text('area_covered[]', $area->location, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                                </div>
                            </td>
                            <td colspan="3" class="p-0">
                                <div class="input-group input-group-sm">
                                    {!! Form::text('area_in_base[]', $area->in_base, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                                </div>
                            </td>
                            <td colspan="4" class="p-0">
                                <div class="input-group input-group-sm">
                                    {!! Form::text('area_remarks[]', $area->remarks, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                                    <span class="input-group-prepend align-middle">
                                        <a href="" class="px-2 btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                    <tr class="line-row areas">
                        <td colspan="2" class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::date('area_date[]', date('Y-m-d'), ['class' => 'form-control border-0 text-center area-date', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="2" class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::text('area_day[]', '', ['class' => 'form-control border-0 text-center area-day', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('area_covered[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('area_in_base[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('area_remarks[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                                <span class="input-group-prepend align-middle">
                                    <a href="" class="px-2 btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endif
                {{-- Highlights --}}
                    <tr>
                        <th class="align-middle war-label" colspan="14">III. Highlight(s) of week’s field visit (use 2nd page for more highlights when necessary):</th>
                    </tr>
                    <tr>
                        <td class="p-0" colspan="14">
                            {!! Form::textarea('highlights', $weekly_activity_report->highlights, ['class' => 'form-control border-0', 'form' => 'update_war', 'rows' => 5]) !!}
                        </td>
                    </tr>
                {{-- collections --}}
                    <tr class="text-center section-header">
                        <th colspan="3">BEGINNING AR</th>
                        <th colspan="4">DUE FOR COLLECTION</th>
                        <th colspan="3">BEGINNING HANGING BALANCE</th>
                        <th colspan="4">TARGET RECONCILIATIONS</th>
                    </tr>
                    <tr>
                        <td colspan="3" class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::text('beginning_ar', $weekly_activity_report->collection->beginning_ar, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('due_for_collection', $weekly_activity_report->collection->due_for_collection, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('beginning_hanging_balance', $weekly_activity_report->collection->beginning_hanging_balance, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('target_reconciliations', $weekly_activity_report->collection->target_reconciliations, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="3">WEEK TO DATE</th>
                        <th colspan="4">MONTH TO DATE</th>
                        <th colspan="3">MONTH TARGET</th>
                        <th colspan="4">BALANCE TO SELL</th>
                    </tr>
                    <tr>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('week_to_date', $weekly_activity_report->collection->week_to_date, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('month_to_date', $weekly_activity_report->collection->month_to_date, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('month_target', $weekly_activity_report->collection->month_target, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('balance_to_sell', $weekly_activity_report->collection->balance_to_sell, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                    </tr>
                {{-- action plans --}}
                    <tr>
                        <th class="align-middle war-label pr-1" colspan="14">
                            IV. SALES ACTION PLAN (to achieve sales/collection targets/to accomplish a project):
                            <button class="btn btn-primary btn-xs float-right btn-add-line"><i class="fa fa-plus mr-1"></i>Add Line</button>
                        </th>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="6">ACTION PLAN/S</th>
                        <th colspan="2">TIMETABLE</th>
                        <th colspan="6">PERSON/S RESPONSIBLE</th>
                    </tr>
                    @if(!empty($weekly_activity_report->action_plans))
                        @foreach($weekly_activity_report->action_plans as $action_plan)
                        <tr class="line-row action-plans">
                            <td colspan="6" class="p-0">
                                <div class="input-group input-group-sm">
                                    {!! Form::text('action_plan[]', $action_plan->action_plan, ['class' => 'form-control border-0', 'form' => 'update_war']) !!}
                                </div>
                            </td>
                            <td colspan="2" class="p-0">
                                <div class="input-group input-group-sm">
                                    {!! Form::date('time_table[]', $action_plan->time_table, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                                </div>
                            </td>
                            <td colspan="6" class="p-0">
                                <div class="input-group input-group-sm">
                                    {!! Form::text('person_responsible[]', $action_plan->person_responsible, ['class' => 'form-control border-0', 'form' => 'update_war']) !!}
                                    <span class="input-group-prepend align-middle">
                                        <a href="" class="px-2 btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                    <tr class="line-row action-plans">
                        <td colspan="6" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('action_plan[]', '', ['class' => 'form-control border-0', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="2" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::date('time_table[]', date('Y-m-d'), ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="6" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('person_responsible[]', '', ['class' => 'form-control border-0', 'form' => 'update_war']) !!}
                                <span class="input-group-prepend align-middle">
                                    <a href="" class="px-2 btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endif
                {{-- activities --}}
                    <tr>
                        <th class="align-middle war-label pr-1" colspan="14">
                            V. ACTIVITIES
                            <button class="btn btn-primary btn-xs float-right btn-add-line"><i class="fa fa-plus mr-1"></i>Add Line</button>
                        </th>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="4">ACTIVITY</th>
                        <th>NO. OF DAYS (Weekly)</th>
                        <th>NO. OF DAYS (MTD)</th>
                        <th colspan="4">AREA/REMARKS</th>
                        <th>NO. OF DAYS (YTD)</th>
                        <th colspan="4">% to TOTAL WORKING DAYS</th>
                    </tr>
                    @php
                        $total_weekly = 0;
                        $total_mtd = 0;
                        $total_ytd = 0;
                    @endphp
                    @if(!empty($weekly_activity_report->activities))
                        @foreach($weekly_activity_report->activities as $activity)
                            @php
                                $total_weekly += $activity->no_of_days_weekly;
                                $total_mtd += $activity->no_of_days_mtd;
                                $total_ytd += $activity->no_of_days_ytd;
                            @endphp
                            <tr class="line-row activities">
                                <td colspan="4" class="p-0">
                                    <div class="input-group input-group-sm">
                                        {!! Form::text('activity[]', $activity->activity, ['class' => 'form-control border-0', 'form' => 'update_war']) !!}
                                    </div>
                                </td>
                                <td class="p-0">
                                    <div class="input-group input-group-sm">
                                        {!! Form::number('no_of_days_weekly[]', $activity->no_of_days_weekly, ['class' => 'form-control border-0 text-center days-weekly', 'form' => 'update_war']) !!}
                                    </div>
                                </td>
                                <td class="p-0">
                                    <div class="input-group input-group-sm">
                                        {!! Form::number('no_of_days_mtd[]', $activity->no_of_days_mtd, ['class' => 'form-control border-0 text-center days-mtd', 'form' => 'update_war']) !!}
                                    </div>
                                </td>
                                <td colspan="4" class="p-0">
                                    <div class="input-group input-group-sm">
                                        {!! Form::text('activity_remarks[]', $activity->remarks, ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                                    </div>
                                </td>
                                <td class="p-0">
                                    <div class="input-group input-group-sm">
                                        {!! Form::number('no_of_days_ytd[]', $activity->no_of_days_ytd, ['class' => 'form-control border-0 text-center days-ytd', 'form' => 'update_war']) !!}
                                    </div>
                                </td>
                                <td colspan="4" class="p-0">
                                    <div class="input-group input-group-sm">
                                        {!! Form::text('total_working_days[]', $activity->percent_to_total_working_days, ['class' => 'form-control border-0 text-center days-percent bg-white', 'form' => 'update_war', 'readonly']) !!}
                                        <span class="input-group-prepend align-middle">
                                            <a href="" class="px-2 btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                    <tr class="line-row activities">
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('activity[]', '', ['class' => 'form-control border-0', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::number('no_of_days_weekly[]', '', ['class' => 'form-control border-0 text-center days-weekly', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::number('no_of_days_mtd[]', '', ['class' => 'form-control border-0 text-center days-mtd', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('activity_remarks[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::number('no_of_days_ytd[]', '', ['class' => 'form-control border-0 text-center days-ytd', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('total_working_days[]', '', ['class' => 'form-control border-0 text-center days-percent bg-white', 'form' => 'update_war', 'readonly']) !!}
                                <span class="input-group-prepend align-middle">
                                    <a href="" class="px-2 btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endif
                    <tr class="text-center war-label">
                        <th colspan="4" class="">TOTAL</th>
                        <td id="total-weekly" class="p-0 pr-3 align-middle font-weight-bold"></td>
                        <td id="total-mtd" class="p-0 pr-3 align-middle font-weight-bold"></td>
                        <td colspan="4"></td>
                        <td id="total-ytd" class="p-0 pr-3 align-middle font-weight-bold"></td>
                        <td colspan="4"></td>
                    </tr>
                {{-- spacing --}}
                    <tr>
                        <th class="border-0" colspan="14"></th>
                    </tr>
                {{--  --}}
            </tbody>
        </table>
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
        $('body').on('change', '.area-date', function() {
            var date = $(this).val();
            const d = new Date(date);
            let day = d.getDay(); 

            $(this).closest('tr').find('.area-day').val(days_arr[day]);
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
