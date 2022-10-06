
<div class="modal-content">
    <div wire:loading.class="overlay" wire:loading>
        <i class="fas fa-2x fa-sync fa-spin"></i>
    </div>
    <div class="modal-header">
        <h4 class="modal-title">Assign Special Products</h4>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <input type="text" class="form-control" wire:model="search" placeholder="Search">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default" form="search_form">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-body">
        @if(!empty($account))
        <h4>[{{$account->account_code}}] {{$account->short_name}}</h4>
        <span>{{$account->account_name}}</span>

        <hr>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr class="text-center">
                        <th colspan="3">SPECIAL PRODUCTS</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Product</th>
                        <th>Price Code</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    @php
                        if(!isset($special_products[$product->id]['price_code'])) {
                            $special_products[$product->id]['price_code'] = $account->price_code;
                        }
                    @endphp
                    <tr>
                        <td class="p-0 text-center align-middle">
                            <div class="form-group m-0">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" wire:model.lazy="special_products.{{$product->id}}.product" id="switch{{$product->id}}" wire:change="saveChanges">
                                    <label class="custom-control-label" for="switch{{$product->id}}"></label>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">
                            {{$product->stock_code}} {{$product->description}} {{$product->size}}
                        </td>
                        <td class="align-middle">
                            @php
                                $price_codes = $product->price_codes()->where('company_id', $account->company_id)->get();
                            @endphp
                            <select class="form-control border-0" wire:model.lazy="special_products.{{$product->id}}.price_code" wire:change="saveChanges">
                                <option value=""></option>
                                @foreach($price_codes as $key => $price_code)
                                    <option value="{{$price_code->code}}" {{$price_code->code == $special_products[$product->id]['price_code'] ? 'selected="selected"' : ''}}>{{$price_code->code}}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col-12">
                {{$products->links()}}
            </div>
        </div>

        @endif
    </div>
    <div class="modal-footer text-right">
        <button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
    </div>
</div>
