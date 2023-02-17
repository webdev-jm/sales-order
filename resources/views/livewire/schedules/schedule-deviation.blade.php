<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title">Schedule for [{{$date}}]</h4>
    </div>
    <div class="modal-body">

        @if(!empty($errors->all()))
        <div class="alert alert-danger">
            <ul class="mb-0 pl-2">
            @foreach($errors->all() as $error)
                <li>{{$error}}</li>
            @endforeach
            </ul>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th class="w100 text-center align-middle">
                            <img src="{{asset('/assets/images/logo.jpg')}}" alt="bevi" width="80px">
                        </th>
                        <th class="text-center align-middle" colspan="3">
                            DEVIATION FORM
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>NAME:</th>
                        <td colspan="2">{{$user->fullName()}}</td>

                        <th @error('cost_center') class="border border-danger" @enderror>COST CENTER:
                            <input type="text" class="border-0" wire:model="cost_center">
                        </th>
                    </tr>
                    <tr>
                        <th colspan="4">REASON FOR DEVIATION</th>
                    </tr>
                    <tr>
                        <td colspan="4" class="p-0 @error('reason_for_deviation') border border-danger @enderror">
                            <textarea class="form-control border-0 textarea @error('reason_for_deviation') is-invalid @enderror" wire:model="reason_for_deviation"></textarea>
                        </td>
                    </tr>
                    <tr class="bg-gray">
                        <th class="align-middle" colspan="4">ORIGINAL PLAN</th>
                    </tr>
                    <tr class="text-center">
                        <th>#</th>
                        <th>SCHEDULE</th>
                        <th>ACCOUNT AND AREA</th>
                        <th>ACTIVITY</th>
                    </tr>
                    @if(!empty($original_schedules)) 
                        @foreach($original_schedules as $schedule)
                        <tr class="text-center">
                            <td></td>
                            <td>{{$schedule->date}}</td>
                            <td>
                                {{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}
                            </td>
                            <td class="text-left">
                                {{$schedule->objective}}
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr class="text-center">
                            <td colspan="4">No Schedule</td>
                        </tr>
                    @endif
                    <tr class="bg-gray">
                        <th class="align-middle" colspan="4">
                            NEW PLAN
                            <button class="btn btn-primary btn-xs float-right" wire:click.prevent="addLine" wire:loading.attr="disabled"><i class="fa fa-plus mr-1"></i>Add Line</button>
                        </th>
                    </tr>
                    <tr class="text-center">
                        <th>#</th>
                        <th>SCHEDULE</th>
                        <th>ACCOUNT AND AREA</th>
                        <th>ACTIVITY</th>
                    </tr>
                    @if(!empty($new_schedules))
                        @foreach($new_schedules as $key => $schedule)
                        <tr>
                            <td class="align-middle text-center">
                                <button class="btn btn-danger btn-sm" wire:click.prevent="removeLine({{$key}})" wire:loading.attr="disabled"><i class="fa fa-trash-alt"></i></button>
                            </td>
                            <td class="text-center align-middle">
                                <input type="date" class="form-control border-0 text-center" wire:model.lazy="new_schedules.{{$key}}.date">
                            </td>
                            <td class="align-middle">
                                <textarea class="form-control border-0 textarea" 
                                wire:model="branchQuery.{{$key}}"
                                wire:keyup="setQuery('{{$key}}')"
                                wire:keydown.escape="resetQuery"
                                wire:keydown.tab.prevent="resetQuery"

                                placeholder="{{$schedule['branch_name']}}" style="min-width: 250px"></textarea>

                                @if(isset($branchQuery[$key]) && !empty($branchQuery[$key]))
                                    <div class="list-group position-absolute" wire:loading>
                                        <button class="list-group-item">Searching...</button>
                                    </div>

                                    <div class="list-group position-absolute" wire:loading.remove>
                                        @if($branches->count() > 0)
                                            @foreach($branches as $branch)
                                                <button class="list-group-item text-left" wire:click.prevent="selectBranch({{$key}}, {{$branch->id}}, '[{{$branch->account->account_name}}] {{$branch->branch_code}} {{$branch->branch_name}}')">
                                                    [{{$branch->account->account_name}}] {{$branch->branch_code}} {{$branch->branch_name}}
                                                </button>
                                            @endforeach
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="p-0">
                                <textarea class="form-control border-0 textarea" wire:model.lazy="new_schedules.{{$key}}.activity">{{$schedule['activity']}}</textarea>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                    <tr class="bg-gray">
                        <th class="align-middle" colspan="4">APPROVAL</th>
                    </tr>
                    <tr>
                        <th colspan="2">DATE FILED:</th>
                        <td class="text-center">{{date('Y-m-d')}}</td>
                        
                        <th class="text-left">DATE APPROVED:</th>
                    </tr>
                    <tr>
                        <th colspan="2">DATE OF DEVIATION:</th>
                        <td class="text-center">{{$date}}</td>

                        <th class="text-left">APPROVED BY:</th>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
    <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button class="btn btn-primary" wire:click="submitForm" wire:loading.attr="disabled">Submit</button>
    </div>

    <script>
        
    </script>
    
</div>