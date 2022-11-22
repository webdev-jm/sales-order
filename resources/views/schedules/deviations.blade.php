@extends('adminlte::page')

@section('title')
    Schedules - Deviations
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Schedules / Deviations</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('schedule.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-2"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['schedule.deviations'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Requests</h3>
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
                    <th>Cost Center</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($deviations as $deviation)
                <tr>
                    <td>{{$deviation->user->fullName()}}</td>
                    <td>{{$deviation->cost_center}}</td>
                    <td>{{$deviation->date}}</td>
                    <td>
                        <span class="badge badge-{{$status_arr[$deviation->status]}}">{{$deviation->status}}</span>
                    </td>
                    <td>{{$deviation->created_at->diffForHumans()}}</td>
                    <td class="text-right">
                        <a href="{{route('schedule.deviation-print', $deviation->id)}}" title="print" target="_blank" class="mr-1"><i class="fa fa-print text-success"></i></a>
                        <a href="#" title="view" class="btn-detail" data-id="{{$deviation->id}}"><i class="fa fa-eye text-primary"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$deviations->links()}}
    </div>
</div>

<div class="modal fade" id="deviation-approval-modal">
    <div class="modal-dialog modal-xl">
        <livewire:schedules.schedule-deviation-approval/>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '.btn-detail', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('setDeviationApproval', id);
            $('#deviation-approval-modal').modal('show');
        });
    })
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection