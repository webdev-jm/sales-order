<div class="card">
    <div class="card-header"><h3 class="card-title">Search Invoice</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Invoice Number</label>
                    <input type="text" class="form-control" wire:model.defer="invoice_number">
                </div>
            </div>
            </div>
    </div>
    <div class="card-footer text-right">
        <button class="btn btn-primary btn-sm" wire:click="searchInvoice">Search</button>
        <button class="btn btn-info btn-sm" wire:click="showSummary">
            {{ $show_summary ? 'HIDE' : 'SHOW' }} SUMMARY
        </button>
    </div>
</div>

@if($show_summary)
    @include('livewire.credit-memo.partials._summary_table')
@else
    @include('livewire.credit-memo.partials._results_table')
@endif
