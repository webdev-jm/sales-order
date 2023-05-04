<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity Plan Details</h3>
            <div class="card-tools text-sm" wire:loading>
                <i class="fa fa-spinner fa-spin"></i>
            </div>
        </div>
        <div class="card-body p-1 table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr class="text-center text-uppercase">
                        <th>Day</th>
                        <th>Date</th>
                        <th></th>
                        <th>Exact Location</th>
                        <th>Account</th>
                        <th>Branch</th>
                        <th>Purpose</th>
                        {{-- <th>Work With</th> --}}
                        <th>Work With</th>
                    </tr>
                </thead>
                <tbody>
                    
                    @foreach($lines[$month] as $date => $line)
                        <tr class="text-center {{$line['class']}}">
                            <td class="align-middle text-uppercase font-weight-bold" rowspan="{{count($line['lines']) + 1}}">
                                {{$line['day']}}
                            </td>
                            <td class="align-middle" rowspan="{{count($line['lines']) + 1}}">
                                {{$line['date']}}
                            </td>
                        </tr>
                        @foreach($line['lines'] as $key => $row)
                        <tr class=" {{$line['class']}}">
                            {{-- remove line --}}
                            <td class="align-middle text-center p-0">
                                @if($key != 0)
                                    <a href="#" class="btn btn-danger btn-xs" wire:click.prevent="removeLine('{{$month}}', '{{$date}}', {{$key}})" wire:loading.attr="disabled"><i class="fa fa-trash-alt fa-sm"></i></a>
                                @endif
                            </td>
                            {{-- location --}}
                            <td class="p-0 align-middle">
                                <textarea class="form-control border-0 {{$line['class']}}" wire:model.lazy="lines.{{$month}}.{{$date}}.lines.{{$key}}.location"></textarea>
                            </td>
                            {{-- account --}}
                            <td class="p-0">
                                <div class="input-group">
                                    <input type="text" class="form-control border-0 {{$line['class']}}" 
                                        wire:model="account_query.{{$date}}.{{$key}}" 
                                        wire:keyup="setAccountQuery('{{$date}}', '{{$key}}')"
                                        wire:keydown.escape="resetAccountQuery"
                                        wire:keydown.tab.prevent="resetAccountQuery"
                                        
                                        @if(!empty($row['account_name']))
                                            placeholder="{{$row['account_name']}}"
                                        @endif
                                    />
                                    @if(!empty($row['account_id']))
                                    <span class="input-group-append">
                                        <button class="btn text-danger" wire:click.prevent="clearAccount('{{$date}}', '{{$key}}')"><i class="fa fa-times"></i></button>
                                    </span>
                                    @endif
                                </div>
                                
                                @if(isset($account_query[$date][$key]) && !empty($account_query[$date][$key]))

                                <div class="list-group position-absolute search-branch" wire:loading>
                                    <button class="list-group-item">Searching...</button>
                                </div>

                                <div class="list-group position-absolute search-branch" wire:loading.remove>
                                    @if($accounts->count() > 0)
                                        @foreach($accounts as $account)
                                            <button class="list-group-item text-left" wire:click.prevent="selectAccount('{{$date}}', '{{$key}}',{{$account->id}}, '[{{$account->account_code}}], {{$account->short_name}}')">[{{$account->account_code}}], {{$account->short_name}}</button>
                                        @endforeach
                                    @else
                                        <button class="list-group-item">No Results</button>
                                    @endif
                                </div>
                                @endif
                            </td>
                            {{-- branches --}}
                            <td class="p-0">
                                <div class="input-group">
                                    <input type="text" class="form-control border-0 {{$line['class']}}" 
                                        wire:model="branch_query.{{$date}}.{{$key}}" 
                                        wire:keyup="setQuery('{{$date}}', '{{$key}}')"
                                        wire:keydown.escape="resetQuery"
                                        wire:keydown.tab.prevent="resetQuery"
                                        
                                        @if(!empty($row['branch_name']))
                                            placeholder="{{$row['branch_name']}}"
                                        @endif
                                    />
                                    @if(!empty($row['branch_id']))
                                    <span class="input-group-append">
                                        <button class="btn text-danger" wire:click.prevent="clearBranch('{{$date}}', '{{$key}}')"><i class="fa fa-times"></i></button>
                                    </span>
                                    @endif
                                </div>
                                
                                @if(isset($branch_query[$date][$key]) && !empty($branch_query[$date][$key]))

                                <div class="list-group position-absolute search-branch" wire:loading>
                                    <button class="list-group-item">Searching...</button>
                                </div>

                                <div class="list-group position-absolute search-branch" wire:loading.remove>
                                    @if($branches->count() > 0)
                                        @foreach($branches as $branch)
                                            <button class="list-group-item text-left" wire:click.prevent="selectBranch('{{$date}}', '{{$key}}',{{$branch->id}}, '[{{$branch->account->short_name}}], {{$branch->branch_code}} - {{$branch->branch_name}}')">[{{$branch->account->short_name}}], {{$branch->branch_code}} - {{$branch->branch_name}}</button>
                                        @endforeach
                                    @else
                                        <button class="list-group-item">No Results</button>
                                    @endif
                                </div>
                                @endif
                            </td>
                            {{-- purpose --}}
                            <td class="p-0 align-middle">
                                <textarea class="form-control border-0 {{$line['class']}}" wire:model.lazy="lines.{{$month}}.{{$date}}.lines.{{$key}}.purpose"></textarea>
                            </td>
                            {{-- work with --}}
                            {{-- <td class="p-0">
                                <select class="form-control border-0 {{$line['class']}}" wire:model.lazy="lines.{{$month}}.{{$date}}.lines.{{$key}}.user_id">
                                    <option value=""></option>
                                    @foreach($users as $user)
                                    <option value="{{$user->id}}">{{$user->fullName()}}</option>
                                    @endforeach
                                </select>
                            </td> --}}
                            {{-- work with --}}
                            <td class="p-0">
                                <input type="text" class="form-control border-0 {{$line['class']}}" wire:model.lazy="lines.{{$month}}.{{$date}}.lines.{{$key}}.work_with">
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-light">
                            <td class="px-2" colspan="9">
                                <button class="btn btn-xs btn-info" wire:click.prevent="addLine('{{$date}}')" wire:loading.attr="disabled"><i class="fa fa-plus mr-1"></i>Add Line</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            
        });
    </script>
</div>
