<div>
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">Products</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" name="table_search" wire:model="search" class="form-control float-right" placeholder="Search">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-bordered">
                <thead class="bg-success">
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Order</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $num = 0;
                    @endphp
                    @foreach($products as $product)
                    @php
                        $num++;
                    @endphp
                    <tr>
                        <td class="align-middle">[{{$product->stock_code}}] {{$product->description}} {{$product->size}}</td>
                        <td class="align-middle text-center">{{$product->category}}</td>
                        <td class="align-middle text-center">{{$product->uom}}</td>
                        <td class="p-0 align-middle">
                            <input type="number" wire:model="quantity.{{$product->id}}" class="form-control border-0">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer pb-0">
            {{$products->links()}}
        </div>
    </div>
</div>
