<div>
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">COMPETETIVE REPORTS <i class="fa fa-circle-notch fa-spin" wire:loading></i></h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-stripe table-sm">
                <thead>
                    <tr class="text-center bg-dark text-white">
                        <th class="align-middle">ACCOUNT NAME</th>
                        <th class="align-middle">COMPANY NAME</th>
                        <th class="align-middle">PRODUCT DESCRIPTION</th>
                        <th class="align-middle">SRP</th>
                        <th class="align-middle">TYPES OF PROMOTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                    <tr class="text-center">
                        <td>{{$result->short_name}}</td>
                        <td>{{$result->company_name}}</td>
                        <td>{{$result->product_description}}</td>
                        <td>{{$result->srp}}</td>
                        <td>{{$result->type_of_promotion}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$results->links()}}
        </div>
    </div>
</div>