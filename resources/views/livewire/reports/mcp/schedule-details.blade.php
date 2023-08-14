<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Schedule Details</h4>
        </div>
        <div class="modal-body p-0">
            @if(!empty($schedule))
                <ul class="list-group">
                    <li class="list-group-item py-2">
                        <span class="font-weight-bold text-uppercase">
                            {{$schedule->user->fullName()}}
                        </span>
                    </li>
                    <li class="list-group-item py-2">
                        <span class="font-weight-bold text-uppercase">
                            [{{$schedule->branch->account->short_name}}] {{$schedule->branch->branch_code}} - {{$schedule->branch->branch_name}} 
                        </span>
                    </li>
                    <li class="list-group-item py-2">
                        <b>Source:</b>
                        {{$schedule->source}}
                    </li>
                    <li class="list-group-item py-2">
                        <b>Objective:</b>
                        {{$schedule->objective}}
                    </li>
                    {{-- DEVIATION DETAILS --}}
                    @if($schedule->source == 'deviation')
                        @if(!empty($deviation_data))
                        <li class="list-group-item py-2">
                            <b>Reason for Deviation:</b>
                            {{$deviation_data->reason_for_deviation}}
                        </li>
                        @endif
                    @endif
                </ul>
                {{-- APPROVALS --}}
                @if($schedule->source == 'deviation')
                    @if(!empty($deviation_data) && !empty($deviation_data->approvals->count()))
                    <table class="table table-bordered table-sm m-3">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deviation_data->approvals as $approval)
                            <tr>
                                <td class="text-uppercase">{{$approval->user->fullName()}}</td>
                                <td>{{$approval->status}}</td>
                                <td>{{$approval->remarks}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
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
