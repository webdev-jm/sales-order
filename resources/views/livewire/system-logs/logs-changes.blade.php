<div>
    <div class="modal-header">
        <h4 class="modal-title">System Log Detail</h4>
        <div class="card-tools" wire:loading>
            <i class="fa fa-circle-notch fa-spin"></i>
        </div>
    </div>
    <div class="modal-body">

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Old</th>
                        <th>Changes</th>
                    </tr>
                </thead>
                <tbody>
                    @isset($changes)
                        @foreach($changes as $key => $detail)
                        <tr>
                            <td>{{$key}}</td>
                            <td>{{$detail['old']}}</td>
                            <td>{{$detail['update']}}</td>
                        </tr>
                        @endforeach
                    @endisset
                </tbody>
            </table>
        </div>

    </div>
    <div class="modal-footer text-right">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
</div>
