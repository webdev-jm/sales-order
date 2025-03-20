<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PO SEARCH</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3">
                    <label for="po_number">PO NUMBER</label>
                    <input type="text" class="form-control" wire:model="po_search" placeholder="Search po number">
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" wire:click.prevent="searchPO">
                <i class="fa fa-search"></i>
                SEARCH
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Invoices</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group mb-0">
                        <label for="upload_file">UPLOAD PO NUMBERS</label>
                        <input type="file" class="form-control" wire:model="upload_file">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" wire:click.prevent="uploadFile" wire:loading.attr="disabled">
                <i class="fa fa-upload"></i>
                UPLOAD
            </button>
        </div>
    </div>    

    @if(!empty($po_data))
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PO DETAILS</h3>
            <div class="card-tools">
                <button class="btn btn-success btn-xs" wire:click.prevent="DownloadData" wire:loading.attr="disabled">
                    <i class="fa fa-download"></i>
                    DOWNLOAD
                </button>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>PO NUMBER</th>
                        <th>SYSTEM PO NUMBER</th>
                        <th>ACCOUNT CODE</th>
                        <th>INVOICE</th>
                        <th>SALES ORDER</th>
                        <th>ORDER DATE</th>
                        <th>INVOICE DATE</th>
                        <th>POD DATE</th>
                        <th>VALUE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($po_data as $po_number => $data)
                        @if(!empty($data))
                            @foreach($data as $invoice)
                                <tr>
                                    <td>{{$po_number}}</td>
                                    <td>{{$invoice['po_number']}}</td>
                                    <td>{{$invoice['account_code']}}</td>
                                    <td>{{$invoice['invoice']}}</td>
                                    <td>{{$invoice['sales_order']}}</td>
                                    <td>{{$invoice['order_date']}}</td>
                                    <td>{{$invoice['invoice_date']}}</td>
                                    <td>{{$invoice['pod_date']}}</td>
                                    <td>{{number_format($invoice['currency_value'], 2)}}</td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
