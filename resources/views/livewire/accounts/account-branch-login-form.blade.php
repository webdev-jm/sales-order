<div class="modal-content">

    @section('plugins.EkkoLightbox', true)

    <form wire:submit.prevent="logout" enctype="multipart/form-data">
        <div class="modal-header">
            <h4 class="modal-title">Logout Branch <span class="badge badge-primary">[{{$branch->branch_code}}] {{$branch->branch_name}}</span></h4>
            {{-- <div class="card-tools">
                <button type="button" class="btn btn-secondary" id="btn-reload-location">Reload Location</button>
            </div> --}}
        </div>
        <div class="modal-body text-left">

            {{-- <div class="row">
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
            </div> --}}

            <div class="row">
                
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="activities">Activities</label>
                        <textarea wire:model="activities" id="activities" rows="5" class="form-control {{$errors->has('activities') ? 'is-invalid' : ''}}"></textarea>
                        <p class="text-danger">{{$errors->first('activities')}}</p>
                    </div>
                </div>

                @if($picture_file)
                    <label>Preview</label>
                    <div class="col-12 py-2">
                        <a href="{{ $picture_file->temporaryUrl() }}" data-toggle="lightbox" data-title="Preview">
                            <img src="{{ $picture_file->temporaryUrl() }}" class="mx-auto d-block" height="300px">
                        </a>
                    </div>
                @elseif(file_exists($image_url.'/small.jpg'))
                    <label>Preview</label>
                    <div class="col-12 py-2">
                        <a href="{{ asset('uploads/account-login/'.$logged->user_id.'/'.$logged->id).'/large.jpg' }}" data-toggle="lightbox" data-title="Preview">
                            <img src="{{ asset('uploads/account-login/'.$logged->user_id.'/'.$logged->id).'/large.jpg' }}" class="mx-auto d-block" height="300px">
                        </a>
                    </div>
                @endif

                <div class="col-md-12">
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" wire:model="picture_file" id="picture" class="custom-file-input {{$errors->has('picture_file') ? 'is-invalid' : ''}}">
                            <label for="picture" class="custom-file-label">Upload Picture</label>
                            <p class="text-danger">{{$errors->first('picture_file')}}</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div class="modal-footer text-right">
            <button type="submit" class="btn btn-danger" wire:loading.attr="disabled">Sign Out</button>
        </div>
    </form>

    <script>
        document.addEventListener('livewire:load', function () {
            // getLocation();
            // $('body').on('click', '#btn-reload-location', function(e) {
            //     e.preventDefault();
            //     getLocation();
            // });

            $('body').on('click', '[data-toggle="lightbox"]', function(event) {
                event.preventDefault();
                $(this).ekkoLightbox({
                    alwaysShowClose: true
                });
            });

            // function getLocation() {
            //     if (navigator.geolocation) {
            //         navigator.geolocation.getCurrentPosition(function(position) {
            //             @this.accuracy = position.coords.accuracy.toFixed(3)+' m';
            //             @this.longitude = position.coords.longitude;
            //             @this.latitude = position.coords.latitude;
            //         }, function(error) {
            //             @this.accuracy = error.message;
            //         });
            //     } else { 
            //         console.log("Geolocation is not supported by this browser.");
            //     }
            // }

        });
    </script>
</div>

