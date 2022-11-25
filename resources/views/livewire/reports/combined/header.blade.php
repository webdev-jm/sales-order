<div>

    <div class="container mb-2">
        <select class="form-control text-center" wire:model="user_id">
            <option value="">Select User</option>
            @foreach($user_options as $val => $option)
            <option value="{{$val}}">{{$option}}</option>
            @endforeach
        </select>
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
            </div>
        </div>
    </div>
</div>
