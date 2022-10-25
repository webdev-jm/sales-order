<div>
    <ul class="pagination pagination-month justify-content-center">
        <li class="page-item"><a class="page-link" href="#" wire:click.prevent="selectDate({{$prev_year}}, {{$prev_month}})">«</a></li>
        <li class="page-item{{$month == '01' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '01')">
                <p class="page-month">Jan</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '02' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '02')">
                <p class="page-month">Feb</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '03' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '03')">
                <p class="page-month">Mar</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '04' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '04')">
                <p class="page-month">Apr</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '05' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '05')">
                <p class="page-month">May</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '06' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '06')">
                <p class="page-month">Jun</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '07' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '07')">
                <p class="page-month">Jul</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '08' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '08')">
                <p class="page-month">Aug</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '09' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '09')">
                <p class="page-month">Sep</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '10' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '10')">
                <p class="page-month">Oct</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '11' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '11')">
                <p class="page-month">Nov</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '12' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '12')">
                <p class="page-month">Dec</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item"><a class="page-link" href="#" wire:click.prevent="selectDate({{$next_year}}, {{$next_month}})">»</a></li>
    </ul>
</div>
