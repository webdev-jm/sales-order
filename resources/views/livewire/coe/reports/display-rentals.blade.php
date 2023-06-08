<div>
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">DISPLAY RENTALS <i class="fa fa-circle-notch fa-spin" wire:loading></i></h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-stripe table-sm">
                <thead>
                    <tr class="text-center bg-dark text-white">
                        <th class="align-middle">ACCOUNT</th>
                        <th class="align-middle">ACTUAL VALIDATED</th>
                        <th class="align-middle">TARGET DOORS</th>
                        <th class="align-middle">%</th>
                        <th class="align-middle">% OF STOCKS</th>
                        <th class="align-middle">IMPLEMENTED</th>
                        <th class="align-middle">NOT IMPLEMENTED</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                    <tr class="text-center">
                        <td>{{$result->short_name}}</td>
                        <td>{{$result->actual}}</td>
                        <td>{{$result->target}}</td>
                        <td></td>
                        <td>{{$result->stocks_displayed}}</td>
                        <td>{{$result->implemented}}</td>
                        <td>{{$result->not_implemented}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
