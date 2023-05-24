<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity Logs</h3>
            <div class="card-tools">
                <input type="text" wire:model="search" class="form-control float-right" placeholder="Search">
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover text-nowrap table-sm">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>User</th>
                        <th>Message</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                    <tr>
                        <td>{{$activity->log_name}}</td>
                        <td>{{$activity->causer->fullName()}}</td>
                        <td>{{$activity->description}}</td>
                        <td>{{$activity->created_at->diffForHumans()}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer pb-0">
            {{$activities->links()}}
        </div>
    </div>
    
</div>
