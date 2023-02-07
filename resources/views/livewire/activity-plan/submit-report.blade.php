<div>
    @if(!empty($subordinate_ids))
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity Plan Submission Report</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control" placeholder="year" wire:model.lazy="year">
                    <select class="form-control ml-2" wire:model.lazy="month">
                        @foreach($months as $key => $mon)
                            <option value="{{$key}}">{{$mon}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0 table-reponsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submit_users as $user)
                    <tr>
                        <td>{{$user->fullName()}}</td>
                        <td class="{{$submission_arr[$user->id]['status'] == 'submitted' ? 'text-success' : 'text-danger'}}">{{$submission_arr[$user->id]['status']}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$submit_users->links()}}
        </div>
    </div>
    @endif
</div>
