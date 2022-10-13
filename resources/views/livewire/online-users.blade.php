<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Online Users</h4>
        </div>
        <div class="modal-body">
            <ul class="list-group" wire:poll.visible.3000ms>
                @foreach($users as $user)
                <li class="list-group-item">
                    {{$user->firstname}} {{$user->lastname}}
                    @if(Cache::has('user-is-online-' . $user->id))
                        <span class="text-success float-right">Online</span>
                    @elseif(!empty($user->last_activity))
                        <span class="text-secondary float-right">{{ Carbon\Carbon::parse($user->last_activity)->diffForHumans() }}</span>
                    @else
                    <span class="text-secondary float-right">Offline</span>
                    @endif
                </li>
                @endforeach
            </ul>

            <div class="row mt-2">
                <div class="col-12">
                    {{$users->links()}}
                </div>
            </div>
        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>

</div>