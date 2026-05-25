<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Request History</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">

            @if(!empty($schedule))
            <div class="row">
                <div class="col-12 mb-2">
                    @if(!empty($schedule->status))
                        <span class="badge {{$status_colors[$schedule->status]}} px-3 py-2 text-uppercase">
                            {{$schedule->status}}
                        </span>
                    @else
                        @php
                            $status = $schedule->approvals()->orderBy('id', 'DESC')->first()->status;
                        @endphp
                        <span class="badge {{$status_colors[$status]}} px-3 py-2 text-uppercase">
                            {{$status}}
                        </span>
                    @endif
                </div>

                <div class="col-12 my-2">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvals as $approval)
                                <tr>
                                    <td>
                                        {{$approval->user->fullName()}}
                                    </td>
                                    <td>
                                        <span class="badge {{$status_colors[$approval->status]}}">{{$approval->status}}</span>
                                    </td>
                                    <td>
                                        {{$approval->remarks}}
                                    </td>
                                    <td class="text-nowrap">
                                        {{$approval->created_at->diffForHumans()}}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-12">
                    {{$approvals->links()}}
                </div>
            </div>
            @endif

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
