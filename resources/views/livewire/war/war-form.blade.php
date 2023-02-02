<div>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th class="w200 text-center align-middle px-0">
                    <img src="{{asset('/assets/images/bevi-logo.png')}}" alt="bevi logo">
                </th>
                <th class="text-center align-middle war-title" colspan="10">WEEKLY ACTIVITY REPORT</th>
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
                            {!! Form::date('date_from', date('Y-m-d'), ['class' => 'form-control border-0'.($errors->has('date_to') ? ' is-invalid' : ''), 'form' => 'add_war', 'wire:model' => 'date_from', 'wire:change' => 'changeDate()']) !!}
                        </div>
                    </td>
                    <td>to</td>
                    <td class="p-0 align-middle">
                        <div class="input-group input-group-sm">
                            {!! Form::date('date_to', date('Y-m-d'), ['class' => 'form-control border-0'.($errors->has('date_to') ? ' is-invalid' : ''), 'form' => 'add_war', 'wire:model' => 'date_to', 'wire:change' => 'changeDate()']) !!}
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="war-label">AREA VISITED:</th>
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
                        {!! Form::textarea('objective', '', ['class' => 'form-control border-0'.($errors->has('objective') ? ' is-invalid' : ''), 'form' => 'add_war', 'rows' => 5]) !!}
                    </td>
                </tr>
            {{-- areas --}}
                <tr>
                    <th class="align-middle war-label pr-1" colspan="14">
                        II. AREAS
                        {{-- <button class="btn btn-primary btn-xs float-right btn-add-line"><i class="fa fa-plus mr-1"></i>Add Line</button> --}}
                    </th>
                </tr>
                <tr class="text-center section-header">
                    <th colspan="2">DATE</th>
                    <th colspan="2">DAY</th>
                    <th colspan="3">AREA COVERED</th>
                    <th colspan="3">IN/OUT BASE</th>
                    <th colspan="4">ACTIVITIES/REMARKS</th>
                </tr>
                @if(!empty($area_lines))
                    @foreach($area_lines as $line)
                    <tr class="line-row areas">
                        <td colspan="2" class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::date('area_date[]', $line['date'], ['class' => 'form-control border-0 text-center area-date', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="2" class="p-0 align-middle">
                            <div class="input-group input-group-sm">
                                {!! Form::text('area_day[]', $line['day'], ['class' => 'form-control border-0 text-center area-day', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('area_covered[]', $line['area'], ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('area_in_base[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war']) !!}
                            </div>
                        </td>
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::textarea('area_remarks[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war', 'rows' => 1]) !!}
                                <span class="input-group-prepend align-middle">
                                    <a href="" class="mx-1 btn-area-modal" data-date="{{$line['date']}}"><i class="fa fa-info-circle text-info"></i></a>
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
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
                            {!! Form::textarea('area_remarks[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'add_war', 'rows' => 1]) !!}
                            <span class="input-group-prepend align-middle">
                                <a href="" class="mx-1 btn-area-modal" data-date="{{date('Y-m-d')}}"><i class="fa fa-info-circle text-info"></i></a>
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
                        {!! Form::textarea('highlights', '', ['class' => 'form-control border-0', 'form' => 'add_war', 'rows' => 5]) !!}
                    </td>
                </tr>
        </tbody>
    </table>
</div>
