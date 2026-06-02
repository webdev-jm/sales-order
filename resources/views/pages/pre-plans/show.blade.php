@extends('adminlte::page')

@section('title')
    Pre Plan Detail
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Pre Plan Detail</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('pre-plan.index')}}" class="btn btn-default">
            <i class="fa fa-arrow-left mr-1"></i>
            BACK
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pre Plan Header</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item">
                        <span>PRE-PLAN NUMBER</span>
                        <strong class="float-right">{{$pre_plan->pre_plan_number}}</strong>
                    </li>
                    <li class="list-group-item">
                        <span>ACCOUNT</span>
                        <strong class="float-right">{{$pre_plan->account->account_code ?? ''}} - {{$pre_plan->account->short_name ?? ''}}</strong>
                    </li>
                    <li class="list-group-item">
                        <span>SUPPORT TYPE</span>
                        <strong class="float-right">{{$pre_plan->support_type->support ?? '-'}}</strong>
                    </li>
                    <li class="list-group-item">
                        <span>YEAR</span>
                        <strong class="float-right">{{$pre_plan->year ?? ''}}</strong>
                    </li>
                    <li class="list-group-item">
                        <span>START DATE</span>
                        <strong class="float-right">
                            {{ $pre_plan->start_date ? \Carbon\Carbon::parse($pre_plan->start_date)->format('d-M-Y') : '' }}
                        </strong>
                    </li>
                    <li class="list-group-item">
                        <span>END DATE</span>
                        <strong class="float-right">
                            {{ $pre_plan->end_date ? \Carbon\Carbon::parse($pre_plan->end_date)->format('d-M-Y') : '' }}
                        </strong>
                    </li>
                    <li class="list-group-item">
                        <span>TITLE</span>
                        <strong class="float-right">{{$pre_plan->title ?? ''}}</strong>
                    </li>
                    <li class="list-group-item">
                        <span>CONCEPT</span>
                        <strong class="float-right">{{$pre_plan->concept ?? ''}}</strong>
                    </li>
                </ul>
            </div>
            <div class="card-footer">

            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pre Plan Details</h3>
            </div>
            <div class="card-body">

                <table class="table table-bordered table-hover table-sm">
                    <thead>
                        <tr class="text-center">
                            <th class="align-middle p-0">TYPE</th>
                            <th class="align-middle p-0">COMPONENTS</th>
                            <th class="align-middle p-0">BRANCH</th>
                            <th class="align-middle p-0">STOCK CODE</th>
                            <th class="align-middle p-0">DESCRIPTION</th>
                            <th class="align-middle p-0">PRICE CODE</th>
                            <th class="align-middle p-0">BRAND</th>
                            <th class="align-middle p-0">QUANTITY</th>
                            <th class="align-middle p-0">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pre_plan_details as $detail)
                            <tr class="text-center">
                                <td class="p-0 align-middle">{{$detail->type}}</td>
                                <td class="p-0 align-middle">{{$detail->components}}</td>
                                <td class="p-0 align-middle">{{$detail->branch}}</td>
                                <td class="p-0 align-middle">{{$detail->stock_code}}</td>
                                <td class="p-0 align-middle">{{$detail->description}}</td>
                                <td class="p-0 align-middle">{{$detail->price_code}}</td>
                                <td class="p-0 align-middle">{{$detail->brand}}</td>
                                <td class="p-0 align-middle">{{$detail->quantity}}</td>
                                <td class="p-0 align-middle">{{number_format($detail->amount, 2)}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="text-center">
                            <th colspan="8" class="text-right p-0">TOTAL</th>
                            <th class="p-0">{{ number_format($total_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer">
                {{$pre_plan_details->links()}}
            </div>
        </div>
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
@endsection
