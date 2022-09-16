@extends('adminlte::page')

@section('title')
    Account Logins
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Account Logins</h1>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['account-login.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Accounts</h3>
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
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th>Short Name</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $account)
                <tr>
                    <td>{{$account->company->name}}</td>
                    <td>{{$account->account_code}}</td>
                    <td>{{$account->account_name}}</td>
                    <td>{{$account->short_name}}</td>
                    <td class="text-right">
                        <a href="{{route('account-login.show', $account->id)}}" title="Details"><i class="fas fa-eye text-primary mx-1"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$accounts->links()}}
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection