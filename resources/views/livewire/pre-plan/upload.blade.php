<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">PRE PLAN UPLOAD</h4>
        </div>
        <div class="modal-body">

            @if($successMessage)
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fa fa-check-circle mr-1"></i> {{ $successMessage }}
                </div>
            @endif

            @if($errorMessage)
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fa fa-exclamation-circle mr-1"></i> {{ $errorMessage }}
                </div>
            @endif

            <div class="row mb-2">
                <div class="col-lg-12">
                    <a href="{{route('pre-plan.template')}}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-download mr-1"></i>
                        DOWNLOAD TEMPLATE
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="mb-0">UPLOAD FILE</label>
                        <input type="file"
                               class="form-control{{$errors->has('file') ? ' is-invalid' : ''}}"
                               wire:model="file"
                               accept=".xls,.xlsx">
                        <small class="text-danger">{{$errors->first('file')}}</small>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
            <button type="button"
                    class="btn btn-primary"
                    wire:click.prevent="upload"
                    wire:loading.attr="disabled">
                <i class="fa fa-upload mr-1" wire:loading.remove wire:target="upload"></i>
                <i class="fa fa-spinner fa-spin mr-1" wire:loading wire:target="upload"></i>
                UPLOAD
            </button>
        </div>
    </div>
</div>
