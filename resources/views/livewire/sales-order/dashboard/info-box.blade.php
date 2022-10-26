<div>
    <div class="row">
        {{-- total sales order --}}
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fa fa-clipboard-list"></i></span>
    
                <div class="info-box-content">
                    <span class="info-box-text">Sales Orders</span>
                    <span class="info-box-number">
                        <span wire:loading.remove>{{number_format($sales_orders_count)}}</span>
                        <i class="fa fa-spinner fa-spin" wire:loading></i>
                    </span>
                </div>
            </div>
        </div>
    
        {{-- grand total --}}
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fa fa-coins"></i></span>
    
                <div class="info-box-content">
                    <span class="info-box-text">Grand Total</span>
                    <span class="info-box-number">
                        <span wire:loading.remove>{{number_format($grand_total, 2)}}</span>
                        <i class="fa fa-spinner fa-spin" wire:loading></i>
                    </span>
                </div>
            </div>
        </div>
    
        {{-- grand total less discount --}}
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fa fa-percent"></i></span>
    
                <div class="info-box-content">
                    <span class="info-box-text">Grand Total Less Discount</span>
                    <span class="info-box-number">
                        <span wire:loading.remove>{{number_format($grand_total_discounted, 2)}}</span>
                        <i class="fa fa-spinner fa-spin" wire:loading></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
