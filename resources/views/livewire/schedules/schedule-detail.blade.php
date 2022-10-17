<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Request Detail</h4>
        </div>
        <div class="modal-body">

            @if(!empty($schedule))
            <div class="row">
                <div class="col-12">
                    @if(!empty($schedule->status))
                    <button class="btn {{$status_colors[$schedule->status]}} font-weight-bold text-uppercase">{{$schedule->status}}</button>
                    @else
                        @php
                            $status = $schedule->approvals()->orderBy('id', 'DESC')->first()->status;
                        @endphp
                        <button class="btn {{$status_colors[$status]}} font-weight-bold text-uppercase">{{$status}}</button>
                    @endif
                    
                </div>

                <div class="col-12 my-2">
                    <div class="table-responsive">
                        <table class="table table-stripe">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedule->approvals as $approval)
                                <tr>
                                    <td>
                                        {{$approval->user->firstname}} {{$approval->user->lastname}}
                                    </td>
                                    <td>
                                        <span class="badge {{$status_colors[$approval->status]}}">{{$approval->status}}</span>
                                    </td>
                                    <td>
                                        {{$approval->remarks}}
                                    </td>
                                    <td>
                                        {{$approval->created_at->diffForHumans()}}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
