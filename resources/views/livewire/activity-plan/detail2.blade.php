<div>
    @php
        $on_leave_dates_with_schedules = [];
        foreach($month_days[$month] as $date => $day) {
            if(!empty($day['on_leave'])) {
                $has_visible_lines = false;
                foreach($day['lines'] as $line) {
                    if(empty($line['deleted']) || $line['deleted'] == false) {
                        $has_visible_lines = true;
                        break;
                    }
                }
                if($has_visible_lines) {
                    $on_leave_dates_with_schedules[] = $day['day'] . ' - ' . $day['date'];
                }
            }
        }
    @endphp
    <div id="on-leave-warnings" data-dates="{{ json_encode($on_leave_dates_with_schedules) }}" class="d-none"></div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-1"></i> Activity Plan Details</h3>
            <div class="card-tools">
                <button class="btn btn-sm btn-outline-secondary mr-1" wire:click.prevent="minimizeAll">
                    <i class="fa fa-minus mr-1"></i> Minimize All
                </button>
                <button class="btn btn-sm btn-outline-secondary" wire:click.prevent="expandAll">
                    <i class="fa fa-chevron-down mr-1"></i> Expand All
                </button>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead class="thead-light">
                    <tr class="text-uppercase">
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- month days --}}
                    @foreach($month_days[$month] as $date => $day)
                    <tr class="{{$day['class']}}" data-widget="expandable-table" aria-expanded="{{$expand_dates[$date] ? 'true' : 'false'}}" wire:click="expandDate('{{$date}}')" wire:key="date-{{$date}}">
                        <td class="text-uppercase font-weight-bold">
                            <i class="expandable-table-caret fas fa-caret-right fa-fw"></i>
                            {{$day['day']}} - {{$day['date']}}
                            @if(!empty($day['holiday']))
                                @if(!empty($day['holiday_work']))
                                <span class="badge badge-warning ml-1"><i class="fas fa-briefcase mr-1"></i>{{ $day['holiday'] }} (Work Day)</span>
                                @else
                                <span class="badge badge-success ml-1"><i class="fas fa-flag mr-1"></i>{{ $day['holiday'] }}</span>
                                @endif
                            @endif
                            <span class="float-right">
                                <button class="btn btn-xs {{$day['on_leave'] ? 'btn-danger' : 'btn-outline-danger'}} mr-2"
                                    wire:click.stop="toggleOnLeave('{{$date}}')"
                                    title="{{$day['on_leave'] ? 'Click to remove vacation leave' : 'Mark as vacation leave'}}">
                                    <i class="fa fa-umbrella-beach mr-1"></i>
                                    {{$day['on_leave'] ? 'VACATION LEAVE' : 'Set Vacation Leave'}}
                                </button>
                                @if(!$day['on_leave'])
                                <span class="badge badge-warning">
                                    @php
                                        $count = count(array_filter($day['lines'], function($item) {
                                            return empty($item['deleted']) || (!empty($item['deleted']) && $item['deleted'] == false);
                                        }));
                                    @endphp
                                    {{$count}} {{$count == 1 ? 'schedule' : 'schedules'}}
                                </span>
                                @endif
                            </span>
                        </td>
                    </tr>
                    <tr class="expandable-body{{$expand_dates[$date] ? '' : ' d-none'}}" wire:key="date-body-{{$date}}">
                        <td class="text-right">
                            @if($day['on_leave'])
                                <div class="alert alert-danger text-center my-2 mx-3">
                                    <i class="fa fa-umbrella-beach mr-2"></i>
                                    <strong>VACATION LEAVE</strong> — No schedule required for this date.
                                </div>
                                @php
                                    $prev_date = date('Y-m-d', strtotime($date . ' -1 day'));
                                    $next_date = date('Y-m-d', strtotime($date . ' +1 day'));
                                    $is_consecutive_leave = (
                                        !empty($month_days[$month][$prev_date]['on_leave']) ||
                                        !empty($month_days[$month][$next_date]['on_leave'])
                                    );
                                @endphp
                                <div class="mx-3 mb-2 text-left">
                                    <label class="small font-weight-bold {{ $is_consecutive_leave ? 'text-danger' : 'text-muted' }}">
                                        Remarks
                                        @if($is_consecutive_leave)
                                            <span class="text-danger">*</span> <span class="font-weight-normal">(required for consecutive vacation leave)</span>
                                        @else
                                            <span class="font-weight-normal">(optional)</span>
                                        @endif
                                    </label>
                                    <textarea
                                        class="form-control form-control-sm {{ ($is_consecutive_leave && empty($day['on_leave_remarks'])) ? 'border-danger' : '' }}"
                                        wire:model.lazy="month_days.{{ $month }}.{{ $date }}.on_leave_remarks"
                                        rows="2"
                                        placeholder="Reason for leave...">{{ $day['on_leave_remarks'] ?? '' }}</textarea>
                                    @if($is_consecutive_leave && empty($day['on_leave_remarks']))
                                        <small class="text-danger d-block mt-1">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            Remarks are required for consecutive vacation leave days.
                                        </small>
                                    @endif
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr class="text-center">
                                                <th class="p-0 text-center align-middle">#</th>
                                                <th>Location</th>
                                                <th>Account</th>
                                                <th>Branch</th>
                                                <th>Purpose</th>
                                                <th>Work With</th>
                                                <th>Trip</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $num = 0;
                                            @endphp
                                            @foreach($day['lines'] as $line_key => $data)
                                                @if(empty($data['deleted']) || (!empty($data['deleted']) && !$data['deleted']))
                                                @php $num++; @endphp
                                                    <tr wire:key="line-{{$date}}-{{$line_key}}">
                                                        {{-- number --}}
                                                        <th class="text-center px-1">
                                                            {{$num}}
                                                        </th>
                                                        {{-- location --}}
                                                        <td class="p-0 align-middle">
                                                            <input type="text" class="form-control form-control-sm border-0" wire:model.lazy="month_days.{{$month}}.{{$date}}.lines.{{$line_key}}.location">
                                                        </td>
                                                        {{-- account --}}
                                                        <td class="p-0 align-middle">
                                                            {{-- search input --}}
                                                            <div class="input-group input-group-sm">
                                                                <input type="text" class="form-control border-0"
                                                                    wire:model="account_query.{{$date}}.{{$line_key}}"
                                                                    wire:keyup.debounce.200ms="setAccountQuery('{{$date}}', '{{$line_key}}')"
                                                                    wire:keydown.escape="resetAccountQuery"
                                                                    wire:keydown.tab.prevent="resetAccountQuery"
                                                                    @if(!empty($data['account_name']))
                                                                        placeholder="{{$data['account_name']}}"
                                                                    @endif
                                                                />
                                                                @if(!empty($data['account_id']))
                                                                    <span class="input-group-append">
                                                                        <button class="btn text-danger border-0" wire:click.prevent="clearAccount('{{$date}}', '{{$line_key}}')"><i class="fa fa-times"></i></button>
                                                                    </span>
                                                                @endif

                                                                <span wire:loading wire:target="setAccountQuery" class="input-group-append"><i class="fa fa-spinner fa-spin fa-sm text-muted mx-1"></i></span>
                                                            </div>

                                                            {{-- search results --}}
                                                            @if(isset($account_query[$date][$line_key]) && !empty($account_query[$date][$line_key]))
                                                                <div class="list-group position-absolute search-branch" wire:loading.remove wire:key="account-results-{{$date}}-{{$line_key}}">
                                                                    @if($accounts->count() > 0)
                                                                        @foreach($accounts as $account)
                                                                            {{-- Pass only the ID to the Livewire method --}}
                                                                            <button wire:key="account-{{ $account->id }}" class="list-group-item list-group-item-action text-left" wire:click.prevent="selectAccount('{{$date}}', '{{$line_key}}', {{$account->id}})">[{{$account->account_code}}], {{$account->short_name}}</button>
                                                                        @endforeach
                                                                    @else
                                                                        <button class="list-group-item disabled">No Results</button>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </td>
                                                        {{-- branch --}}
                                                        <td class="p-0 align-middle">
                                                            {{-- search input --}}
                                                            <div class="input-group input-group-sm">
                                                                <input type="text" class="form-control border-0"
                                                                    wire:model="branch_query.{{$date}}.{{$line_key}}"
                                                                    wire:keyup.debounce.200ms="setBranchQuery('{{$date}}', '{{$line_key}}')"
                                                                    wire:keydown.escape="resetBranchQuery"
                                                                    wire:keydown.tab.prevent="resetBranchQuery"
                                                                    @if(!empty($data['branch_name']))
                                                                        placeholder="{{$data['branch_name']}}"
                                                                    @endif
                                                                />
                                                                @if(!empty($data['branch_id']))
                                                                    <span class="input-group-append">
                                                                        <button class="btn text-danger border-0" wire:click.prevent="clearBranch('{{$date}}', '{{$line_key}}')"><i class="fa fa-times"></i></button>
                                                                    </span>
                                                                @endif

                                                                <span wire:loading wire:target="setBranchQuery" class="input-group-append"><i class="fa fa-spinner fa-spin fa-sm text-muted mx-1"></i></span>
                                                            </div>

                                                            {{-- search results --}}
                                                            @if(isset($branch_query[$date][$line_key]) && !empty($branch_query[$date][$line_key]))
                                                                {{-- show results --}}
                                                                <div class="list-group position-absolute search-branch" wire:loading.remove wire:target="setBranchQuery" wire:key="branch-results-{{$date}}-{{$line_key}}">
                                                                    @if($branches->count() > 0)
                                                                        @foreach($branches as $branch)
                                                                            <button class="list-group-item list-group-item-action text-left"
                                                                                wire:key="branch-item-{{$branch->id}}"
                                                                                wire:click.prevent="selectBranch('{{$date}}', '{{$line_key}}',{{$branch->id}})"
                                                                            >
                                                                                [{{$branch->account->short_name}}], {{$branch->branch_code}} - {{$branch->branch_name}}
                                                                            </button>
                                                                        @endforeach
                                                                    @else
                                                                        <button class="list-group-item disabled">No Results</button>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </td>
                                                        {{-- purpose --}}
                                                        <td class="p-0 align-middle">
                                                            <textarea rows="1" class="form-control form-control-sm border-0" wire:model.lazy="month_days.{{$month}}.{{$date}}.lines.{{$line_key}}.purpose"></textarea>
                                                        </td>
                                                        {{-- work with --}}
                                                        <td class="p-0 align-middle">
                                                            <input type="text" class="form-control form-control-sm border-0" wire:model.lazy="month_days.{{$month}}.{{$date}}.lines.{{$line_key}}.work_with">
                                                        </td>
                                                        {{-- trip --}}
                                                        <td class="p-0 align-middle text-center">
                                                            <button class="btn {{isset($data['trip']) && !empty($data['trip']['trip_number']) ? 'btn-success' : 'btn-info'}} btn-xs btn-trip" data-year="{{$year}}" data-month="{{$month}}" data-date="{{$date}}" data-key="{{$line_key}}">
                                                                @if(isset($data['trip']) && !empty($data['trip']['transportation_type']) && $data['trip']['transportation_type'] == 'LAND')
                                                                    <i class="fa fa-car"></i>
                                                                @else
                                                                    <i class="fa fa-plane"></i>
                                                                @endif
                                                                TRIP
                                                                @if(isset($data['trip']) && !empty($data['trip']['trip_number']))
                                                                    NUMBER:
                                                                    <span class="ml-1 font-weight-bold">{{$data['trip']['trip_number']}}</span>
                                                                @endif
                                                            </button>
                                                        </td>
                                                        {{-- remove row --}}
                                                        <td class="text-center align-middle p-0">
                                                            <a href="#" class="btn btn-xs btn-outline-danger" wire:click.prevent="removeLine('{{$date}}', {{$line_key}})"><i class="fa fa-trash-alt"></i></a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Add schedule line --}}
                                <button class="btn btn-primary btn-xs mb-2 mr-3" wire:click.prevent="addScheduleLine('{{$date}}')">
                                    <i class="fa fa-plus mr-1"></i>
                                    Add Schedule
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
