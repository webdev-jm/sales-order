@extends('adminlte::page')

@section('title')
    Schedules - Requests
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

<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">List of Requests</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm">
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
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Branch</th>
                    <th class="text-nowrap">Date</th>
                    <th class="text-nowrap">Reschedule Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $status_colors = [
                        'for reschedule'             => 'bg-warning',
                        'for deletion'               => 'bg-danger',
                        'reschedule rejected'        => 'bg-orange',
                        'rescheduled'                => 'bg-teal',
                        'deletion rejected'          => 'bg-maroon',
                        'deletion approved'          => 'bg-olive',
                        'schedule request rejected'  => 'bg-purple',
                        'schedule request approved'  => 'bg-lime',
                        'schedule request'           => 'bg-success',
                        'for deviation'              => 'bg-primary',
                        'deviated'                   => 'bg-warning',
                    ];
                @endphp
                @foreach($schedules as $schedule)
                    <tr>
                        <td>{{$schedule->user->fullName()}}</td>
                        <td>{{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}</td>
                        <td class="text-nowrap">{{$schedule->date}}</td>
                        <td class="text-nowrap">{{$schedule->reschedule_date}}</td>
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
                        <td class="text-right text-nowrap">
                            @if($schedule->status == 'for deletion')
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete mr-1"
                                        data-id="{{$schedule->id}}" title="Approve Deletion">
                                    <i class="fa fa-wrench"></i>
                                </button>
                            @elseif($schedule->status == 'for reschedule')
                                <button type="button" class="btn btn-sm btn-outline-warning btn-reschedule mr-1"
                                        data-id="{{$schedule->id}}" title="Approve Reschedule">
                                    <i class="fa fa-wrench"></i>
                                </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-primary btn-detail"
                                    data-id="{{$schedule->id}}" title="View History">
                                <i class="fa fa-info-circle"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                @if($schedules->isEmpty())
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                            No schedule requests found.
                        </td>
                    </tr>
                @endif
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
