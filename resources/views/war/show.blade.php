@extends('adminlte::page')

@section('title')
    Weekly Productivity Reports - Form
@endsection

@section('css')
<style>
    .w200 {
        width: 200px !important; 
    }
    .w300 {
        width: 300px !important;
    }
    .war-title {
        font-size: 25px;
    }
    .war-label {
        background-color: rgb(202, 202, 202);
    }

    .report-table th, .report-table td {
        border: 1.5px solid black !important;
    }
    .section-header {
        background-color: black;
        color: white;
    }

    .min-h-100 {
        min-height: 100px !important;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>Weekly Productivity Reports / Details <span class="badge badge-{{$status_arr[$weekly_activity_report->status]}}">{{$weekly_activity_report->status}}</span></h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('war.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        <a href="{{route('war.print-pdf', $weekly_activity_report->id)}}" class="btn btn-primary" target="_blank"><i class="fa fa-print mr-1"></i>Print</a>
    </div>
</div>
@endsection

@section('content')
@if($weekly_activity_report->status == 'submitted')
    {!! Form::open(['method' => 'POST', 'route' => ['war.approval', $weekly_activity_report->id], 'id' => 'war_approval']) !!}
    {!! Form::hidden('status', $weekly_activity_report->status, ['form' => 'war_approval', 'id' => 'status']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Approval</h3>
            <div class="card-tools">
                @if((in_array(auth()->user()->id, $supervisor_ids) && auth()->user()->can('war approve')) || auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin'))
                    {!! Form::submit('Approve', ['class' => 'btn btn-success btn-sm btn-approval', 'form' => 'war_approval']) !!}
                    {!! Form::submit('Reject', ['class' => 'btn btn-danger btn-sm btn-approval', 'form' => 'war_approval']) !!}
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                {!! Form::label('remarks', 'Remarks') !!}
                {!! Form::textarea('remarks', '', ['class' => 'form-control'.($errors->has('remarks') ? ' is-invalid' : ''), 'form' => 'war_approval', 'rows' => 3]) !!}
                @if($errors->has('remarks'))
                <p class="text-danger">{{$errors->first('remarks')}}</p>
                @endif
            </div>
        </div>
    </div>
@elseif($weekly_activity_report->status != 'draft')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Approval History</h3>
        </div>
        <div class="card-body p-0 table-reponsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weekly_activity_report->approvals as $approval)
                    <tr>
                        <td>{{$approval->user->fullName()}}</td>
                        <td>
                            <span class="badge badge-{{$status_arr[$approval->status]}}">{{$approval->status}}</span>
                        </td>
                        <td>{{$approval->remarks}}</td>
                        <td>{{$approval->created_at->diffForHumans()}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Weekly Productivity Report Form</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm report-table">
            <thead>
                <tr>
                    <th class="w200 text-center align-middle px-0">
                        <img src="{{asset('/assets/images/bevi-logo.png')}}" alt="bevi logo">
                    </th>
                    <th class="text-center align-middle war-title" colspan="10">WEEKLY ACTIVITY REPORT</th>
                    <th class="w300 align-top" colspan="3">
                        DATE SUBMITTED: <br>
                        <p class="text-center mb-0 mt-2">{{$weekly_activity_report->date_submitted}}</p>
                    </th>
                </tr>
                {{-- space --}}
                <tr>
                    <th class="border-0" colspan="12"></th>
                </tr>
            </thead>
            <tbody>
                {{-- header --}}
                    <tr>
                        <th class="war-label">NAME:</th>
                        <td colspan="6" class="px-3">{{$weekly_activity_report->user->fullName()}}</td>
    
                        {{-- space --}}
                        <td class="border-0" colspan="3"></td>
    
                        <th class="war-label">DATE:</th>
                        <td class="px-3 align-middle">
                            {{$weekly_activity_report->date_from}}
                        </td>
                        <td class="text-center">to</td>
                        <td class="px-3 align-middle">
                            {{$weekly_activity_report->date_to}}
                        </td>
                    </tr>
                    <tr>
                        <th class="war-label">AREA:</th>
                        <td colspan="6" class="px-3 align-middle">
                            [{{$weekly_activity_report->area->area_code}}] {{$weekly_activity_report->area->area_name}}
                        </td>
    
                        {{-- space --}}
                        <td class="border-0" colspan="3"></td>
    
                        <th class="war-label">WEEK:</th>
                        <td colspan="3" class="px-3 align-middle">
                            Week {{$weekly_activity_report->week_number}}
                        </td>
                    </tr>
                {{-- spacing --}}
                    <tr>
                        <th class="border-0" colspan="14"></th>
                    </tr>
                {{-- objectives --}}
                    <tr>
                        <th class="align-middle war-label" colspan="14">I. OBJECTIVE/S</th>
                    </tr>
                    <tr>
                        <td class="px-3" colspan="14">
                            <p class="min-h-100">
                                {{$weekly_activity_report->objectives()->first()->objective}}  
                            </p>
                        </td>
                    </tr>
                {{-- areas --}}
                    <tr>
                        <th class="align-middle war-label pr-1" colspan="14">
                            II. AREAS
                        </th>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="2">DATE</th>
                        <th colspan="2">DAY</th>
                        <th colspan="3">AREA COVERED</th>
                        <th colspan="3">IN/OUT BASE</th>
                        <th colspan="4">ACTIVITIES/REMARKS</th>
                    </tr>
                    @if(!empty($weekly_activity_report->areas))
                        @foreach($weekly_activity_report->areas as $area)
                        <tr class="line-row areas">
                            <td colspan="2" class="align-middle text-center">
                                {{$area->date}}
                            </td>
                            <td colspan="2" class="align-middle text-center">
                                {{$area->day}}
                            </td>
                            <td colspan="3" class="align-middle text-center">
                                {{$area->location}}
                            </td>
                            <td colspan="3" class="align-middle text-center">
                                {{$area->in_base}}
                            </td>
                            <td colspan="4">
                                {{$area->remarks}}
                                <a href="" class="mx-1 btn-area-modal float-right" data-date="{{$area->date}}"><i class="fa fa-info-circle text-info"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    @else
                    <tr class="line-row areas">
                        <td colspan="14" class="text-center">NO DATA</td>
                    </tr>
                    @endif
                {{-- Highlights --}}
                    <tr>
                        <th class="align-middle war-label" colspan="14">III. Highlight(s) of week’s field visit (use 2nd page for more highlights when necessary):</th>
                    </tr>
                    <tr>
                        <td class="px-3" colspan="14">
                            <p class="min-h-100">
                                {{$weekly_activity_report->highlights}}
                            </p>
                        </td>
                    </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="area-activity-modal">
    <div class="modal-dialog modal-lg">
        <livewire:war.war-area-detail :user_id="$weekly_activity_report->user->id"/>
    </div>
</div>
@endsection

@section('js')
<script>
    $(function() {
        // set status
        $('body').on('click', '.btn-approval', function(e) {
            e.preventDefault();
            var val = $(this).val();
            var status = 'submitted';
            if(val == 'Approve') {
                status = 'approved';
            } else {
                status = 'rejected';
            }

            $('#status').val(status);
            $('#'+$(this).attr('form')).submit();
        });

        // branch login details
        $('body').on('click', '.btn-area-modal', function(e) {
            e.preventDefault();
            var date = $(this).data('date');
            Livewire.emit('setDate', date);
            $('#area-activity-modal').modal('show');
        });

        $('[data-toggle="tooltip"]').tooltip();

        $('body').on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({
                alwaysShowClose: true
            });
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
