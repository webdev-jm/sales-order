<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Branch Sign-in Details</h4>
        </div>
        <div class="modal-body">
            @if(!empty($branch_login))
                <p class="text-uppercase">
                    <b>BRANCH:</b>
                    {{$branch_login->branch->account->short_name.' - ['.$branch_login->branch->branch_code.'] '.$branch_login->branch->name}}
                </p>

                <p class="text-uppercase">
                    <b>Address: </b>{{ \App\Helpers\AppHelper::instance()->getAddress($branch_login->latitude, $branch_login->longitude) }}
                </p>

                @if(!empty($branch_login->operation_process_id))
                    <label>Activities</label>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label>{{$branch_login->operation_process->operation_process}}</label>
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
                @elseif(!empty($branch_login->login_activities()->count()))
                    <label>Remarks</label>
                    <p>{{$branch_login->login_activities()->first()->remarks}}</p>
                @endif

                @if(is_dir(public_path().'/uploads/branch-login/'.$branch_login->user_id.'/'.$branch_login->id.'/0'))
                    <label>Picture:</label>
                    @php
                        $dirs = glob(public_path().'/uploads/branch-login/'.$branch_login->user_id.'/'.$branch_login->id.'/*', GLOB_ONLYDIR);
                    @endphp
                    <div class="mb-3">
                        @for($i = 0; $i < count($dirs); $i++)
                            <a href="{{ asset('/uploads/branch-login/'.$branch_login->user_id.'/'.$branch_login->id).'/'.$i.'/large.jpg' }}" data-toggle="lightbox" data-title="Preview">
                                <img src="{{asset('/uploads/branch-login/'.$branch_login->user_id.'/'.$branch_login->id.'/'.$i.'/small.jpg')}}" alt="picture" class="mx-1">
                            </a>
                        @endfor
                    </div>
                @elseif(file_exists(public_path().'/uploads/branch-login/'.$branch_login->user_id.'/'.$branch_login->id.'/small.jpg'))
                    <label>Picture:</label>
                    <div class="mb-3">
                        <a href="{{ asset('/uploads/branch-login/'.$branch_login->user_id.'/'.$branch_login->id).'/large.jpg' }}" data-toggle="lightbox" data-title="Preview">
                            <img src="{{asset('/uploads/branch-login/'.$branch_login->user_id.'/'.$branch_login->id.'/small.jpg')}}" alt="picture" class="mx-1">
                        </a>
                    </div>
                @endif
            @endif
        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    
        <script>
            document.addEventListener('livewire:load', function () {
            });
        </script>
    </div>
</div>
