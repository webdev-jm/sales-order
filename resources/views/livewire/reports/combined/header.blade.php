<div>

    <div class="container mb-2">
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4">
                <select class="form-control text-center" wire:model="user_id">
                    <option value="">Select User</option>
                    @foreach($user_options as $val => $option)
                    <option value="{{$val}}">{{$option}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-1">
            <ul class="pagination pagination-month justify-content-center mb-0">
                <li class="page-item">
                    <a href="" class="page-link" wire:click.prevent="selectYear({{$prev_year}})">«</a>
                </li>
                @foreach($months as $key => $mon)
                <li class="page-item{{$key == $month && $selected_year == $year ? ' active' : ''}}">
                    <a href="" class="page-link" wire:click.prevent="selectDate('{{$key}}', {{$year}})">
                        <p class="page-month">{{$mon}}</p>
                        <p class="page-year">{{$year}}</p>
                    </a>
                </li>
                @endforeach
                <li class="page-item">
                    <a href="" class="page-link" wire:click.prevent="selectYear({{$next_year}})">»</a>
                </li>
            </ul>
            <div class="container text-center">
                <h4 class="mb-0 mt-2 text-uppercase font-weight-bold">{{date('F Y', strtotime($selected_year.'-'.$month.'-01'))}}</h4>
                @if(!empty($user_id) && auth()->user()->can('report export'))
                    <a href="{{route('report.combined-print', [$user_id, $selected_year, $month])}}" class="btn btn-danger" target="_blank"><i class="fa fa-file-pdf mr-1"></i>Print to PDF</a>
                @endif
            </div>
        </div>
    </div>
</div>
