<div>
    <li class="nav-item">
        <a href="" class="nav-link" id="btn-changelog">
            <i class="fas fa-clipboard-list text-warning"></i>
            <span class="navbar-badge animation__shake"><i class="fa fa-asterisk text-danger"></i></span>
        </a>
    </li>

    <div class="modal fade" id="changelog-modal" style="z-index: 9999;">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Change Logs</h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">

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
