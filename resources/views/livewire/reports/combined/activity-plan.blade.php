<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-1">Activity Plans</h3>
            <div class="card-tools" wire:loading>
                <i class="fa fa-spinner fa-spin fa-xs"></i>
            </div>
        </div>
        <div class="card-body table-reponsive p-0">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Objectives</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activity_plans as $activity_plan)
                    <tr>
                        <td class="align-middle">{{$activity_plan->user->fullName()}}</td>
                        <td>{{$activity_plan->objectives}}</td>
                        <td class="text-center align-middle">
                            <span class="badge badge-{{$status_arr[$activity_plan->status]}}">{{$activity_plan->status}}</span>
                        </td>
                        <td class="text-center align-middle px-1">
                            <a href="{{route('mcp.show', $activity_plan->id)}}" target="_blank"><i class="fa fa-info-circle text-primary"></i></a>
                            <a href="{{route('mcp.print-pdf', $activity_plan->id)}}" target="_blank" title="print to pdf"><i class="fa fa-file-pdf text-danger"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$activity_plans->links()}}
        </div>
    </div>
</div>