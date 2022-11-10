<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Schedule Details</h4>
            @if(!empty($detail))<span class="float-right"> {{$detail->date}} </span>@endif
            <span wire:loading class="float-right"><i class="fa fa-spinner fa-spin"></i></span>
        </div>
        <div class="modal-body">
            @if(!empty($detail))
                @if(isset($detail->branch))
                <h5>BRANCH: [{{$detail->branch->branch_code}}] {{$detail->branch->branch_name}}</h5>
                @endif
            
                <ul class="list-group">
                    @if(!empty($detail->exact_location))
                    <li class="list-group-item">
                        <b>ADDRESS: </b>{{$detail->exact_location}}
                    </li>
                    @endif
                    @if(!empty($detail->activity))
                    <li class="list-group-item">
                        <b>ACTIVITY/PURPOSE: </b>{{$detail->activity}}
                    </li>
                    @endif
                    @if(isset($detail->user))
                    <li class="list-group-item">
                        <b>WORK WITH: </b>{{$detail->user->fullName()}}
                    </li>
                    @endif
                </ul>
            @endif

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
        });
    </script>
</div>
