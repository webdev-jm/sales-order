<div class="modal-content">
@if(!empty($account))
    
    @if(empty($branch))
        <div class="modal-header">
            <h4 class="modal-title">Login to Branch of <span class="badge badge-primary">[{{$account->account_code}}] {{$account->short_name}}</span></h4>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" class="form-control float-right" placeholder="Search" wire:model="search">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search" wire:loading.remove></i>
                            <i class="fa fa-spinner fa-sm fa-spin" wire:loading></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-body text-left">

            <div class="row">
                @foreach($branches as $branch)
                <div class="col-lg-4 my-2">
                    <button type="button" class="btn btn-default btn-block h-100" wire:click.prevent="selectBranch({{$branch->id}})" wire:loading.attr="disabled">[{{$branch->branch_code}}] {{$branch->branch_name}}</button>
                </div>
                @endforeach
            </div>

        </div>
        <div class="modal-footer text-right">
            {{$branches->links()}}
        </div>
    @else
        <form wire:submit.prevent="login" enctype="multipart/form-data">
            <div class="modal-header">
                <h4 class="modal-title">Login to Branch <span class="badge badge-primary">[{{$branch->branch_code}}] {{$branch->branch_name}}</span></h4>
                <div class="card-tools">
                    <button type="button" class="btn btn-secondary" wire:loading.attr="disabled" wire:click.prevent="loadLocation">Reload Location</button>
                </div>
            </div>
            <div class="modal-body text-left">
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Accuracy (m.)</label>
                            <input type="text" wire:model="accuracy" class="form-control bg-white {{$errors->has('accuracy') ? 'is-invalid' : ''}}" readonly>
                            <p class="text-danger">{{$errors->first('accuracy')}}</p>
                        </div>
                    </div>
    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Longitude</label>
                            <input type="text" wire:model="longitude" class="form-control bg-white {{$errors->has('longitude') ? 'is-invalid' : ''}}" readonly>
                            <p class="text-danger">{{$errors->first('longitude')}}</p>
                        </div>
                    </div>
    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Latitude</label>
                            <input type="text" wire:model="latitude" class="form-control bg-white {{$errors->has('latitude') ? 'is-invalid' : ''}}" readonly>
                            <p class="text-danger">{{$errors->first('latitude')}}</p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer align-content-between">
                <button class="btn btn-default" wire:click.prevent="resetBranch" wire:loading.attr="disabled">Back</button>
                <button class="btn btn-primary" wire:loading.attr="disabled">Sign In</button>
            </div>
        </form>
    @endif
    
    <script>
        window.addEventListener('reloadLocation', event => {
            getLocation();
        });

        getLocation();
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    @this.accuracy = position.coords.accuracy.toFixed(3)+' m';
                    @this.longitude = position.coords.longitude;
                    @this.latitude = position.coords.latitude;
                }, function(error) {
                    @this.accuracy = error.message;
                });
            } else { 
                console.log("Geolocation is not supported by this browser.");
            }
        }
    </script>
@endif
</div>
