<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Organization Structure</h3>
            <div class="card-tools">
                <a href="#" class="btn btn-primary btn-sm" id="btn-structure-add"><i class="fa fa-plus mr-1"></i>Add Structure</a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>User</th>
                        <th>Reports To</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($structures as $structure)
                    <tr>
                        <td>{{$structure->job_title->job_title}}</td>
                        {{-- user --}}
                        @if(!empty($structure->user_id))
                            <td>{{$structure->user->fullName()}}</td>
                        @else
                            <td>Vacant</td>
                        @endif
                        {{-- reports to --}}
                        <td>{{$reports_to_arr[$structure->id]}}</td>
                        <td class="p-0 text-center align-middle">
                            <a href="" class="btn-structure-edit" data-id="{{$structure->id}}"><i class="fa fa-edit text-success"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$structures->links()}}
        </div>
    </div>
</div>
