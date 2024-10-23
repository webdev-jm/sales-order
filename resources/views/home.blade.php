@extends('adminlte::page')

@section('title')
    Home
@endsection

@section('css')
<style>
    .h-90 {
        height: 89% !important;
    }
    .h-80 {
        height: 84% !important;
    }

    .form-underline {
        border-top: 0px;
        border-left: 0px;
        border-right: 0px;
    }
    .readonly {
        background-color: rgb(242, 249, 249) !important;
    }
    .form-underline.is-invalid {
        border-color: red;
    }
</style>
@endsection

@section('content_header')
    <h1>Home</h1>
@endsection

@section('content')

@if(!empty($logged_account))
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Activities</h3>
    </div>
    <div class="card-body">
        <a href="{{route('sales-order.create')}}" class="btn btn-primary">Booking Order</a>
    </div>
</div>
@elseif(!empty($logged_branch))
    {{-- exempt bevi offices --}}
    @if(auth()->user()->coe && $logged_branch->branch->account_id != 241)
    <livewire:coe.form :logged_branch="$logged_branch"/>
    @else
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Activities</h3>
            </div>
            <div class="card-body">
                <livewire:activities.activities :logged_branch="$logged_branch"/>
            </div>
        </div>
    @endif
@else
    <livewire:accounts.account-login/>
@endif
@endsection

@section('bsStepper', true)

@section('js')
<script>
    $(function() {
        // BS-Stepper Init
        document.addEventListener('DOMContentLoaded', function () {
            window.stepper = new Stepper(document.querySelector('.bs-stepper'))
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
