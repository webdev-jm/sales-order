<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-1">Deviation Forms</h3>
            <div class="card-tools" wire:loading>
                <i class="fa fa-spinner fa-spin fa-xs"></i>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Date</th>
                        <th>Reason For Deviation</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deviations as $deviation)
                    <tr>
                        <td>{{$deviation->user->fullName()}}</td>
                        <td>{{$deviation->date}}</td>
                        <td>{{$deviation->reason_for_deviation}}</td>
                        <td class="text-center">
                            <span class="badge badge-{{$status_arr[$deviation->status]}}">{{$deviation->status}}</span>
                        </td>
                        <td class="text-center align-middle px-1">
                            <a href="#" title="view" class="btn-detail" data-id="{{$deviation->id}}"><i class="fa fa-info-circle text-primary"></i></a>
                            <a href="{{route('schedule.deviation-print', $deviation->id)}}" target="_blank" title="print to pdf"><i class="fa fa-file-pdf text-danger"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$deviations->links()}}
        </div>
    </div>
</div>
