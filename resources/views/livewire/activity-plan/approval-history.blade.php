<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Approval History</h4>
        </div>
        <div class="modal-body table-responsive p-1">
            
            <table class="table table-bordered">
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

            <div class="mt-2">
                {{$approvals->links()}}
            </div>

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
