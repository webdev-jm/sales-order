<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">PRE PLAN UPLOAD</h4>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="" class="mb-0">UPLOAD FILE</label>
                        <input type="file" class="form-control{{$errors->has('file') ? ' is-invalid' : ''}}" wire:model="file">
                        <small class="text-danger">{{$errors->first('file')}}</small>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
            <button type="button" class="btn btn-primary" wire:click.prevent="upload">UPLOAD</button>
        </div>
    </div>
</div>
