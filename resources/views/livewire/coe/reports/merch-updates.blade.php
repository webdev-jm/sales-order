<div>
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">MERCH UPDATES <i class="fa fa-circle-notch fa-spin" wire:loading></i></h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-stripe table-sm">
                <thead>
                    <tr class="text-center bg-dark text-white">
                        <th class="align-middle">NAME</th>
                        <th class="align-middle">HEADCOUNT ACTUAL</th>
                        <th class="align-middle">TARGET</th>
                        <th class="align-middle">DOORS ACTUAL</th>
                        <th class="align-middle">TARGET</th>
                        <th class="align-middle">% HC DEPLOYED</th>
                        <th class="align-middle">% DOORS MANNED</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $name => $val)
                    <tr class="text-center">
                        <td class="align-middle">{{$name}}</td>
                        <td class="align-middle">{{$val['actual']}}</td>
                        <td class="align-middle">{{$val['target']}}</td>
                        <td class="align-middle">{{$val['actual_doors']}}</td>
                        <td class="align-middle">{{$val['target_doors']}}</td>
                        <td class="align-middle">{{number_format($val['hc_deployed'])}}%</td>
                        <td class="align-middle">{{number_format($val['doors_manned'])}}%</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="text-center bg-dark text-white">
                        <th>TOTAL</th>
                        <th>{{$totals['actual'] ?? 0}}</th>
                        <th>{{$totals['target'] ?? 0}}</th>
                        <th>{{$totals['actual_doors'] ?? 0}}</th>
                        <th>{{$totals['target_doors'] ?? 0}}</th>
                        <th>{{number_format($totals['hc_deployed'])}}%</th>
                        <th>{{number_format($totals['doors_manned'])}}%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
