<div class="d-inline">
    <a href="#" wire:click.prevent="assignModals" title="user accounts"><i class="fas fa-wrench text-secondary mx-1"></i></a>

    <div class="modal fade" id="assign-modal{{$user_id}}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Assign Accounts</h4>
                </div>
                <div class="modal-body text-left">
        
                    <livewire:users.user-assign-form :user_id="$user_id"/>
            
                </div>
                <div class="modal-footer text-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        window.addEventListener('openFormModal{{$user_id}}', event => {
            $("#assign-modal{{$user_id}}").modal('show');
        });

        window.addEventListener('closeFormModal{{$user_id}}', event => {
            $("#assign-modal{{$user_id}}").modal('hide');
        });
    </script>
</div>
