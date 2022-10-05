<div>
    <div class="modal fade" id="changelog-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Change Logs</h4>
                </div>
                <div class="modal-body">
                    <label class="text-primary">MINOR CHANGES</label>
                    <dl>
                        <dt>Sales order product search</dt>
                        <dd>Include size detail on product searching.</dd>

                        <dt>
                            Address pagination and search
                        </dt>
                        <dd>Add search and pagination on the change address option.</dd>

                        <dt>Sales order finalized confirmation</dt>
                        <dd>Add a confirmation message before finalizing to prevent unintentional submission.</dd>
                    </dl>
                    <label class="text-primary">MAJOR CHANGES</label>
                    <dl>
                        <dt>Product DF20004</dt>
                        <dd>
                            Enable product DF20004 on account PHIL SEVEN and disable it on other accounts.
                        </dd>

                        <dt>Add product KS99065</dt>
                        <dd>Add product KS99065 and its corresponding price codes.</dd>

                        <dt>Auto save sales order form</dt>
                        <dd>
                            Add an auto-save function. Auto saves every 5 seconds currently applied on the edit page. It is recommended to save as a draft first and continue editing to avoid data loss on a poor internet connection.
                        </dd>
                    </dl>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            $('#btn-changelog').on('click', function(e) {
                e.preventDefault();
                $('#changelog-modal').modal('show');
            });
        });
    </script>
</div>
