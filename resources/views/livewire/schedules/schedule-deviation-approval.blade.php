<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title">Deviation Form
            @if(!empty($deviation))
                <span class="badge badge-{{$status_arr[$deviation->status]}}">{{$deviation->status}}</span>
            @endif
        </h4>
        @if(!empty($deviation))
        <div class="card-tools">
            <a href="{{route('schedule.deviation-print', $deviation->id)}}" target="_blank" class="btn btn-primary"><i class="fa fa-print mr-1"></i>Print</a>
        </div>
        @endif
    </div>
    <div class="modal-body">

        @if(!empty($deviation))
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
                            <th class="text-uppercase">NAME:</th>
                            <td colspan="2">{{$deviation->user->fullName()}}</td>

                            <th>COST CENTER:
                                {{$deviation->cost_center}}
                            </th>
                        </tr>
                        <tr>
                            <th colspan="4">REASON FOR DEVIATION</th>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <p>
                                    {{$deviation->reason_for_deviation}}
                                </p>
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
                                    {{$schedule->activity}}
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
                            </th>
                        </tr>
                        <tr class="text-center">
                            <th>#</th>
                            <th>SCHEDULE</th>
                            <th>ACCOUNT AND AREA</th>
                            <th>ACTIVITY</th>
                        </tr>
                        @if(!empty($new_schedules))
                            @foreach($new_schedules as $schedule)
                            <tr>
                                <td class="text-center">
                                </td>
                                <td class="text-center">
                                    {{$schedule->date}}
                                </td>
                                <td>
                                    [{{$schedule->branch->account->account_code}} {{$schedule->branch->account->short_name}}] {{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}
                                </td>
                                <td class="text-left">
                                    {{$schedule->activity}}
                                </td>
                            </tr>
                            @endforeach
                        @endif
                        <tr class="bg-gray">
                            <th class="align-middle" colspan="4">APPROVAL</th>
                        </tr>
                        <tr>
                            <th colspan="2">DATE FILED:</th>
                            <td class="text-center">{{$deviation->created_at}}</td>
                            
                            <th class="text-left">DATE APPROVED:</th>
                        </tr>
                        <tr>
                            <th colspan="2">DATE OF DEVIATION:</th>
                            <td class="text-center">{{$deviation->date}}</td>

                            <th class="text-left">APPROVED BY:</th>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if(!empty($deviation) && auth()->user()->can('schedule approve deviation') && $deviation->status == 'submitted' && (in_array(auth()->user()->id, $supervisor_ids) || auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin')))
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="remarks">REMARKS:</label>
                        <textarea class="form-control @error('remarks') is-invalid @enderror" rows="5" wire:model.lazy="remarks"></textarea>
                        @error('remarks')
                        <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>
            </div>
            @else
                {{-- approval history --}}
                @if(!empty($approvals))
                    <div class="row">
                        <div class="col-12">
                            <u>APPROVAL HISTORY</u>
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($approvals as $approval)
                                    <tr>
                                        <td>{{$approval->user->fullName()}}</td>
                                        <td>
                                            <span class="badge badge-{{$status_arr[$approval->status]}}">{{$approval->status}}</span>
                                        </td>
                                        <td>{{$approval->remarks}}</td>
                                        <td>{{$approval->created_at->diffForHumans()}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="col-12">
                            {{$approvals->links()}}
                        </div>
                    </div>
                @endif
            @endif
        @endif

    </div>
    <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        @if(!empty($deviation) && auth()->user()->can('schedule approve deviation') && $deviation->status == 'submitted' && (in_array(auth()->user()->id, $supervisor_ids) || auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin')))
            <div>
                <button class="btn btn-success" wire:loading.attr="disabled" wire:click.prevent="approve"><i class="fa fa-check mr-1"></i>Approve</button>
                <button class="btn btn-danger" wire:loading.attr="disabled" wire:click.prevent="reject"><i class="fa fa-ban mr-1"></i>Reject</button>
            </div>
        @endif
    </div>
    
</div>
