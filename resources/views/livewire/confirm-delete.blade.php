<div>
    <form wire:submit.prevent="submitForm">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title">Confirm Delete</h4>
            </div>
            <div class="modal-body">
                <div wire:loading.flex wire:target="setModel" class="align-items-center text-muted mb-2">
                    <i class="fa fa-spinner fa-spin mr-2"></i> Loading record...
                </div>

                <div wire:loading.remove wire:target="setModel">
                    @if(!empty($name))
                        <p class="mb-1">You are about to delete:</p>
                        <p class="font-weight-bold mb-2">{{ $name }}</p>
                    @endif
                    <p>
                        Are you sure you want to delete this record? <br>
                        <small class="text-danger">
                            Deleting this will affect the associated records in the database.
                        </small>
                    </p>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="password">Please enter your password to continue.</label>
                            <input type="password" id="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" wire:model.defer="password" autocomplete="current-password">
                            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                            <p class="text-danger mb-0">{{$error_message}}</p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" wire:loading.attr="disabled" @if(empty($model)) disabled @endif>
                    <span wire:loading.remove wire:target="submitForm"><i class="fas fa-trash-alt mr-1"></i>Delete</span>
                    <span wire:loading wire:target="submitForm"><i class="fa fa-spinner fa-spin mr-1"></i>Deleting...</span>
                </button>
            </div>
        </div>
    </form>
</div>
