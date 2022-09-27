<div>
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    
    <form class="form-horizontal" wire:submit.prevent="submitForm">
        <div class="form-group row">
            <label for="current" class="col-sm-2 col-form-label">Current Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current" placeholder="Current Password" wire:model.lazy="current_password">
                @error('current_password')
                <p class="text-danger">{{$message}}</p>
                @enderror
                @if(!empty($password_error))
                <p class="text-danger">{{$password_error}}</p>
                @endif
            </div>
        </div>
        <div class="form-group row">
            <label for="new" class="col-sm-2 col-form-label">New Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="new" placeholder="New Password" wire:model.lazy="password">
                @error('password')
                <p class="text-danger">{{$message}}</p>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="confirm" class="col-sm-2 col-form-label">Confirm Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="confirm" placeholder="Confirm Password" wire:model.lazy="password_confirmation">
                @error('password')
                <p class="text-danger">{{$message}}</p>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <div class="offset-sm-2 col-sm-10">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Update</button>
            </div>
        </div>
    </form>
</div>
