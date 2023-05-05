<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Approval History</h4>
        </div>
        <div class="modal-body">

            <div class="timeline timeline-inverse">

                @foreach($approvals as $date => $data)
                    {{-- DATE LABEL --}}
                    <div class="time-label">
                        <span class="bg-primary text-uppercase">
                            {{date('M j Y', strtotime($date))}}
                        </span>
                    </div>

                    @foreach($data as $approval)
                        <!-- timeline item -->
                        <div>
                            <i class="fas fa-user bg-{{$status_arr[$approval->status]}}"></i>

                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> {{$approval->created_at->diffForHumans()}}</span>

                                <h3 class="timeline-header {{!empty($approval->remarks) ? '' : 'border-0'}}">
                                    <a href="#">{{$approval->user->fullName()}}</a> <span class="mx-2 badge bg-{{$status_arr[$approval->status]}}">{{$approval->status}}</span> the activity plan
                                </h3>

                                @if(!empty($approval->remarks))
                                    <div class="timeline-body">
                                        <label class="mb-0">REMARKS:</label>
                                        <p class="mb-0 ml-2">{{$approval->remarks}}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                @endforeach

                <div>
                    <i class="far fa-clock bg-gray"></i>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-12">
                    {{$approval_dates->links()}}
                </div>
            </div>

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
