<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Add Holiday</h4>
        </div>
        <div class="modal-body">

            <h4>{{$date}}</h4>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="">Title</label>
                        <input type="text" class="form-control{{$errors->has('title') ? ' is-invalid' : ''}}" wire:model.lazy="title">
                        @error('title')
                            <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="switch" wire:model="repeat" value="1">
                        <label class="custom-control-label" for="switch">Repeat annually</label>
                        @error('repeat')
                            <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>
                
            </div>

        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" wire:loading.attr="disabled" wire:click.prevent="addHoliday">Add Holiday</button>
        </div>
    
        <script>
            document.addEventListener('livewire:load', function () {
            });
        </script>
    </div>
</div>