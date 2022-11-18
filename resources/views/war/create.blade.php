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
{!! Form::open(['method' => 'POST', 'route' => ['war.store'], 'id' => 'add_war']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Weekly Activity Report Form</h3>
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
                        <p class="text-center mb-0 mt-2">2022-11-17</p>
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
                        <td colspan="6" class="px-3">{{auth()->user()->fullName()}}</td>

                        {{-- space --}}
                        <td class="border-0" colspan="3"></td>

                        <th class="war-label">DATE:</th>
                        <td class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::date('date_from', date('Y-m-d'), ['class' => 'form-control border-0'.($errors->has('date_from') ? ' is-invalid' : ''), 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td>to</td>
                        <td class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::date('date_to', date('Y-m-d'), ['class' => 'form-control border-0'.($errors->has('date_to') ? ' is-invalid' : ''), 'form' => 'add_war']) !!}
                            </div>
                            </td>
                    </tr>
                    <tr>
                        <th class="war-label">AREA:</th>
                        <td colspan="6" class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::select('area_id', $areas, null, ['class' => 'form-control border-0'.($errors->has('area_id') ? ' is-invalid' : ''), 'form' => 'add_war']) !!}
                            </div>
                        </td>

                        {{-- space --}}
                        <td class="border-0" colspan="3"></td>

                        <th class="war-label">WEEK:</th>
                        <td colspan="3" class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::number('week', 0, ['class' => 'form-control border-0'.($errors->has('week') ? ' is-invalid' : ''), 'form' => 'add_war']) !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="war-label">AREA VISITED:</th>
                        <td colspan="6" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::select('area_visited_id', $areas, null, ['class' => 'form-control border-0'.($errors->has('area_visited_id') ? ' is-invalid' : ''), 'form' => 'add_war']) !!}
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
                            {!! Form::textarea('objective', '', ['class' => 'form-control border-0'.($errors->has('objective') ? ' is-invalid' : ''), 'form' => 'add_war', 'rows' => 5]) !!}
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
                    <tr class="line-row areas">
                        <td colspan="2" class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::date('area_date[]', date('Y-m-d'), ['class' => 'form-control border-0 text-center area-date', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="2" class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::text('area_day[]', '', ['class' => 'form-control border-0 text-center area-day', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('area_covered[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('area_in_base[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('area_remarks[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                                <span class="input-group-prepend align-middle">
                                    <a href="" class="px-2 btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                </span>
                            </div>
                        </td>
                    </tr>
                {{-- Highlights --}}
                    <tr>
                        <th class="align-middle war-label" colspan="14">III. Highlight(s) of week’s field visit (use 2nd page for more highlights when necessary):</th>
                    </tr>
                    <tr>
                        <td class="p-0" colspan="14">
                            {!! Form::textarea('highlights', '', ['class' => 'form-control border-0', 'form' => 'add_war', 'rows' => 5]) !!}
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
                                {!! Form::text('beginning_ar', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('due_for_collection', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('beginning_hanging_balance', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('target_reconciliations', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
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
                                {!! Form::text('week_to_date', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('month_to_date', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('month_target', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('balance_to_sell', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
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
                    <tr class="line-row action-plans">
                        <td colspan="6" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('action_plan[]', '', ['class' => 'form-control border-0', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="2" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::date('time_table[]', date('Y-m-d'), ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="6" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('person_responsible[]', '', ['class' => 'form-control border-0', 'form' => 'add_war']) !!}
                                <span class="input-group-prepend align-middle">
                                    <a href="" class="px-2 btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                </span>
                            </div>
                        </td>
                    </tr>
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
                    <tr class="line-row activities">
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('activity[]', '', ['class' => 'form-control border-0', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::number('no_of_days_weekly[]', '', ['class' => 'form-control border-0 text-center days-weekly', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::number('no_of_days_mtd[]', '', ['class' => 'form-control border-0 text-center days-mtd', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('activity_remarks[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::number('no_of_days_ytd[]', '', ['class' => 'form-control border-0 text-center days-ytd', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('total_working_days[]', '', ['class' => 'form-control border-0 text-center days-percent bg-white', 'form' => 'add_war', 'readonly']) !!}
                                <span class="input-group-prepend align-middle">
                                    <a href="" class="px-2 btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                </span>
                            </div>
                        </td>
                    </tr>
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
    <div class="card-footer text-right">
        {!! Form::submit('Add Weekly Activity Report', ['class' => 'btn btn-primary', 'form' => 'add_war']) !!}
    </div>
</div>
@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '.btn-add-line', function(e) {
            e.preventDefault();
            var line = $(this).closest('tr').nextAll('.line-row:first');
            var row = line.clone(true);
            row.find('input').val('');
            line.after(row);
        });

        $('body').on('click', '.btn-remove-row', function(e) {
            e.preventDefault();
            var row = $(this).closest('tr');
            var classes = row.prop('class');
            var classes_arr = classes.split(' ');
            var part = classes_arr.pop();
            if(($('.'+part).length) > 1) {
                row.remove();
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
