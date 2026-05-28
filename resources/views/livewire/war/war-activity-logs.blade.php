<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ACTIVITY LOGS</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-sm" style="font-size:0.82rem;">
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
                        <td class="p-1 text-center align-middle">
                            <span class="badge badge-secondary">{{$activity->log_name}}</span>
                        </td>
                        <td class="p-1 text-center align-middle">{{$activity->causer->fullName() ?? '-'}}</td>
                        <td class="p-1 text-left align-middle">{{$activity->description}}</td>
                        <td class="p-1 text-center align-middle text-nowrap">{{$activity->created_at->format('Y-m-d h:i A')}}</td>
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
