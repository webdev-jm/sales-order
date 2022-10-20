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
{!! Form::open(['method' => 'GET', 'route' => ['dashboard'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Dashboard</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                {!! Form::text('search', $search, ['class' => 'form-control float-right', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default" form="search_form">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">

        <div class="row">
            @foreach($accounts as $account)
            <div class="col-lg-3">
                <div class="small-box bg-secondary h-90 account-card" data-id="{{$account->id}}">
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

<div class="modal fade" id="modal-account-report">
    <div class="modal-dialog modal-lg">
        <livewire:dashboard.account-report/>
    </div>
</div>
@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '.account-card', function() {
            var id = $(this).data('id');
            Livewire.emit('setAccount', id);
            $('#modal-account-report').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
