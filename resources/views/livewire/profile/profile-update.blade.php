<div>
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    
    <form class="form-horizontal" wire:submit.prevent="submitForm">
        <div class="form-group row">
            <label for="firstname" class="col-sm-2 col-form-label">First Name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control @error('firstname') is-invalid @enderror" id="firstname" placeholder="First Name" wire:model.defer="firstname">
                @error('firstname')
                <p class="text-danger">{{$message}}</p>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="lastname" class="col-sm-2 col-form-label">Last Name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control @error('lastname') is-invalid @enderror" id="lastname" placeholder="Last Name" wire:model.defer="lastname">
                @error('lastname')
                <p class="text-danger">{{$message}}</p>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="email" class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-10">
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="Email" wire:model.defer="email">
                @error('email')
                <p class="text-danger">{{$message}}</p>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <div class="offset-sm-2 col-sm-10">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>
</div>
