@extends('adminlte::page')

@section('title')
    Notifications
@endsection

@section('css')
@endsection

@section('content_header')
    <h1>Notifications</h1>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['notifications'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Users</h3>
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
                    <th>Module</th>
                    <th>Message</th>
                    <th>Timestamp</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($notifications as $notification)
                    <tr>
                        <td>{{$notification->data['module']}}</td>
                        <td>
                            <a href="{{$notification->data['url']}}">
                                {{$notification->data['message']}}
                            </a>
                        </td>
                        <td>{{$notification->created_at->diffForHumans()}}</td>
                        <td>
                            @if(empty($notification->read_at))
                            <i class="fa fa-circle text-danger"></i>
                            @endif
                        </td>
                    </tr>
                    @php
                        $notification->markAsRead();
                    @endphp
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$notifications->links()}}
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
