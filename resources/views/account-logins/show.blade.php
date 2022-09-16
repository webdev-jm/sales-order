@extends('adminlte::page')

@section('title')
    Account Logins - Details
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Account Logins / Details</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('account-login.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-2"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['account-login.show', $account->id], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">[<span class="font-weight-bold">{{$account->account_code}}</span>] {{$account->account_name}} <span class="text-muted">[{{$account->short_name}}]</span></h3>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Account Logins</h3>
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
                    <th>User</th>
                    <th>Longitude</th>
                    <th>Latitude</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($account_logins as $account_login)
                <tr>
                    <td>{{$account_login->user->firstname}} {{$account_login->user->lastname}}</td>
                    <td>{{$account_login->longitude}}</td>
                    <td>{{$account_login->latitude}}</td>
                    <td>{{$account_login->time_in}}</td>
                    <td>{{$account_login->time_out}}</td>
                    <td class="text-right">
                        <a href="" class="btn btn-info btn-sm btn-activity" data-id="{{$account_login->id}}"><i class="fa fa-list mr-2"></i>activities</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$account_logins->links()}}
    </div>
</div>

<div class="modal fade" id="modal-activities">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <livewire:account-logins.account-login-activities/>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({
                alwaysShowClose: true
            });
        });
        
        $('body').on('click', '.btn-activity', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('showActivities', id);
            $('#modal-activities').modal('show');
        });
    })
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection