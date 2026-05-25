<div>
    @if(!empty($subordinate_ids))
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Activity Plan Submission Report</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control" placeholder="Year" wire:model.lazy="year" style="width: 80px;">
                    <select class="form-control ml-1" wire:model.lazy="month">
                        @foreach($months as $key => $mon)
                            <option value="{{$key}}">{{$mon}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">User</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submit_users as $user)
                    <tr wire:key="submit-user-{{ $user->id }}">
                        <td class="pl-3">{{$user->fullName()}}</td>
                        <td>
                            <span class="badge badge-pill {{$submission_arr[$user->id]['status'] == 'submitted' ? 'badge-success' : 'badge-danger'}}">
                                {{$submission_arr[$user->id]['status']}}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{-- {{$submit_users->links()}} --}}
        </div>
    </div>
    @endif
</div>
