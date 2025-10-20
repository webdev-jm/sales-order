<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">RUD SUMMARY</h4>
        </div>
        <div class="modal-body">
            @if(!empty($summary_data))

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"></h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">

                            </div>
                            <div class="col-lg-6">

                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                    </div>
                </div>
            @endif
        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">CLOSE</button>
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">SAVE AS DRAFT</button>
            <button type="button" class="btn btn-success btn-sm" data-dismiss="modal">SUBMIT</button>
        </div>
    </div>
</div>
