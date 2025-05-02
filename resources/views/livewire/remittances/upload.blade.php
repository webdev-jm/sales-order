<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">REMITTANCE</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="file">Upload file.</label>
                        <input type="file" class="form-control{{$errors->has('upload_file') ? ' is-invalid' : ''}}" wire:model="upload_file" multiple>
                        <small class="text-danger">{{$errors->first('upload_file')}}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" wire:click.prevent="checkFile">
                <i class="fa fa-upload"></i>
                CHECK
            </button>
        </div>
    </div>

    @if(!empty($remittance_data)) 
        @foreach($remittance_data as $remittance)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">REMITTANCE DETAILS</h3>
                    <div class="card-tools">
                        <button class="btn btn-success btn-sm">
                            <i class="fa fa-save"></i>
                            SAVE
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    
                    <div class="row">
                        <!-- HEADER -->
                        <div class="col-lg-6 mb-2">
                            <ul class="list-group">
                                @foreach($remittance['header'] as $title => $header)
                                    <li class="list-group-item py-2">
                                        <strong>{{$title}}</strong>
                                        <span class="float-right">{{$header}}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- DETAILS -->
                        <div class="col-lg-12 table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr class="text-center bg-dark">
                                        <th class="align-middle p-0">TRANSACTION TYPE</th>
                                        <th class="align-middle p-0">STORE</th>
                                        <th class="align-middle p-0">REFERENCE NO</th>
                                        <th class="align-middle p-0">DESCRIPTION</th>
                                        <th class="align-middle p-0">RC NUMBER</th>
                                        <th class="align-middle p-0">DATE</th>
                                        <th class="align-middle p-0">PO NUMBER</th>
                                        <th class="align-middle p-0">INVOICE NUMBER</th>
                                        <th class="align-middle p-0">INVOICE AMOUNT</th>
                                        <th class="align-middle p-0">DUE DATE</th>
                                        <th class="align-middle p-0">RC AMOUNT</th>
                                        <th class="align-middle p-0">EWT</th>
                                        <th class="align-middle p-0">NET AMOUNT</th>
                                        <th class="align-middle p-0">VAT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($remittance['details'] as $details)
                                        <tr class="text-center">
                                            @foreach($details as $col => $val)
                                                <td class="align-middle py-0 px-1{{ is_numeric($val) ? ' text-right' : ''}}">
                                                    {{ $val }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    @endif
</div>
