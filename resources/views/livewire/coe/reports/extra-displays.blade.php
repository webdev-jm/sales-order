<div>
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">EXTRA DISPLAYS <i class="fa fa-circle-notch fa-spin" wire:loading></i></h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-stripe table-sm">
                <thead>
                    <tr class="text-center bg-dark text-white">
                        <th class="align-middle">ACCOUNT</th>
                        <th class="align-middle"># DOORS</th>
                        <th class="align-middle">RATE PER MONTH IF RENTED</th>
                        <th class="align-middle">VALUES OF STOCKS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                    <tr class="text-center">
                        <td>{{$result->short_name}}</td>
                        <td>{{$result->doors}}</td>
                        <td>{{$result->rate}}</td>
                        <td>{{$result->amount}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>