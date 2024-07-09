<div>
    
    <div class="card card-outline card-primary">
        <div class="card-body text-right py-1 px-1">
            <button class="btn btn-warning btn-sm" wire:click.prevent="saveAll('draft')" wire:loading.attr="disabled">
                DRAFT ALL
            </button>
            <button class="btn btn-info btn-sm" wire:click.prevent="saveAll('finalized')" wire:loading.attr="disabled">
                FINALIZE ALL
            </button>
        </div>
    </div>

    <div class="row">    
        @foreach($selectedPO as $po_id => $order)

        @if(!empty($alerts[$po_id]['success']))
            <div class="col-12">
                <div class="alert alert-success">
                    {{$alerts[$po_id]['success']}}
                </div>
            </div>
        @endif

        @if(!empty($alerts[$po_id]['error']))
            <div class="col-12">
                <div class="alert alert-danger">
                    {{$alerts[$po_id]['error']}}
                </div>
            </div>
        @endif

        <div class="col-12">

            <div class="invoice p-3 mb-3">
                <div class="row">
                    <div class="col-6">
                        <h4>
                            <strong>PO: </strong>{{$order['po_number']}}
                        </h4>
                    </div>
                    <div class="col-6 text-right">
                        @if(empty($order['control_number']))
                        <button class="btn btn-secondary btn-sm" wire:click.prevent="saveSO('draft', {{$po_id}})" wire:loading.attr="disabled">
                            SAVE AS DRAFT
                        </button>
                        <button class="btn btn-success btn-sm" wire:click.prevent="saveSO('finalized', {{$po_id}})" wire:loading.attr="disabled">
                            FINALIZE
                        </button>
                        @endif
                    </div>
                </div>
                
                <div class="row invoice-info">
                    <div class="col-sm-4 invoice-col">
                        Ship to Address
                        <button class="btn btn-primary btn-xs btn-select-address" data-id="{{$po_id}}">
                            SELECT
                        </button>
                        @if(!empty($order['selected_address']))
                            <address>
                                {!! $order['selected_address']['address_code'] ? '<span class="badge badge-success"><b>'.$order['selected_address']['address_code'].'</b></span>' : '' !!}
                                <a href="#" class="text-danger" wire:click.prevent="clearAddress({{$po_id}})">
                                    <u>
                                        clear
                                    </u>
                                </a>
                                <br>
                                {!! $order['selected_address']['ship_to_name'] ? '<span>'.$order['selected_address']['ship_to_name'].'</span><br>' : '' !!}
                                {!! $order['selected_address']['building'] ? '<span>'.$order['selected_address']['building'].'</span><br>' : '' !!}
                                {!! $order['selected_address']['street'] ? '<span>'.$order['selected_address']['street'].'</span><br>' : '' !!}
                                {!! $order['selected_address']['city'] ? '<span>'.$order['selected_address']['city'].'</span><br>' : '' !!}
                                {!! $order['selected_address']['tin'] ? '<span>'.$order['selected_address']['tin'].'</span><br>' : '' !!}
                                {!! $order['selected_address']['postal'] ? '<span>'.$order['selected_address']['postal'].'</span>' : '' !!}
                            </address>
                        @else
                            <address>
                                <span class="badge badge-secondary">DEFAULT</span><br>
                                {!! !empty(trim($logged_account->account->ship_to_address1)) ? '<span>'.$logged_account->account->ship_to_address1.'</span><br>' : '' !!}
                                {!! !empty(trim($logged_account->account->ship_to_address2)) ? '<span>'.$logged_account->account->ship_to_address2.'</span><br>' : '' !!}
                                {!! !empty(trim($logged_account->account->ship_to_address3)) ? '<span>'.$logged_account->account->ship_to_address3.'</span><br>' : '' !!}
                                {!! !empty(trim($logged_account->account->postal_code)) ? '<span>'.$logged_account->account->postal_code.'</span><br>' : '' !!}
                                {!! !empty(trim($logged_account->account->tax_number)) ? '<span>'.$logged_account->account->tax_number.'</span>' : '' !!}
                            </address>
                        @endif
                    </div>
                    
                    <div class="col-sm-4 invoice-col">
                        <b>Po Value:</b> {{number_format($po_value[$po_id]['total_net'], 2)}}<br>
                        <b>Approved Date:</b> {{$order['order_date']}}<br>
                        <b>Ship Date:</b> {{$order['ship_date']}}<br>
                        <b>Discount:</b> {{$logged_account->account->discount->description ?? ''}}<br>
                        <b>Account:</b> [{{$logged_account->account->account_code}}] {{$logged_account->account->short_name}}
                    </div>
            
                    <div class="col-sm-4 invoice-col">
                        <b>Control Number:</b> {{$order['control_number'] ?? ''}}<br>
                        <b>Shipping Instruction:</b><br>
                        <textarea rows="3" class="form-control" wire:model="selectedPO.{{$po_id}}.shipping_instruction"></textarea>
                    </div>
                </div>

                <hr class="my-0">
                <table class="table table-sm">
                    <thead>
                        <tr class="text-center">
                            <th style="width: 25px">
                            </th>
                            <th class="text-left">SKU CODE</th>
                            <th class="text-left">OTHER SKU CODE</th>
                            <th class="text-left">SKU DESCRIPTION</th>
                            <th class="text-left">UOM</th>
                            <th class="text-right">TOTAL</th>
                            <th class="text-right">QTY ORDERED</th>
                            <th class="text-right">TOTAL LESS DISCOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order['products'] as $product)
                            <tr>
                                <td class="p-0 align-middle text-center border-0" style="width: 25px">
                                    <input type="checkbox" {{empty(trim($product['product_name'])) ? 'disabled' : (!empty($checked[$po_id][$product['product_id']]) ? 'checked' : '')}} wire:model="checked.{{$po_id}}.{{$product['product_id']}}" value="1">
                                </td>
                                <td class="border-0 text-left{{empty(trim($product['product_name'])) ? ' text-red' : ''}}">{{$product['sku_code']}}</td>
                                <td class="border-0 text-left">{{$product['sku_code_other']}}</td>
                                <td class="border-0 text-left">{{$product['product_name']}}</td>
                                <td class="border-0 text-left">{{$product['unit_of_measure']}}</td>
                                <td class="text-right border-0">{{number_format($product['total'], 2)}}</td>
                                <td class="text-right border-0">{{number_format($product['quantity'])}}</td>
                                <td class="text-right border-0">{{number_format($product['total_less_discount'], 2)}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">TOTAL GROSS AMOUNT</th>
                            <th colspan="5" class="text-right">{{number_format($order['total_quantity'])}}</th>
                            <th class="text-right">{{number_format($po_value[$po_id]['total'], 2)}}</th>
                        </tr>
                        <tr>
                            <th class="border-0" colspan="2">DISCOUNT</th>
                            <th colspan="6" class="border-0 text-right">{{$logged_account->account->discount->description ?? ''}}</th>
                        </tr>
                        <tr>
                            <th class="border-0" colspan="2">TOTAL NET AMOUNT</th>
                            <th colspan="6" class="border-0 text-right">
                                {{number_format($po_value[$po_id]['total_net'], 2)}}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card card-outline card-primary">
        <div class="card-body text-right py-1 px-1">
            <button class="btn btn-warning btn-sm" wire:click.prevent="saveAll('draft')" wire:loading.attr="disabled">
                DRAFT ALL
            </button>
            <button class="btn btn-info btn-sm" wire:click.prevent="saveAll('finalized')" wire:loading.attr="disabled">
                FINALIZE ALL
            </button>
        </div>
    </div>

    <script>
        window.addEventListener('livewire:load', function() {
            $('body').on('click', '.btn-select-address', function(e) {
                console.log($(this).data('id'));
                Livewire.emit('selectAddress', $(this).data('id'));
                $('body').find('#po-address').modal('show');
            });
        });
    </script>
</div>
