<div class="modal-content">

    @section('plugins.EkkoLightbox', true)

    <form wire:submit.prevent="logout" enctype="multipart/form-data">
        <div class="modal-header">
            <h4 class="modal-title">Logout Branch <span class="badge badge-primary">[{{$branch->branch_code}}] {{$branch->branch_name}}</span></h4>
        </div>
        <div class="modal-body text-left">

            @if(!empty($logged_branch->operation_process_id))
            <h4>Activities</h4>

            <div class="row mb-3">
                <div class="col-12">
                    <label>{{$logged_branch->operation_process->operation_process}}</label>
                    <ol>
                        @foreach($branch_activities as $activity)
                        <li>{{$activity->activity->description}}
                            @if(!empty($activity->remarks))
                            <ul>
                                <li>{{$activity->remarks}}</li>
                            </ul>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>
            @elseif(!empty($logged_branch->login_activities()->count()))
            <h4>Remarks</h4>
            <p>{{$logged_branch->login_activities()->first()->remarks}}</p>
            @endif

            <div class="row">

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
                            <input type="file" wire:model="picture_file" id="picture" accept="image/png, image/gif, image/jpeg" class="custom-file-input {{$errors->has('picture_file') ? 'is-invalid' : ''}}">
                            <label for="picture" class="custom-file-label">Upload Picture</label>
                            <p class="text-danger">{{$errors->first('picture_file')}}</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-danger" wire:loading.attr="disabled">Sign Out</button>
        </div>
    </form>

    <script>
        document.addEventListener('livewire:load', function () {

            $('body').on('click', '[data-toggle="lightbox"]', function(event) {
                event.preventDefault();
                $(this).ekkoLightbox({
                    alwaysShowClose: true
                });
            });

        });
    </script>
</div>

