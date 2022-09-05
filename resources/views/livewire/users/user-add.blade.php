<div class="d-inline">
    <button class="btn btn-primary" wire:click="addForm"><i class="fas fa-plus mr-1"></i>Add User</button>
    
    {{-- MODAL --}}
    <div class="modal fade" id="add-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit.prevent="submitForm">
                    <div class="modal-header">
                        <h4 class="modal-title">Add User</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
            
                        
                
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        window.addEventListener('openFormModal', event => {
            $("#add-modal").modal('show');
        });

        window.addEventListener('closeFormModal', event => {
            $("#add-modal").modal('hide');
        });
    </script>
</div>
