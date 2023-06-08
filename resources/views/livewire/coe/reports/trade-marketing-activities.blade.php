<div>
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">TRADE MARKETING ACTIVITIES <i class="fa fa-circle-notch fa-spin" wire:loading></i></h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-stripe table-sm">
                <thead>
                    <tr class="text-center bg-dark text-white">
                        <th class="align-middle">PROGRAM TITLE</th>
                        <th class="align-middle">DURATION</th>
                        <th class="align-middle">TYPES OF ACTIVITIES</th>
                        <th class="align-middle">PARTICIPATION SKU</th>
                        <th class="align-middle">ACTUAL</th>
                        <th class="align-middle">TARGET BASED ON MAXCAP</th>
                        <th class="align-middle">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                    <tr class="text-center">
                        <td>{{$result->title}}</td>
                        <td>{{$result->duration}}</td>
                        <td>{{$result->support_type}}</td>
                        <td>{{$result->sku}}</td>
                        <td>{{$result->actual}}</td>
                        <td>{{$result->target}}</td>
                        <td>{{number_format($result->percent)}}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer pb-0">
            {{$results->links()}}
        </div>
    </div>
</div>
