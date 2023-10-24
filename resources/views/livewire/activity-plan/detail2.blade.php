<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ACTIVITY PLAN DETAILS</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr class="text-uppercase">
                        <th>DATE</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- month days --}}
                    @foreach($month_days[$month] as $date => $day)
                    <tr class="{{$day['class']}}" data-widget="expandable-table" aria-expanded="{{$expand_dates[$date] ? 'true' : 'false'}}" wire:click="expandDate('{{$date}}')">
                        <td class="text-uppercase font-weight-bold">
                            <i class="expandable-table-caret fas fa-caret-right fa-fw"></i>
                            {{$day['day']}} - {{$day['date']}}
                            <span class="badge badge-warning float-right">
                                {{count($day['lines'])}} schedule/s
                            </span>
                        </td>
                    </tr>
                    <tr class="expandable-body{{$expand_dates[$date] ? '' : ' d-none'}}">
                        <td class="text-right">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-info">
                                        <tr class="text-center">
                                            <th class="p-0 text-center align-middle">#</th>
                                            <th>Location</th>
                                            <th>Account</th>
                                            <th>Branch</th>
                                            <th>Purpose</th>
                                            <th>Work With</th>
                                            <th>Trip</th>
                                            <th>

                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $num = 0;
                                        @endphp
                                        @foreach($day['lines'] as $line_key => $data)
                                            @php
                                                $num++;
                                            @endphp
                                            <tr>
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
                                                            wire:keyup="setAccountQuery('{{$date}}', {{$line_key}})"
                                                            wire:keydown.escape="resetAccountQuery"
                                                            wire:keydown.tab.prevent="resetAccountQuery"
                                                            
                                                            @if(!empty($data['account_name']))
                                                                placeholder="{{$data['account_name']}}"
                                                            @endif
                                                        />
                                                        @if(!empty($data['account_id']))
                                                            <span class="input-group-append">
                                                                <button class="btn text-danger" wire:click.prevent="clearAccount('{{$date}}', '{{$line_key}}')"><i class="fa fa-times"></i></button>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    {{-- search results --}}
                                                    @if(isset($account_query[$date][$line_key]) && !empty($account_query[$date][$line_key]))
                        
                                                        <div class="list-group position-absolute search-branch" wire:loading.remove>
                                                            @if($accounts->count() > 0)
                                                                @foreach($accounts as $account)
                                                                    <button class="list-group-item text-left" wire:click.prevent="selectAccount('{{$date}}', '{{$line_key}}',{{$account->id}}, '[{{$account->account_code}}], {{$account->short_name}}')">[{{$account->account_code}}], {{$account->short_name}}</button>
                                                                @endforeach
                                                            @else
                                                                <button class="list-group-item">No Results</button>
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
                                                            wire:keyup="setBranchQuery('{{$date}}', '{{$line_key}}')"
                                                            wire:keydown.escape="resetBranchQuery"
                                                            wire:keydown.tab.prevent="resetBranchQuery"
                                                            
                                                            @if(!empty($data['branch_name']))
                                                                placeholder="{{$data['branch_name']}}"
                                                            @endif
                                                        />
                                                        @if(!empty($data['branch_id']))
                                                            <span class="input-group-append">
                                                                <button class="btn text-danger" wire:click.prevent="clearBranch('{{$date}}', '{{$line_key}}')"><i class="fa fa-times"></i></button>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    
                                                    {{-- search results --}}
                                                    @if(isset($branch_query[$date][$line_key]) && !empty($branch_query[$date][$line_key]))
                                                        {{-- show results --}}
                                                        <div class="list-group position-absolute search-branch" wire:loading.remove>
                                                            @if($branches->count() > 0)
                                                                @foreach($branches as $branch)
                                                                    <button class="list-group-item text-left" 
                                                                        wire:click.prevent="selectBranch('{{$date}}', '{{$line_key}}',{{$branch->id}}, '[{{$branch->account->short_name}}], {{$branch->branch_code}} - {{str_replace("'", "",$branch->branch_name)}}')"
                                                                    >
                                                                        [{{$branch->account->short_name}}], {{$branch->branch_code}} - {{$branch->branch_name}}
                                                                    </button>
                                                                @endforeach
                                                            @else
                                                                <button class="list-group-item">No Results</button>
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
                                                        <i class="fa fa-plane"></i>
                                                        TRIP
                                                        @if(isset($data['trip']) && !empty($data['trip']['trip_number']))
                                                            NUMBER:
                                                            <span class="ml-1 font-weight-bold">{{$data['trip']['trip_number']}}</span>
                                                        @endif
                                                    </button>
                                                </td>
                                                {{-- remove row --}}
                                                <td class="text-center align-middle p-0">
                                                    <a href="#" class="text-danger" wire:click.prevent="removeLine('{{$date}}', {{$line_key}})"><i class="fa fa-trash-alt"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Add schedule line --}}
                            <button class="btn btn-primary btn-xs mb-2 mr-3" wire:click.prevent="addSheduleLine('{{$date}}')">
                                <i class="fa fa-plus mr-1"></i>
                                ADD SCHEDULE
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
