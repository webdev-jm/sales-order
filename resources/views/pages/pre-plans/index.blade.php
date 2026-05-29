@extends('adminlte::page')

@section('title')
    Pre Plans
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Pre Plans</h1>
    </div>
    <div class="col-md-6 text-right">
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Pre Plans</h3>
        <div class="card-tools">
            <button class="btn btn-sm btn-success" id="btn-upload">
                <i class="fa fa-upload mr-1"></i>
                UPLOAD
            </button>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>PRE PLAN NUMBER</th>
                    <th>ACCOUNT</th>
                    <th>YEAR</th>
                    <th>START DATE</th>
                    <th>END DATE</th>
                    <th>TITLE</th>
                    <th>SUPPORT TYPE</th>
                    <th class="p-0"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($pre_plans as $pre_plan)
                    <tr>
                        <td>{{$pre_plan->pre_plan_number}}</td>
                        <td>{{$pre_plan->account->account_code ?? '-'}} {{$pre_plan->account->short_name}}</td>
                        <td>{{$pre_plan->year}}</td>
                        <td>{{$pre_plan->start_date}}</td>
                        <td>{{$pre_plan->end_date}}</td>
                        <td>{{$pre_plan->title}}</td>
                        <td>{{$pre_plan->support_type->support ?? '-'}}</td>
                        <td class="text-right align-middle">
                            <a href="{{route('pre-plan.show', $pre_plan->id)}}" title="details">
                                <i class="fa fa-eye text-primary"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$pre_plans->links()}}
    </div>
</div>

<div class="modal fade" id="modal-upload">
    <div class="modal-dialog modal-lg">
        <livewire:pre-plan.upload/>
    </div>
</div>

<div class="modal fade" id="modal-delete">
    <div class="modal-dialog">
        <livewire:confirm-delete/>
    </div>
</div>
@endsection

@section('js')
<script>
    $(function() {
        $('#btn-upload').on('click', function(e) {
            e.preventDefault();
            $('#modal-upload').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection