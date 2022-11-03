<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Schedule Requests {{$date ?? ''}}</h4>
        </div>
        <div class="modal-body">

            @if(!empty($schedules)) 
                @if(!empty($schedule_data))
                    {{-- Request Data --}}
                    <div class="row">
                        <div class="col-12">
                            <h4>{{$schedule_data->branch->branch_code}} {{$schedule_data->branch->branch_name}}</h4>
                            <label class="text-uppercase">{{$schedule_data->user->firstname}} {{$schedule_data->user->lastname}}</label>
                        </div>

                        @can('schedule approve request')
                            @if(empty($action))
                                <div class="col-12">
                                    <b>Approve schedule request?</b>
                                </div>
                                <div class="col-12 mb-2">
                                    <button class="btn btn-danger" wire:click.prevent="reject" wire:loading.attr="disabled"><i class="fa fa-ban mr-1"></i>Reject</button>
                                    <button class="btn btn-success" wire:click.prevent="approve" wire:loading.attr="disabled"><i class="fa fa-check mr-1"></i>Approve</button>
                                </div>

                                <div class="col-12">
                                    <a href="" class="btn btn-default" wire:click.prevent="back"><i class="fa fa-arrow-left mr-1"></i>Back</a>
                                </div>

                            @else
                                @if($action == 'approve')
                                    <div class="col-12">
                                        <b>Are you sure to approve this request?</b>
                                        <div class="col-12 mb-2">
                                            <button class="btn btn-danger" wire:click.prevent="cancel" wire:loading.attr="disabled"><i class="fa fa-ban mr-1"></i>Cancel</button>
                                            <button class="btn btn-success" wire:click.prevent="submitApprove" wire:loading.attr="disabled"><i class="fa fa-check mr-1"></i>Approve</button>
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
                                @endif
                            @endif
                        @endcan
                    </div>
                @else
                    {{-- schedule list --}}
                    <div class="list-group">
                        @foreach($schedules as $schedule)
                            <a href="#" class="list-group-item list-group-item-action" wire:click.prevent="selectSchedule({{$schedule->id}})" wire:loading.attr="disabled">
                                {{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}
                                <span class="float-right">{{$schedule->user->firstname}} {{$schedule->user->lastname}}</span>
                            </a>
                        @endforeach
                    </div>

                @endif
            @endif
    
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    
    </div>
    <script>
        document.addEventListener('livewire:load', function () {
        });
    </script>
</div>