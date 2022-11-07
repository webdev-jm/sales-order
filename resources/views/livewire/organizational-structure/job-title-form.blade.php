<div>
    <form wire:submit.prevent="submitForm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Job Title</h4>
            </div>
            <div class="modal-body">
                
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="job-title">Job Title</label>
                            <input type="text" class="form-control @error('job_title') is-invalid @enderror" id="job-title" wire:model.defer="job_title">
                            @error('job_title')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Job Title</button>
            </div>
        </div>
    </form>
</div>
