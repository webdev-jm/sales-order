<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity Plans</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Objectives</th>
                        <th>Status</th>
                        <th>Created at</th>
                        <th>Updated at</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activity_plans as $activity_plan)
                    <tr>
                        <td>
                            <a href="{{route('mcp.show', $activity_plan->id)}}">{{$activity_plan->month}}</a>
                        </td>
                        <td>{{$activity_plan->year}}</td>
                        <td>{{$activity_plan->objectives}}</td>
                        <td>{{$activity_plan->status}}</td>
                        <td>{{$activity_plan->created_at}}</td>
                        <td>{{$activity_plan->updated_at}}</td>
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
