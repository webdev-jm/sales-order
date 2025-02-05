<div>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th class="w200 text-center align-middle px-0">
                    <img src="{{asset('/assets/images/bevi-logo.png')}}" alt="bevi logo">
                </th>
                <th class="text-center align-middle war-title" colspan="10">WEEKLY PRODUCTIVITY REPORT</th>
                <th class="w300 align-top" colspan="3">
                    DATE SUBMITTED: <br>
                    <p class="text-center mb-0 mt-2"></p>
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
                <td colspan="6" class="px-3">{{$user->fullName()}}</td>

                {{-- space --}}
                <td class="border-0" colspan="3"></td>

                <th class="war-label">DATE:</th>
                <td class="p-0 align-middle">
                    <div class="input-group input-group-sm">
                        {!! Form::date('date_from', date('Y-m-d'), ['class' => 'form-control border-0 bg-editable'.($errors->has('date_to') ? ' is-invalid' : ''), 'form' => $type, 'wire:model' => 'date_from', 'wire:change' => 'changeDate()']) !!}
                    </div>
                </td>
                <td>to</td>
                <td class="p-0 align-middle">
                    <div class="input-group input-group-sm">
                        {!! Form::date('date_to', date('Y-m-d'), ['class' => 'form-control border-0 bg-editable'.($errors->has('date_to') ? ' is-invalid' : ''), 'form' => $type, 'wire:model' => 'date_to', 'wire:change' => 'changeDate()']) !!}
                    </div>
                </td>
            </tr>
            <tr>
                <th class="war-label">AREA VISITED:</th>
                <td colspan="6" class="p-0 align-middle">
                    <div class="input-group input-group-sm">
                        {!! Form::select('area_id', $areas, $area_id, ['class' => 'form-control border-0 bg-editable'.($errors->has('area_id') ? ' is-invalid' : ''), 'form' => $type]) !!}
                    </div>
                </td>

                {{-- space --}}
                <td class="border-0" colspan="3"></td>

                <th class="war-label">WEEK:</th>
                <td colspan="3" class="p-0 align-middle">
                    <div class="input-group input-group-sm">
                        {!! Form::number('week', $war->week_number ?? 0, ['class' => 'form-control border-0'.($errors->has('week') ? ' is-invalid' : ''), 'form' => $type]) !!}
                    </div>
                </td>
            </tr>
            <tr>
                {{-- <th class="war-label">AREA VISITED:</th>
                <td colspan="6" class="p-0">
                    <div class="input-group input-group-sm">
                        {!! Form::select('area_visited_id', $areas, null, ['class' => 'form-control border-0'.($errors->has('area_visited_id') ? ' is-invalid' : ''), 'form' => 'add_war']) !!}
                    </div>
                </td> --}}

                {{-- space --}}
                <td class="border-0" colspan="14"></td>
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
                    {!! Form::textarea('objective', $objectives, ['class' => 'form-control border-0 bg-editable'.($errors->has('objective') ? ' is-invalid' : ''), 'form' => $type, 'rows' => 5]) !!}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- areas --}}
    <div class="table-responsive">

        @if(!empty($area_lines))
            <table class="table table-bordered table-sm">
                @foreach($area_lines as $line)
                    <thead>
                        <tr>
                            <th class="align-middle war-label" colspan="14">II. AREAS</th>
                        </tr>
                        <tr class="text-center section-header">
                            <th>DATE</th>
                            <th>DAY</th>
                            <th>AREA COVERED</th>
                            <th colspan="2">REMARKS</th>
                        </tr>
                        <tr class="line-row areas text-center">
                            <td class="p-0 align-middle">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm border-0 text-center bg-white" form="{{$type}}" name="area_date[{{$line['date']}}]" value="{{$line['date']}}" readonly>
                                </div>
                            </td>
                            <td class="p-0 align-middle">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm border-0 text-center bg-white" form="{{$type}}" name="area_day[{{$line['date']}}]" value="{{$line['day']}}" readonly>
                                </div>
                            </td>
                            <td class="p-0 align-middle">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm border-0 text-center bg-editable" form="{{$type}}" name="area_covered[{{$line['date']}}]" value="{{$line['area']}}">
                                </div>
                            </td>
                            <td class="p-0 align-middle" colspan="2">
                                <div class="input-group input-group-sm">
                                    {!! Form::textarea('area_remarks['.$line['date'].']', $line['activities'], ['class' => 'form-control border-0 text-center bg-editable', 'form' => $type, 'rows' => 1]) !!}
                                    <span class="input-group-prepend align-middle">
                                        <a href="" class="mx-0 btn btn-xs btn-info btn-area-modal" data-date="{{$line['date']}}">
                                            <i class="fa fa-info-circle"></i>
                                            VIEW ACTIVITIES
                                        </a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </thead>
                    @if(!empty($line['schedules']) || !empty($line['deviations']))
                        <tbody>
                            <tr class="sub-line-row">
                                <th>BRANCHES</th>
                                <th>STATUS</th>
                                <th>PLOTTED ACTIVITIES</th>
                                <th>ACTUAL ACTIVITIES</th>
                                <th>ACTION POINTS</th>
                            </tr>
                            @foreach($line['schedules'] as $schedule)
                                <tr class="text-center">
                                    <td class="p-0 align-middle text-left pl-2">
                                        {{$schedule['branch']['account']['short_name']}} [{{$schedule['branch']['branch_code']}}] {{$schedule['branch']['branch_name']}}
                                    </td>
                                    <td class="p-0 align-middle">
                                        
                                        @if(!empty($line['schedules_visited'][$schedule['id']]))
                                            <input type="hidden" name="branch_status[{{$line['date']}}][{{$schedule['branch_id']}}]" value="VISITED" form="{{$type}}">
                                            <span class="bg-success px-1">
                                                VISITED
                                            </span>
                                        @else
                                            <input type="hidden" name="branch_status[{{$line['date']}}][{{$schedule['branch_id']}}]" value="NOT VISITED" form="{{$type}}">
                                            <span class="bg-danger px-1">
                                                NOT VISITED
                                            </span>
                                        @endif

                                    </td>
                                    <td class="p-0 align-middle">
                                        {{$schedule['objective']}}
                                    </td>
                                    <td class="p-0 align-middle">
                                        @if(!empty($line['schedules_visited'][$schedule['id']]))
                                            @php
                                                $login = $line['schedules_visited'][$schedule['id']];
                                            @endphp
                                            <p class="mb-0">
                                                <a href="#" data-toggle="tooltip" data-placement="right" title="View Details" wire:click.prevent="showDetail({{$login['id']}})">
                                                    <i class="fa fa-info-circle text-primary mr-2"></i>
                                                </a>
                                                {{$login['latitude']}}, {{$login['longitude']}}
                                            </p>
                                        @else
                                            -
                                        @endif

                                        <input type="hidden" name="user_branch_schedule_id[{{$line['date']}}][{{$schedule['branch_id']}}]" value="{{$schedule['id']}}" form="{{$type}}">
                                        <input type="hidden" name="branch_login_id[{{$line['date']}}][{{$schedule['branch_id']}}]" value="{{$login['id'] ?? ''}}" form="{{$type}}">
                                    </td>
                                    <td class="p-0">
                                        <div class="input-group input-group-sm">
                                            @php
                                                $action_point = null;
                                                if(!empty($line['action_points_arr'][$line['date']])) {
                                                    $action_point = collect($line['action_points_arr'][$line['date']])
                                                        ->first()
                                                        ->where('branch_id', $schedule['branch_id'])
                                                        ->first();
                                                }
                                            @endphp
                                            <textarea name="action_points[{{$line['date']}}][{{$schedule['branch_id']}}]" class="form-control border-0 bg-editable" form="{{$type}}">{{$action_point->action_points ?? ''}}</textarea>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @foreach($line['deviations'] as $deviation)
                                <tr class="text-center">
                                    <td class="p-0 align-middle text-left pl-2">
                                        {{$deviation['branch']['account']['short_name']}} [{{$deviation['branch']['branch_code']}}] {{$deviation['branch']['branch_name']}}
                                    </td>
                                    <td class="p-0 align-middle">
                                        <input type="hidden" name="branch_status[{{$line['date']}}][{{$deviation['branch_id']}}]" value="DEVIATION" form="{{$type}}">
                                        <span class="bg-warning px-1">
                                            DEVIATION
                                        </span>
                                    </td>
                                    <td class="p-0 align-middle">
                                        -
                                        <input type="hidden" name="user_branch_schedule_id[{{$line['date']}}][{{$deviation['branch_id']}}]" value="" form="{{$type}}">
                                        <input type="hidden" name="branch_login_id[{{$line['date']}}][{{$deviation['branch_id']}}]" value="{{$deviation['id']}}" form="{{$type}}">
                                    </td>
                                    <td class="p-0 align-middle">
                                        <p class="mb-0">
                                            <a href="#" data-toggle="tooltip" data-placement="right" title="View Details" wire:click.prevent="showDetail({{$deviation['id']}})">
                                                <i class="fa fa-info-circle text-primary mr-2"></i>
                                            </a>
                                            {{$deviation['latitude']}}, {{$deviation['longitude']}}
                                        </p>
                                    </td>
                                    <td class="p-0">
                                        <div class="input-group input-group-sm">
                                            @php
                                                $action_point = NULL;
                                                if(!empty($line['action_points_arr'][$line['date']])) {
                                                    $action_point = collect($line['action_points_arr'][$line['date']])
                                                        ->first()
                                                        ->where('branch_id', $deviation['branch_id'])
                                                        ->first();
                                                }
                                            @endphp
                                            <textarea name="action_points[{{$line['date']}}][{{$deviation['branch_id']}}]" class="form-control border-0 bg-editable" form="{{$type}}">{{$action_point->action_points ?? ''}}</textarea>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                @endforeach
            </table>
        @endif
    </div>
    
    {{-- Highlights --}}
    <table class="table table-bordered table-sm">
        
        <tr>
            <th class="align-middle war-label" colspan="14">III. Highlight(s) of week’s field visit (use 2nd page for more highlights when necessary):</th>
        </tr>
        <tr>
            <td class="p-0" colspan="14">
                {!! Form::textarea('highlights', $highlights, ['class' => 'form-control border-0 bg-editable', 'form' => $type, 'rows' => 5]) !!}
            </td>
        </tr>
    </table>

   
</div>
