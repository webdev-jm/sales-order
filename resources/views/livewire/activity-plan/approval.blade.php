<div>
    <form wire:submit.prevent="submitApproval">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h4 class="modal-title">
                    @if(!empty($action))
                        @if($action == 'reject')
                            <i class="fas fa-times-circle text-danger mr-2"></i>Reject Activity Plan
                        @elseif($action == 'approve')
                            <i class="fas fa-check-circle text-success mr-2"></i>Approve Activity Plan
                        @else
                            <i class="fas fa-stamp mr-2"></i>MCP Approval
                        @endif
                    @else
                        <i class="fas fa-stamp mr-2"></i>MCP Approval
                    @endif
                </h4>
                <span wire:loading class="float-right"><i class="fa fa-spinner fa-spin"></i></span>
            </div>
            <div class="modal-body">

                @if(!empty($activity_plan) && !empty($action))
                    @if($action == 'reject')
                        <div class="form-group">
                            <label for="remarks" class="font-weight-bold">Remarks / Reason for Rejection</label>
                            <textarea id="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4" placeholder="Enter your reason for rejection..." wire:model.defer="remarks"></textarea>
                            @error('remarks')
                            <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>
                    @elseif($action == 'approve')
                        <div class="callout callout-success">
                            <h5><i class="fas fa-info-circle mr-2"></i>Confirm Approval</h5>
                            <p class="mb-0">
                                Are you sure you want to approve this Activity Plan?
                                <br>
                                <small class="text-muted"><strong>Note:</strong> This activity plan will be included in the schedule of the user upon approval.</small>
                            </p>
                        </div>
                    @endif
                @endif

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                @if(!empty($activity_plan) && !empty($action))
                    @if($action == 'reject')
                        <button class="btn btn-danger" wire:loading.attr="disabled">
                            <i class="fas fa-times mr-1"></i> Reject
                        </button>
                    @elseif($action == 'approve')
                        <button class="btn btn-success" wire:loading.attr="disabled">
                            <i class="fas fa-check mr-1"></i> Approve
                        </button>
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
