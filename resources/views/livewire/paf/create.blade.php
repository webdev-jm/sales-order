<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PAF HEADER</h3>
            <div class="card-tools">
                
            </div>
        </div>
        <div class="card-body">

            <div class="row">

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">ACCOUNT</label>
                        <select class="form-control form-control-sm">
                            @foreach($accounts as $account)
                                <option value="{{$account->id}}">[{{$account->account_code}}] {{$account->short_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer">
        </div>
    </div>
</div>
