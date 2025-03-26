<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">REMITTANCE</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="file">Upload file.</label>
                        <input type="file" class="form-control{{$errors->has('upload_file') ? ' is-invalid' : ''}}" wire:model="upload_file">
                        <small class="text-danger">{{$errors->first('upload_file')}}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" wire:click.prevent="checkFile">
                <i class="fa fa-upload"></i>
                CHECK
            </button>
        </div>
    </div>
</div>
