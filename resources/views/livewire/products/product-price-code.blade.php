<div class="d-inline">
    <a href="#" wire:click.prevent="managePriceCode" title="user accounts"><i class="fas fa-wrench text-secondary mx-1"></i></a>

    <div class="modal fade" id="assign-modal{{$product_id}}">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form wire:submit.prevent="savePriceCode">
                    <div class="modal-header">
                        <h4 class="modal-title">Price Codes<span class="badge badge-info ml-2">{{$product->stock_code}}</span></h4>
                    </div>
                    <div class="modal-body text-left">
                        
                        <div class="row">
                            @foreach($companies as $company)
                            <div class="col-lg-6 table-responsive">
                                <label>{{$company->name}}</label>
                                <table class="table table-bordered table-sm">
                                    <thead class="text-center">
                                        <tr>
                                            <th>Code</th>
                                            <th>Selling Price</th>
                                            <th>Price Basis</th>
                                            <th class="text-center align-middle p-2">
                                                <a href="" class="btn-add-row"><i class="fa fa-plus text-info"></i></a>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $price_codes = $company->price_codes()->where('product_id', $product_id)->get();
                                        @endphp

                                        @if($price_codes->count() > 0)
                                            @foreach($price_codes as $key => $price_code)
                                            <tr>
                                                <td class="p-0">
                                                    <input type="text" wire:model.defer="code.{{$company->id}}.{{$key}}" class="form-control text-center border-0">
                                                </td>
                                                <td class="p-0">
                                                    <input type="number" wire:model.defer="selling_price.{{$company->id}}.{{$key}}" class="form-control text-center border-0">
                                                </td>
                                                <td class="p-0">
                                                    <input type="text" wire:model.defer="price_basis.{{$company->id}}.{{$key}}" class="form-control text-center border-0">
                                                </td>
                                                <td class="p-0 align-middle text-center">
                                                    <a href="" class="btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @else
                                        <tr>
                                            <td class="p-0">
                                                <input type="text" wire:model.defer="code.{{$company->id}}.0" class="form-control text-center border-0">
                                            </td>
                                            <td class="p-0">
                                                <input type="number" wire:model.defer="selling_price.{{$company->id}}.0" class="form-control text-center border-0">
                                            </td>
                                            <td class="p-0">
                                                <input type="text" wire:model.defer="price_basis.{{$company->id}}.0" class="form-control text-center border-0">
                                            </td>
                                            <td class="p-0 align-middle text-center">
                                                <a href="" class="btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @endforeach
                        </div>
                
                    </div>
                    <div class="modal-footer text-right">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        window.addEventListener('openFormModal{{$product_id}}', event => {
            $("#assign-modal{{$product_id}}").modal('show');
        });

        window.addEventListener('closeFormModal{{$product_id}}', event => {
            $("#assign-modal{{$product_id}}").modal('hide');
        });
    </script>
</div>
