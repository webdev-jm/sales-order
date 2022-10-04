<div class="d-inline">
    @if($sales_order->reference != '' && $sales_order->upload_status == 0)
        @if($confirmation)
            Clear status?
            <button wire:click.prevent="selectYes" class="btn btn-sm text-success"><i class="fa fa-check mr-1"></i>Yes</button>
            <button wire:click.prevent="selectNo" class="btn btn-sm text-danger"><i class="fa fa-times mr-1"></i>No</button>
        @else
            <a href="#"><i class="fa fa-cogs text-warning" wire:click.prevent="changeStatus"></i></a>
        @endif
    @endif
</div>
