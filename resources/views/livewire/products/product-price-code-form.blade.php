<div>
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
                            <a href="" wire:click.prevent="add({{$i}})" class=""><i class="fa fa-plus text-info"></i></a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="p-0">
                            <input type="text" class="form-control text-center border-0">
                        </td>
                        <td class="p-0">
                            <input type="number" class="form-control text-center border-0">
                        </td>
                        <td class="p-0">
                            <input type="text" class="form-control text-center border-0">
                        </td>
                        <td class="p-0 align-middle text-center">
                            
                        </td>
                    </tr>
                    @foreach($inputs as $key => $value)
                    <tr>
                        <td class="p-0">
                            <input type="text" class="form-control text-center border-0">
                        </td>
                        <td class="p-0">
                            <input type="number" class="form-control text-center border-0">
                        </td>
                        <td class="p-0">
                            <input type="text" class="form-control text-center border-0">
                        </td>
                        <td class="p-0 align-middle text-center">
                            <a href="" wire:click.prevent="remove({{$key}})" class=""><i class="fa fa-trash-alt text-danger"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
</div>
