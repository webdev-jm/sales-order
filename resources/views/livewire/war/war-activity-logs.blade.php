<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ACTIVITY LOGS</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr class="text-center">
                        <th>TYPE</th>
                        <th>USER</th>
                        <th>DESCRIPTION</th>
                        <th>TIMESTAMP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                    <tr>
                        <td class="p-0 text-center align-middle">{{$activity->log_name}}</td>
                        <td class="p-0 text-center align-middle">{{$activity->causer->fullName() ?? '-'}}</td>
                        <td class="p-0 pl-1 text-left align-middle">{{$activity->description}}</td>
                        <td class="p-0 text-center align-middle">{{$activity->created_at->diffForHumans()}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$activities->links()}}
        </div>
    </div>
</div>
