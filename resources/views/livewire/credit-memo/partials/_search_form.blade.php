<div class="card">
    <div class="card-header"><h3 class="card-title">Search Invoice</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-3 form-group">
                <label>Invoice Number</label>
                <input type="text" class="form-control" wire:model.defer="invoice_number">
                <small class="text-danger">{{ $errors->first('invoice_number') }}</small>
            </div>
            <div class="col-lg-3 form-group">
                <label>SO Number</label>
                <input type="text" class="form-control" wire:model.defer="so_number">
            </div>
            <div class="col-lg-3 form-group">
                <label>PO Number</label>
                <input type="text" class="form-control" wire:model.defer="po_number">
            </div>
            <div class="col-lg-3 form-group">
                <label>Year</label>
                <input type="number" class="form-control" wire:model.defer="year">
            </div>
            <div class="col-lg-3 form-group">
                <label>Month</label>
                <select class="form-control" wire:model.defer="month">
                    <option value="">- select -</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{$i}}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-lg-3">
                 <livewire:search-dropdown
                    model="App\Models\Account"
                    :search-fields="['account_code', 'short_name', 'account_name']"
                    :display-field="['account_code', 'short_name']"
                    value-field="id"
                    label="Customer Account"
                    emit-event="accountSelected"
                    :selected="$account_id"
                />
            </div>
        </div>
    </div>
    <div class="card-footer text-right">
        <button class="btn btn-primary btn-sm" wire:click.prevent="searchInvoice" wire:loading.attr="disabled">
            <i class="fa fa-search mr-1"></i> Search
        </button>
        <button class="btn btn-info btn-sm" wire:click.prevent="showSummary">
            {{ $show_summary ? 'HIDE' : 'SHOW' }} SUMMARY
        </button>
    </div>
</div>
