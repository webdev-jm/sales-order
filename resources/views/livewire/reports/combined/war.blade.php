<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-1">Weekly Productivity Reports</h3>
            <div class="card-tools" wire:loading>
                <i class="fa fa-spinner fa-spin fa-xs"></i>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Account</th>
                        <th>Date</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weekly_activity_reports as $weekly_activity_report)
                    <tr>
                        <td>{{$weekly_activity_report->user->fullName()}}</td>
                        <td>[{{$weekly_activity_report->area->area_code ?? $weekly_activity_report->accounts_visited}}] {{$weekly_activity_report->area->area_name ?? ''}}</td>
                        <td>{{$weekly_activity_report->date_from}} to {{$weekly_activity_report->date_to}}</td>
                        <td>{{$weekly_activity_report->date_submitted}}</td>
                        <td class="text-center align-middle">
                            <span class="badge badge-{{$status_arr[$weekly_activity_report->status]}}">{{$weekly_activity_report->status}}</span>
                        </td>
                        <td class="text-center align-middle px-1">
                            <a href="{{route('war.show', $weekly_activity_report->id)}}" target="_blank" title="view details"><i class="fa fa-info-circle text-primary"></i></a>
                            <a href="{{route('war.print-pdf', $weekly_activity_report->id)}}" target="_blank" title="print to pdf"><i class="fa fa-file-pdf text-danger"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$weekly_activity_reports->links()}}
        </div>
    </div>
</div>
