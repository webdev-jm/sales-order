<div>

    <form wire:submit.prevent="checkFileData">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">UPLOAD MULTIPLE SO<i class="fa fa-spinner-fa-spin" wire:loading></i></h3>
                <div class="card-tools">
                    
                </div>
            </div>
            <div class="card-body">
                
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="">UPLOAD FILE</label>
                            <input type="file" class="form-control" wire:model.defer="so_file">
                        </div>
                    </div>
                </div>
        
            </div>
            <div class="card-footer text-right">
                <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">
                    <i class="fa fa-upload mr-1"></i>
                    UPLOAD
                </button>
            </div>
        </div>
    </form>

    @if(!empty($so_data))
    <div class="row">
        <div class="col-12 mb-3">
            <button class="btn btn-secondary">
                DRAFT ALL
            </button>
            <button class="btn btn-success">
                FINALIZE ALL
            </button>
        </div>

        @foreach($so_data as $po_number => $data)
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">CONTROL NUMBER: {{$success_data[$po_number]['control_number'] ?? 'N/A'}}</div>
                        <div class="card-tools">
                            @if(empty($success_data[$po_number]['control_number']))
                                <button class="btn btn-secondary" wire:click.prevent="saveSalesOrder('draft', '{{$po_number}}')">
                                    Save as Draft
                                </button>
                                <button class="btn btn-success" wire:click.prevent="saveSalesOrder('finalized', '{{$po_number}}')">
                                    Finalize
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">

                        @if(!empty($err_data[$po_number]))
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-danger pl-0">
                                        <ul class="mb-0">
                                            @foreach($err_data[$po_number] as $err)
                                                <li class="">{{$err}}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($success_data[$po_number]['message']))
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-success">
                                    {{$success_data[$po_number]['message']}}
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <h4>
                                    {{$success_data[$po_number]['control_number'] ?? 'N/A'}}
                                    <small class="float-right">
                                        @if(!empty($success_data[$po_number]['status']))
                                            <span class="badge {{$success_data[$po_number]['status'] == 'draft' ? 'bg-secondary' : 'bg-success'}}">
                                                {{$success_data[$po_number]['status']}}
                                            </span>
                                        @else
                                            <span class="badge badge-info">
                                                preview
                                            </span>
                                        @endif
                                    </small>
                                </h4>
                            </div>
                        </div>
                        
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                Ship to Address
                                <address>
                                    @if(!empty($data['shipping_address']))
                                        <strong>{{$data['shipping_address']['ship_to_name']}}</strong><br>
                                        {{$data['shipping_address']['building']}}<br>
                                        {{$data['shipping_address']['street']}}<br>
                                        {{$data['shipping_address']['city']}}<br>
                                        {{$data['shipping_address']['postal']}}
                                    @else
                                        <strong>{{$account->account_name}}</strong><br>
                                        {{$account->ship_to_address1}}<br>
                                        {{$account->ship_to_address2}}<br>
                                        {{$account->ship_to_address3}}<br>
                                        {{$account->postal_code}}
                                    @endif
                                </address>
                            </div>
                            
                            <div class="col-sm-4 invoice-col">
                                <b>PO Number: {{$po_number}}</b><br>
                                <b>PO Value:</b> {{number_format($data['po_value'])}}<br>
                                <b>Order Date:</b> {{date('Y-m-d')}}<br>
                                <b>Ship Date:</b> {{$data['ship_date']}}<br>
                                <b>Discount:</b> {{$account->discount->description ?? ''}}<br>
                                <b>Account:</b> [{{$account->account_code}}] {{$account->short_name}}
                            </div>
                    
                            <div class="col-sm-4 invoice-col">
                                <b>Reference:</b> <br>
                                <b>Shipping Instruction:</b><br>
                                <p>
                                    {{$data['shipping_instruction']}}
                                </p>
                            </div>
                        </div>
                        
                        <div class="row">
                            
                            <div class="col-12 table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-thead">
                                        <tr>
                                            <th>#</th>
                                            <th class="align-middle">Stock Code</th>
                                            <th class="align-middle">Description</th>
                                            <th class="align-middle">Unit</th>
                                            <th class="align-middle">Quantity</th>
                                            <th class="align-middle">Total</th>
                                            <th class="align-middle">Total less discount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $num = 0;
                                            $quantity_total = 0;
                                            $sales_total = 0;
                                            $sales_total_less_disc = 0;
                                        @endphp
                                        @foreach($data['lines'] as $key => $val)
                                            @php
                                                $num++;
                                            @endphp
                                            <tr>
                                                <td class="align-middle text-center">{{$num}}</td>
                                                <td class="align-middle">{{$val['product']['stock_code']}}</td>
                                                <td class="align-middle">{{$val['product']['description']}} [{{$val['product']['size']}}]</td>
                                                <td>{{$val['uom']}}</td>
                                                <td class="text-right">{{$val['quantity']}}</td>
                                                <td class="text-right">{{number_format($val['total'], 2)}}</td>
                                                <td class="text-right">{{number_format($val['total_less_discount'], 2)}}</td>
                                            </tr>
                                            @php
                                                $quantity_total += $val['quantity'];
                                                $sales_total += $val['total'];
                                                $sales_total_less_disc += $val['total_less_discount'];
                                            @endphp
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4">TOTAL</th>
                                            <th class="text-right">{{number_format($quantity_total)}}</th>
                                            <th class="text-right">{{number_format($sales_total, 2)}}</th>
                                            <th class="text-right">{{number_format($sales_total_less_disc, 2)}}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    
                        <div class="row">
                            <div class="col-lg-6">
                                <p class="lead">Order Summary</p>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th style="width:50%">Total:</th>
                                            <td class="text-right">{{number_format($sales_total, 2)}}</td>
                                        </tr>
                                        <tr>
                                            <th>Discount</th>
                                            <td class="text-right">{{$account->discount->description ?? ''}}</td>
                                        </tr>
                                        <tr>
                                            @php
                                                $discounted_total = $sales_total;
                                                if(!empty($data['discount'])) {
                                                    $discounts = [$data['discount']['discount_1'], $data['discount']['discount_2'], $data['discount']['discount_3']];

                                                    foreach ($discounts as $discountValue) {
                                                        if ($discountValue > 0) {
                                                            $discounted_total = $discounted_total * ((100 - $discountValue) / 100);
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <th>Total Less Discount</th>
                                            <td class="text-right">{{number_format($discounted_total, 2)}}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

</div>
