@extends('adminlte::page')

@section('title')
    Dashboard
@endsection

@section('css')
<style>
    .small-box .inner {
        border: solid 3px rgb(98, 98, 98);
    }
    .small-box:hover .inner {
        border: solid 3px rgb(76, 145, 255);
        cursor: pointer;
    }
    .h-90 {
        height: 90% !important;
    }
</style>
@endsection

@section('content_header')
    <h1>Dashboard</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Dashboard</h3>
    </div>
    <div class="card-body">

        <div class="row">
            @foreach($accounts as $account)
            <div class="col-lg-3">
                <div class="small-box bg-secondary h-90">
                    <div class="inner h-100">
                        <h3>{{$count_data[$account->id]}}</h3>
      
                        <b>{{$account->account_code}}</b>
                        <p class="mb-0">{{$account->account_name}}</p>
                        <span>{{$account->short_name}}</span>
                    </div>
                    <div class="icon">
                        <i class="fa fa-cubes"></i>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
    <div class="card-footer">
        {{$accounts->links()}}
    </div>
</div>
@endsection

@section('js')
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
