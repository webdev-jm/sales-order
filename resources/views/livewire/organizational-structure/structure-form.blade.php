<div>
    <form wire:submit.prevent="submitForm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{!empty($structure_data) ? 'Edit Structure' : 'Add Structure'}}</h4>
            </div>
            <div class="modal-body">
                
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Job Title</label>
                            <select class="form-control @error('job_title_id') is-invalid @enderror" wire:model.defer="job_title_id">
                                <option value=""></option>
                                @foreach($job_titles as $job_title)
                                <option value="{{$job_title->id}}">{{$job_title->job_title}}</option>
                                @endforeach
                            </select>
                            @error('job_title_id')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>User</label>
                            <select class="form-control @error('user_id') is-invalid @enderror" wire:model.defer="user_id">
                                <option value="">Vacant</option>
                                @foreach($users as $user)
                                <option value="{{$user->id}}">{{$user->fullName()}}</option>
                                @endforeach
                            </select>
                            @error('user_id')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Reports To</label>
                            <select class="form-control @error('reports_to_id') is-invalid @enderror" wire:model.defer="reports_to_id">
                                <option value="">None</option>
                                @foreach($structures as $structure)
                                <option value="{{$structure->id}}">{{$structure->job_title->job_title}} - 
                                @if(!empty($structure->user_id))
                                    {{$structure->user ? $structure->user->fullName() : '-'}}
                                @else
                                    Vacant
                                @endif
                                </option>
                                @endforeach
                            </select>
                            @error('reports_to_id')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">{{!empty($structure_data) ? 'Edit Structure' : 'Add Structure'}}</button>
            </div>
        </div>
    </form>
</div>
