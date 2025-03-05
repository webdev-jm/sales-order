<div>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th class="w200 text-center align-middle px-0">
                    @if(auth()->user()->group_code == 'RD')
                        <img src="{{asset('/assets/images/asia.jpg')}}" alt="logo" class="logo">
                    @else
                        <img src="{{asset('/assets/images/logo.jpg')}}" alt="logo" class="logo">
                    @endif
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
                <td colspan="6" class="px-2">
                    {{$user->fullName()}}
                </td>

                {{-- space --}}
                <td class="border-0" colspan="3"></td>

                <th class="war-label">COVERED PERIOD:</th>
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
                <th class="war-label">ACCOUNTS VISITED:</th>
                <td colspan="6" class="p-0 align-middle">
                    <div class="input-group input-group-sm">
                        {!! Form::text('accounts_visited', implode(', ', $accounts_arr), ['class' => 'form-control border-0 bg-white'.($errors->has('accounts_covered') ? ' is-invalid' : ''), 'form' => $type, 'readonly']) !!}
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
            
        </tbody>
    </table>

    {{-- areas --}}
    <div class="table-responsive">

        @if(!empty($area_lines))
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th class="align-middle war-label" colspan="14">II. AREAS</th>
                    </tr>
                </thead>
                @foreach($area_lines as $line)
                    <thead>
                        <tr class="text-center section-header">
                            <th>DATE</th>
                            <th>DAY</th>
                            <th>AREA COVERED</th>
                            <th colspan="2">REMARKS</th>
                        </tr>
                        <tr class="line-row areas text-center">
                            <td class="p-0 align-middle">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm border-0 text-center bg-white font-weight-bold" form="{{$type}}" name="area_date[{{$line['date']}}]" value="{{$line['date']}}" readonly>
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
                                <th>PLANS</th>
                                <th>ACTION POINTS</th>
                                <th>RESULTS</th>
                            </tr>
                            @foreach($line['schedules'] as $schedule)
                                <tr class="text-center">
                                    <td class="p-0 align-middle text-left pl-2">
                                        <span class="badge badge-{{$schedule['source'] == 'request' ? 'warning' : 'success'}}">{{$schedule['source']}}</span>
                                        <br>
                                        <span>
                                            {{$schedule['branch']['account']['short_name']}} [{{$schedule['branch']['branch_code']}}] {{$schedule['branch']['branch_name']}}
                                        </span>
                                        <br>
                                        <span>
                                            @if(!empty($line['attachments_arr'][$line['date']][$schedule['branch_id']]))
                                                @foreach($line['attachments_arr'][$line['date']][$schedule['branch_id']] as $attachments)
                                                    @if(!empty($attachments))
                                                        @foreach($attachments as $attach)
                                                            <a href="{{asset('storage/'.$attach['file'])}}" target="_blank">
                                                                {{$attach['title']}}
                                                            </a>
                                                            <input type="hidden" name="branch_attachment_exists[{{$line['date']}}][{{$schedule['branch_id']}}][{{$attach['id']}}]" value="{{$attach['id']}}" form="{{$type}}">
                                                            <br>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            @endif
                                            @if(isset($attachment_view['schedule'][$schedule['id']]) && $attachment_view['schedule'][$schedule['id']])
                                                <div class="input-group input-group-sm">
                                                    <input type="file" class="form-control" name="branch_attachment[{{$line['date']}}][{{$schedule['branch_id']}}][]" multiple form="{{$type}}">
                                                    <span class="input-group-prepend align-middle mr-1">
                                                        <button class="btn btn-xs btn-danger mb-1" wire:click.prevent="showAttachments({{$schedule['id']}}, 'schedule')">
                                                            CANCEL
                                                        </button>
                                                    </span>
                                                </div>
                                            @else
                                                <button class="btn btn-xs btn-primary mb-1" wire:click.prevent="showAttachments({{$schedule['id']}}, 'schedule')">
                                                    ATTACHMENTS
                                                </button>
                                            @endif
                                        </span>
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
                                            <button data-toggle="tooltip" data-placement="right" title="View Details" class="btn btn-xs btn-primary btn-show-details" wire:click.prevent="showDetail({{$login['id']}})">
                                                <i class="fa fa-info-circle mr-1"></i>
                                                BRANCH ACTIVITIES
                                            </button>
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
                                                if(!empty($line['schedules_visited'][$schedule['id']])) {
                                                    $login = $line['schedules_visited'][$schedule['id']];
                                                    $action_point = $login['action_points'];
                                                } else {
                                                    if(!empty($line['action_points_arr'][$line['date']])) {
                                                        $action_point = collect(collect($line['action_points_arr'][$line['date']])
                                                            ->first())
                                                            ->where('branch_id', $schedule['branch_id'])
                                                            ->first();

                                                        $action_point = $action_point['action_points'];
                                                    }
                                                }
                                            @endphp
                                            <textarea name="action_points[{{$line['date']}}][{{$schedule['branch_id']}}]" class="form-control border-0 bg-editable align-middle" form="{{$type}}">{{$action_point ?? ''}}</textarea>
                                        </div>
                                    </td>
                                </tr>
                                
                                @php
                                    // reset login data
                                    $login = NULL;
                                @endphp
                            @endforeach
                            @foreach($line['deviations'] as $deviation)
                                <tr class="text-center">
                                    <td class="p-0 align-middle text-left pl-2">
                                        <span>
                                            {{$deviation['branch']['account']['short_name']}} [{{$deviation['branch']['branch_code']}}] {{$deviation['branch']['branch_name']}}
                                        </span>
                                        <br>
                                        <span>
                                            @if(!empty($line['attachments_arr'][$line['date']][$deviation['branch_id']]))
                                                @foreach($line['attachments_arr'][$line['date']][$deviation['branch_id']] as $attachments)
                                                    @if(!empty($attachments))
                                                        @foreach($attachments as $attach)
                                                            <a href="{{asset('storage/'.$attach['file'])}}" target="_blank">
                                                                {{$attach['title']}}
                                                            </a>
                                                            <input type="hidden" name="branch_attachment_exists[{{$line['date']}}][{{$deviation['branch_id']}}][{{$attach['id']}}]" value="{{$attach['id']}}" form="{{$type}}">
                                                            <br>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            @endif
                                            @if(isset($attachment_view['deviation'][$deviation['id']]) && $attachment_view['deviation'][$deviation['id']])
                                                <div class="input-group input-group-sm">
                                                    <input type="file" class="form-control" name="branch_attachment[{{$line['date']}}][{{$deviation['branch_id']}}][]" multiple form="{{$type}}">
                                                    <span class="input-group-prepend align-middle mr-1">
                                                        <button class="btn btn-xs btn-danger mb-1" wire:click.prevent="showAttachments({{$deviation['id']}}, 'deviation')">
                                                            CANCEL
                                                        </button>
                                                    </span>
                                                </div>
                                            @else
                                                <button class="btn btn-xs btn-primary mb-1" wire:click.prevent="showAttachments({{$deviation['id']}}, 'deviation')">
                                                    ATTACHMENTS
                                                </button>
                                            @endif
                                        </span>
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
                                        <button data-toggle="tooltip" data-placement="right" title="View Details" class="btn btn-xs btn-primary btn-show-details" wire:click.prevent="showDetail({{$deviation['id']}})">
                                            <i class="fa fa-info-circle mr-1"></i>
                                            BRANCH ACTIVITIES
                                        </button>
                                    </td>
                                    <td class="p-0">
                                        <div class="input-group input-group-sm">
                                            @php
                                                $action_points = NULL;
                                                
                                                if(!empty($deviation['action_points'])) {
                                                    $action_points = $deviation['action_points'];
                                                } else {
                                                    if(!empty($line['action_points_arr'][$line['date']])) {
                                                        $action_point = collect(collect($line['action_points_arr'][$line['date']])
                                                            ->first())
                                                            ->where('branch_id', $deviation['branch_id'])
                                                            ->first();
                                                        $action_points = $action_point['action_points'];
                                                    }
                                                }

                                            @endphp
                                            <textarea name="action_points[{{$line['date']}}][{{$deviation['branch_id']}}]" class="form-control border-0 bg-editable align-middle" form="{{$type}}">{{$action_points ?? ''}}</textarea>
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
