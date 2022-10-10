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
                <div class="col-12">
                    <button class="btn btn-primary" wire:click.prevent="addBranch"><i class="fa fa-plus mr-1"></i>Add Branch</button>
                </div>
            </div>

            <hr>

            <div class="row">
                @foreach($branches as $branch)
                <div class="col-lg-4 my-2">
                    <button type="button" class="btn btn-default btn-block h-100" wire:click.prevent="selectBranch({{$branch->id}})" wire:loading.attr="disabled">[{{$branch->branch_code}}] {{$branch->branch_name}}</button>
                </div>
                @endforeach
            </div>

        </div>
        <div class="modal-footer">
            {{$branches->links()}}
        </div>
    @elseif($branch_form)
    <form wire:submit.prevent="submitAddBranch">
        <div class="modal-header">
            <h4 class="modal-title">Add Branch <span class="badge badge-primary">[{{$account->account_code}}] {{$account->short_name}}</span></h4>
        </div>
        <div class="modal-body text-left">

            <div class="row">

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Branch Code</label>
                        <input type="text" class="form-control @error('branch_code') is-invalid @enderror" wire:model.defer="branch_code">
                        @error('branch_code')
                        <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Branch Name</label>
                        <input type="text" class="form-control @error('branch_name') is-invalid @enderror" wire:model.defer="branch_name">
                        @error('branch_name')
                        <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Region</label>
                        <input type="text" class="form-control @error('region') is-invalid @enderror" wire:model.defer="region">
                        @error('region')
                        <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Classification</label>
                        <input type="text" class="form-control @error('classification') is-invalid @enderror" wire:model.defer="classification">
                        @error('classification')
                        <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Area</label>
                        <input type="text" class="form-control @error('area') is-invalid @enderror" wire:model.defer="area">
                        @error('area')
                        <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer text-right">
            <button class="btn btn-default" wire:click.prevent="branchBack">Back</button>
            <button class="btn btn-primary" type="submit">Add Branch</button>
        </div>
    </form>
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
