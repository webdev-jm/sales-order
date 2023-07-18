<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">FILTER</h3>
            <div class="card-tools" wire:loading.remove>
                <button class="btn btn-success btn-sm" wire:click.prevent="export"><i class="fa fa-file-excel mr-1"></i>Export</button>
            </div>
            <div class="card-tools" wire:loading>
                <button class="btn btn-success btn-sm"><i class="fa fa-spinner fa-spin mr-1"></i>Loading</button>
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                {{-- YEAR --}}
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Year</label>
                        <input type="number" class="form-control" wire:model="year">
                    </div>
                </div>
                {{-- MONTH --}}
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Month</label>
                        <select class="form-control" wire:model="month">
                            @foreach($months as $key => $mon)
                            <option value="{{$key}}">{{$mon}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                MCP OVERVIEW
                <i class="fa fa-spinner fa-spin ml-2 fa-sm" wire:loading></i>
            </h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" class="form-control float-right" placeholder="Search" wire:model="search">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr class="text-center">
                        <th>USER</th>
                        <th>MCP</th>
                        <th>VISITED</th>
                        <th>DEVIATION</th>
                        <th>SCHEDULE REQUEST</th>
                        <th>PERFORMANCE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedule_results as $result)
                        <tr>
                            <td>{{$result->name}}</td>
                            <td>{{$result->schedule_count}}</td>
                            <td>{{$user_data[$result->uid]['visited'] ?? 0}}</td>
                            <td>{{$user_data[$result->uid]['deviation'] ?? 0}}</td>
                            <td>{{$result->request_count ?? 0}}</td>
                            <td>{{number_format(($user_data[$result->uid]['visited'] / $result->schedule_count) * 100, 2)}}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$schedule_results->links()}}
        </div>
    </div>
</div>