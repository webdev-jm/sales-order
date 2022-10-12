<div>
    <div class="card shadow-sm">
        <div class="card-header bg-success">
            <h3 class="card-title">PRODUCTS <i class="fa fa-sm fa-circle-notch fast-spin" wire:loading></i></h3>
            <div class="card-tools mt-3">
                <div class="row">
                    <div class="col">
                        <div class="input-group input-group-sm">
                            <select name="" class="form-control form-control-sm" wire:model="brand">
                                <option value="ALL">ALL BRANDS</option>
                                @foreach($brands as $brand)
                                <option value="{{$brand->brand}}">{{$brand->brand}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <input type="text" name="table_search" wire:model="search" class="form-control float-right" placeholder="Search">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search" wire:loading.remove></i>
                                    <i class="fa fa-spinner fa-sm fa-spin" wire:loading></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-bordered">
                <thead class="bg-secondary">
                    <tr>
                        <th></th>
                        <th>Product</th>
                        <th class="text-center">Category</th>
                        <th class="text-center">Brand</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Order</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    @php
                        $uom_arr = [
                            $product->order_uom,
                            $product->stock_uom,
                            $product->other_uom
                        ];
                        $uom_arr = array_unique($uom_arr);

                        $reference = $product->references()->where('account_id', $account->id)->first();
                        $price_code = $product->price_codes()->where('company_id', $this->account->company_id)->where('code', $this->account->price_code)->first();
                    @endphp
                    <tr>
                        <td class="p-0 text-center align-middle">
                            @if(empty($product->status))
                                <i class="fa fa-circle text-success rounded-circle" title="Active"></i>
                            @elseif($product->status == 'F')
                                <i class="fa fa-circle text-danger rounded-circle" title="Hold"></i>
                            @else
                                <i class="fa fa-circle text-warning rounded-circle" title="Partial Hold"></i>
                            @endif
                        </td>
                        <td class="align-middle px-2">
                            <span class="font-weight-bold">[{{$product->stock_code}}]</span>
                            @if(isset($reference->description) && trim($reference->description) != '')
                                <span>{{$reference->description}}</span>
                            @else
                                <span>{{$product->description}}</span>
                            @endif
                            <span class="text-muted">[{{$product->size}}]</span>
                            @if(!empty($reference))
                                <br>
                                <span class=""><i class="fa fa-barcode mr-2"></i>{{$reference->account_reference}}</span>
                            @endif
                        </td>
                        <td class="align-middle text-center px-1">
                            {{$product->category}}
                        </td>
                        <td class="align-middle text-center px-1">
                            {{$product->brand}}
                        </td>
                        <td class="p-0 align-middle">
                            <select class="form-control border-0 px-1 w100" wire:change="change" wire:model="uom.{{$product->id}}">
                                @foreach($uom_arr as $key => $val)
                                    <option value="{{$val}}" {{$val == $product->order_uom ? 'selected' : ''}}>{{$val}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="p-0 align-middle{{!empty($product->status) || empty($price_code) ? ' bg-disabled' : ''}}" wire:loading.class="bg-disabled">
                            <input type="number" class="form-control border-0 w150{{!empty($product->status) || empty($price_code) ? ' text-center' : ''}}"
                                min="0" 
                                wire:loading.attr="disabled" 
                                max="99999999999" 
                                wire:change="change"
                                wire:model.lazy="quantity.{{$product->id}}.{{$uom[$product->id] ?? $product->order_uom}}" 
                                {{!empty($product->status) || empty($price_code) ? 'disabled' : ''}} 
                                placeholder="{{empty($price_code) ? 'no price code' : ''}}"
                            >
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer pb-0 px-2">
            {{$products->links()}}
        </div>
    </div>
</div>
