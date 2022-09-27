@extends('adminlte::page')

@section('title')
    System Logs
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>System Logs</h1>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['system-logs'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">System Logs</h3>
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
                    <th>Action</th>
                    <th>User</th>
                    <th>Message</th>
                    <th>Timestamp</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities as $activity)
                <tr>
                    <td>{{$activity->log_name}}</td>
                    <td>{{$activity->causer->firstname}} {{$activity->causer->lastname}}</td>
                    <td>{{$activity->description}}</td>
                    <td>{{$activity->created_at->diffForHumans()}}</td>
                    <td class="text-right">
                        @if($activity->log_name == 'update')
                            <a href="#" class="btn-show-changes btn-changes" data-id="{{$activity->id}}"><i class="fa fa-exchange-alt"></i></a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$activities->links()}}
    </div>
</div>

<div class="modal fade" id="modal-changes">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <livewire:system-logs.logs-changes/>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '.btn-changes', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('showChanges', id);
            $('#modal-changes').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection