<div class="modal-content">
    @if(!empty($user))
    <form wire:submit.prevent="changePassword">
        <div class="modal-header">
            <h4 class="modal-title">Change Password</h4>
            <div class="card-tools">
                <span class="badge badge-primary">{{$user->firstname}} {{$user->lastname}}</span>
            </div>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model.lazy="password">
                        @error('password')
                        <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model.lazy="password_confirmation">
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </div>
    </form>
    @endif
</div>
