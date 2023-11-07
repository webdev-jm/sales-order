<div>
    <form wire:submit.prevent="submitApproval">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">MCP Approval</h4>
                <span wire:loading class="float-right"><i class="fa fa-spinner fa-spin"></i></span>
            </div>
            <div class="modal-body">

                @if(!empty($activity_plan) && !empty($action))
                    @if($action == 'reject')
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="remarks">Remarks / Reason for rejection</label>
                                    <textarea id="remarks" class="form-control @error('remarks') is-invalid @enderror" wire:model.defer="remarks"></textarea>
                                    @error('remarks')
                                    <p class="text-danger">{{$message}}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @elseif($action == 'approve')
                        <div class="row">
                            <div class="col-lg-10">
                                <p>
                                    <b class="text-uppercase">Are you sure to approve this Activity Plan?</b>
                                    <br>
                                    <span><b>NOTE: </b>This activity plan will be included in the schedule of the user upon approval.</span>
                                </p>
                            </div>
                        </div>
                    @endif
                @endif

            </div>
            <div class="modal-footer text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                @if(!empty($activity_plan) && !empty($action))
                    @if($action == 'reject')
                        <button class="btn btn-danger" wire:loading.attr="disabled">Reject</button>
                    @elseif($action == 'approve')
                        <button class="btn btn-success" wire:loading.attr="disabled">Approve</button>
                    @endif
                @endif
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('livewire:load', function () {
        });
    </script>
</div>
