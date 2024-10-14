@extends('adminlte::page')

@section('title')
    PAF DETAIL
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>PAF
            <button class="btn btn-xs btn-{{$status_arr[$paf->status]}}">
                {{$paf->status}}
            </button>
        </h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('paf.index')}}" class="btn btn-default">
            <i class="fa fa-arrow-left mr-1"></i>
            BACK
        </a>
    </div>
</div>
@endsection

@section('content')

<div class="row">
    <div class="col-lg-12 mb-1">
        @if($paf->status == 'submitted' && ((!empty($paf->user->getImmediateSuperiorId()) && $paf->user->getImmediateSuperiorId() == auth()->user()->id) || auth()->user()->hasRole('superadmin')))
            <button class="btn btn-primary btn-sm" id="btn-approve">
                <i class="fa fa-check"></i>
                APPROVE
            </button>
        @endif
        <button class="btn btn-secondary btn-sm" id="btn-history">
            <i class="fa fa-clock mr-1"></i>
            HISTORY
        </button>
    </div>

    <div class="col-lg-4">
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title">PAF HEADER</h3>
            </div>
            <div class="card-body pt-3">
        
                <ul class="list-group">
                    <li class="list-group-item py-1">
                        <strong>PAF NUMBER</strong>
                        <span class="float-right">
                            {{$paf->paf_number}}
                        </span>
                    </li>
                    <li class="list-group-item py-1">
                        <strong>ACCOUNT</strong>
                        <span class="float-right">
                            {{$paf->account->account_code ?? ''}} - {{$paf->account->short_name ?? ''}}
                        </span>
                    </li>
                    <li class="list-group-item py-1">
                        <strong>EXPENSE TYPE</strong>
                        <span class="float-right">
                            {{$paf->expense_type->expense ?? '-'}}
                        </span>
                    </li>
                    <li class="list-group-item py-1">
                        <strong>SUPPORT TYPE</strong>
                        <span class="float-right">
                            {{$paf->support_type->support ?? '-'}}
                        </span>
                    </li>
                    <li class="list-group-item py-1">
                        <strong>TITLE</strong>
                        <span class="float-right">
                            {{$paf->title}}
                        </span>
                    </li>
                    <li class="list-group-item py-1">
                        <strong>START DATE</strong>
                        <span class="float-right">
                            {{$paf->start_date}}
                        </span>
                    </li>
                    <li class="list-group-item py-1">
                        <strong>END DATE</strong>
                        <span class="float-right">
                            {{$paf->end_date}}
                        </span>
                    </li>
                </ul>
        
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title">PAF DETAILS</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr class="text-center">
                            <th class="p-0 px-1 align-middle">PRODUCT</th>
                            <th class="p-0 px-1 align-middle">BRANCH</th>
                            <th class="p-0 px-1 align-middle">AMOUNT</th>
                            <th class="p-0 px-1 align-middle">EXPENSE</th>
                            <th class="p-0 px-1 align-middle">QUANTITY</th>
                            <th class="p-0 px-1 align-middle">SRP</th>
                            <th class="p-0 px-1 align-middle">PERCENTAGE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paf_detail as $detail)
                            <tr>
                                <td class="p-0 pl-2 text-left align-middle">
                                    {{$detail->product->stock_code ?? ''}} - {{$detail->product->description ?? ''}} {{$detail->product->size ?? ''}}
                                </td>
                                <td class="p-0 pl-2 text-left align-middle">
                                    {{$detail->branch}}
                                </td>
                                <td class="text-right p-0 px-1 align-middle">
                                    {{number_format($detail->amount, 2)}}
                                </td>
                                <td class="text-right p-0 px-1 align-middle">
                                    {{number_format($detail->expense, 2)}}
                                </td>
                                <td class="text-right p-0 px-1 align-middle">
                                    {{number_format($detail->quantity)}}
                                </td>
                                <td class="text-right p-0 px-1 align-middle">
                                    {{number_format($detail->srp, 2)}}
                                </td>
                                <td class="text-right p-0 px-1 align-middle">
                                    {{number_format($detail->percentage, 2)}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer pb-0">
                {{$paf_detail->links()}}
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modal-approval">
    <div class="modal-dialog modal-lg">
        <livewire:paf.approval/>
    </div>
</div>

<div class="modal fade" id="modal-history">
    <div class="modal-dialog modal-lg">
        <livewire:paf.history/>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('#btn-approve').on('click', function(e) {
            e.preventDefault();
            Livewire.emit('setPaf', 'approved', {{$paf->id}});
            $('#modal-approval').modal('show');
        });

        $('#btn-history').on('click', function(e) {
            e.preventDefault();
            Livewire.emit('setPafHistory', {{$paf->id}});
            $('#modal-history').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection