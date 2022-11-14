<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Reschedule Requests ({{$date}})</h4>
        </div>
        <div class="modal-body">
            @if(!empty($schedules))

                @if(!empty($schedule_data))
                    <div class="row">
                        <div class="col-12">
                            <label class="text-uppercase">{{$schedule_data->user->fullName()}}</label>
                            <h4>{{$schedule_data->branch->branch_code}} {{$schedule_data->branch->branch_name}}</h4>
                            <p>
                                <b>Objective</b><br>
                                {{$schedule_data->objective}}
                            </p>
                        </div>

                        <div class="col-12">
                            <p>
                                <b>Reschedule Date:</b> {{$schedule_data->reschedule_date}}<br>
                                @php
                                    $approval = $approvals->where('status', 'for reschedule')->first();
                                @endphp
                                <b>Remarks:</b> {{$approval->remarks}}
                            </p>
                        </div>
                       
                        @can('schedule approve reschedule')
                            @if(empty($action))
                            <div class="col-12">
                                <button class="btn btn-danger" wire:click.prevent="reject" wire:loading.attr="disabled"><i class="fa fa-ban mr-1"></i>Reject</button>
                                <button class="btn btn-success" wire:click.prevent="approve" wire:loading.attr="disabled"><i class="fa fa-check mr-1"></i>Approve</button>

                                <div class="col-lg-12 mt-3">
                                    <button class="btn btn-default" wire:click.prevent="back" wire:loading.attr="disabled"><i class="fa fa-arrow-left mr-1"></i>Back</button>
                                </div>
                            </div>
                            @elseif($action == 'reject')
                            <div class="col-12">
                                <form wire:submit.prevent="submitReject">
                                    <div class="row">
                                        <div class="col-12">
                                            <label>Remarks</label>
                                            <textarea rows="3" class="form-control @error('remarks') is-invalid @enderror" wire:model.defer="remarks"></textarea>
                                            @error('remarks')
                                            <p class="text-danger">{{$message}}</p>
                                            @enderror
                                        </div>

                                        <div class="col-lg-12 mt-2">
                                            <button class="btn btn-default" wire:click.prevent="cancel" wire:loading.attr="disabled"><i class="fa fa-arrow-left mr-1"></i>Cancel</button>
                                            <button type="submit" class="btn btn-danger"><i class="fa fa-ban mr-1"></i>Reject</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            @elseif($action == 'approve')
                            <div class="col-12">
                                <form wire:submit.prevent="submitApprove">
                                    <div class="col-12">
                                        <p>
                                            <b>Are you sure to appove this reschedule?</b>
                                            <br>
                                            This schedule will be moved to {{$schedule_data->reschedule_date}}
                                        </p>
                                    </div>
                                    <div class="col-lg-12 mt-2">
                                        <button class="btn btn-default" wire:click.prevent="cancel" wire:loading.attr="disabled"><i class="fa fa-arrow-left mr-1"></i>Cancel</button>
                                        <button type="submit" class="btn btn-success"><i class="fa fa-check mr-1"></i>Approve</button>
                                    </div>
                                </form>
                            </div>
                            @endif
                        @endcan
                    </div>
                @else
                    <div class="list-group">
                        @foreach($schedules as $schedule)
                        <a href="#" class="list-group-item list-group-item-action" wire:click.prevent="showDetail({{$schedule->id}})" wire:loading.attr="disabled">
                            <span>[{{$schedule->branch->branch_code}}] {{$schedule->branch->branch_name}}</span>
                            <span class="float-right">{{$schedule->user->fullName()}}</span>
                        </a>
                        @endforeach
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            {{$schedules->links()}}
                        </div>
                    </div>
                @endif
                
            @endif
        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    
        <script>
            document.addEventListener('livewire:load', function () {
            });
        </script>
    </div>
</div>
