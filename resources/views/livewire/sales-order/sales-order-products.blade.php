<div>
    <div class="card shadow-sm">
        <div class="card-header bg-success">
            <h3 class="card-title">PRODUCTS <i class="fa fa-sm fa-circle-notch fast-spin" wire:loading></i></h3>
            <div class="card-tools">
                <div class="row">
                    <div class="col">
                        <div class="input-group input-group-sm">
                            <select name="" class="form-control form-control-sm" wire:model="brand">
                                <option value="ALL">ALL</option>
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
            <table class="table table-sm table-bordered table-hover">
                <thead class="bg-secondary">
                    <tr>
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
                    @endphp
                    <tr>
                        <td class="align-middle px-2">
                            <span class="font-weight-bold">[{{$product->stock_code}}]</span>
                            <span>{{$product->description}} </span>
                            <span class="text-muted">[{{$product->size}}]</span>
                        </td>
                        <td class="align-middle text-center px-1">
                            {{$product->category}}
                        </td>
                        <td class="align-middle text-center px-1">
                            {{$product->brand}}
                        </td>
                        <td class="p-0 align-middle">
                            <select class="form-control border-0 px-1" wire:change="change" wire:model="uom.{{$product->id}}">
                                @foreach($uom_arr as $key => $val)
                                <option value="{{$val}}" {{$val == $product->order_uom ? 'selected' : ''}}>{{$val}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="p-0 align-middle">
                            <input type="number" class="form-control border-0" min="0" wire:loading.attr="disabled" wire:change="change" wire:model.lazy="quantity.{{$product->id}}.{{$uom[$product->id] ?? $product->order_uom}}">
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
