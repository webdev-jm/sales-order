<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Activities</h4>
            <span wire:loading class="float-right"><i class="fa fa-spinner fa-spin"></i></span>
        </div>
        <div class="modal-body">
            
            @if(!empty($branch_logins))
                
                @if($branch_logins->count() > 0)
                    <div class="list-group">
                        @foreach($branch_logins as $login)
                        <div class="list-group-item">
                            <h3>[{{$login->branch->branch_code}}] {{$login->branch->branch_name}}</h3>
                            

                            @if(!empty($login->operation_process_id))
                            <h4>Activities</h4>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label>{{$login->operation_process->operation_process}}</label>
                                    @php
                                        $branch_activities = $login->login_activities()->whereNotNull('activity_id')->get()
                                    @endphp
                                    <ol>
                                        @foreach($branch_activities as $activity)
                                        <li>{{$activity->activity->description}}
                                            @if(!empty($activity->remarks))
                                            <ul>
                                                <li>{{$activity->remarks}}</li>
                                            </ul>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ol>
                                </div>
                            </div>
                            @elseif(!empty($login->login_activities()->count()))
                            <h4>Remarks</h4>
                            <p>{{$login->login_activities()->first()->remarks}}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="list-group">
                        <div class="list-group-item text-center">
                            No Activities
                        </div>
                    </div>
                @endif
            @endif
            

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
