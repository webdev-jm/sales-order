@extends('adminlte::page')

@section('title')
    Schedules - Requests
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Schedules / Requests</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('schedule.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-2"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['schedule.list'], 'id' => 'search_form']) !!}
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
                    <th>Branch</th>
                    <th>Date</th>
                    <th>Reschedule Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedules as $schedule)
                    @php
                        $status_colors = [
                            'for reschedule' => 'bg-warning',
                            'for deletion' => 'bg-danger',
                            'reschedule rejected' => 'bg-orange',
                            'rescheduled' => 'bg-teal',
                            'deletion rejected' => 'bg-maroon',
                            'deletion approved' => 'bg-olive',
                        ];
                    @endphp
                    <tr>
                        <td>{{$schedule->user->firstname}} {{$schedule->user->lastname}}</td>
                        <td>{{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}</td>
                        <td>{{$schedule->date}}</td>
                        <td>{{$schedule->reschedule_date}}</td>
                        <td>
                            @if(!empty($schedule->status))
                            <span class="badge {{$status_colors[$schedule->status]}}">
                                {{$schedule->status}}
                            </span>
                            @else
                                @php
                                    $status = $schedule->approvals()->orderBy('id', 'DESC')->first()->status;
                                @endphp
                                <span class="badge {{$status_colors[$status]}}">
                                    {{$status}}
                                </span>
                            @endif
                        </td>
                        <td class="text-right">
                            @if($schedule->status == 'rescheduled' || $schedule->status == 'deletion approved' || empty($schedule->status))
                                <a href="#" title="details" class="btn-detail" data-id="{{$schedule->id}}"><i class="fa fa-info-circle text-primary"></i></a>
                            @elseif($schedule->status == 'for deletion')
                                <a href="#" title="approvals" class="btn-delete" data-id="{{$schedule->id}}"><i class="fa fa-wrench text-secondary mr-1"></i></a>
                            @elseif($schedule->status == 'for reschedule')
                                <a href="#" title="approvals" class="btn-reschedule" data-id="{{$schedule->id}}"><i class="fa fa-wrench text-secondary mr-1"></i></a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$schedules->links()}}
    </div>
</div>

<div class="modal fade" id="detail-modal">
    <div class="modal-dialog modal-lg">
        <livewire:schedules.schedule-detail/>
    </div>
</div>

<div class="modal fade" id="delete-modal">
    <div class="modal-dialog modal-lg">
        <livewire:schedules.schedule-delete/>
    </div>
</div>

<div class="modal fade" id="reschedule-modal">
    <div class="modal-dialog modal-lg">
        <livewire:schedules.schedule-change/>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '.btn-detail', function() {
            var id = $(this).data('id');
            Livewire.emit('setDetail', id);
            $('#detail-modal').modal('show');
        });

        $('body').on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            Livewire.emit('showDetail', id);
            $('#delete-modal').modal('show');
        });

        $('body').on('click', '.btn-reschedule', function() {
            var id = $(this).data('id');
            Livewire.emit('showChange', id);
            $('#reschedule-modal').modal('show');
        });
    })
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection