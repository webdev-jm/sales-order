<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title">Events [{{$date}}]</h4>
    </div>
    <div class="modal-body">

        @if(!empty($schedule_data))
            <div class="row">
                <div class="col-12">
                    <label>{{$schedule_data->user->firstname}} {{$schedule_data->user->lastname}}</label>
                    <h3>[{{$schedule_data->branch->branch_code}}] {{$schedule_data->branch->branch_name}}</h3>
                </div>

                @if(!empty($action))
                    <div class="col-12">
                        <form wire:submit.prevent="submit">
                            @if($action == 'reschedule-request')
                            <div class="row">
                                <div class="col-12">
                                    <label>RESCHEDULE</label>
                                </div>

                                <div class="col-lg-6 mt-2">
                                    <div class="form-group">
                                        <label for="">Schedule Date</label>
                                        <input type="date" class="form-control @error('reschedule_date') is-invalid @enderror" wire:model.defer="reschedule_date">
                                        @error('reschedule_date')
                                        <p class="text-danger">{{$message}}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
                            </div>
                            @endif
                        </form>
                    </div>

                    <div class="col-12 mt-2">
                        <button class="btn btn-default" wire:click.prevent="backAction" wire:loading.attr="disabled"><i class="fa fa-arrow-left mr-2"></i>Back</button>
                    </div>
                @else
                    <div class="col-12">
                        <button class="btn btn-warning" wire:click.prevent="setAction('reschedule-request')"><i class="fa fa-clock mr-2"></i>Reschedule Request</button>
                        <button class="btn btn-danger" wire:click.prevent="setAction('delete-request')"><i class="fa fa-trash-alt mr-2"></i>Delete Request</button>
                        <button class="btn btn-info" wire:click.prevent="setAction('sign-in')"><i class="fa fa-sign-in-alt mr-2"></i>Sign In</button>
                    </div>

                    <div class="col-12 mt-2">
                        <button class="btn btn-default" wire:click.prevent="back" wire:loading.attr="disabled"><i class="fa fa-arrow-left mr-2"></i>Back</button>
                    </div>
                @endif
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    {{$branch_schedules->links()}}
                </div>
            </div>

            @if(!empty($branch_schedules))
            <div class="list-group">
                @foreach($branch_schedules as $schedule)
                <a href="#" class="list-group-item list-group-item-action" wire:click.prevent="viewSchedule({{$schedule->id}})"  wire:loading.attr="disabled">
                    {{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}
                    <span class="float-right">{{$schedule->user->firstname}} {{$schedule->user->lastname}}</span>
                </a>
                @endforeach
            </div>
            @endif
        @endif

    </div>
    <div class="modal-footer text-right">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
</div>
