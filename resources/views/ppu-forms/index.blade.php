@extends('adminlte::page')

@section('title')
    PPU Forms
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>PPU Forms</h1>
    </div>
    <div class="col-md-6 text-right">
        @if(empty($cut_off))
           
            @can('ppu form create')
                <a href="{{route('ppu.create')}}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i>
                    ADD PPU FORM
                </a>
            @endcan

            <!-- <a href="{{route('ppu-form-multiple.index')}}" class="btn btn-success">
                <i class="fa fa-upload mr-1"></i>
                UPLOAD MULTIPLE PPU
            </a> -->

        @endif
    </div>

    @if(!empty($cut_off))
    <div class="col-lg-12">
        <div class="alert alert-warning mb-0 mt-2" role="alert">
            <h4 class="alert-heading">NOTE:</h4>
            <p>
                {{$cut_off->message}}
            </p>
            <hr class="my-1">
            <p class="mb-0">SO CUT-OFF: from <b>{{date('Y-m-d H:i:s a', $cut_off->start_date)}}</b> to <b>{{date('Y-m-d H:i:s a', $cut_off->end_date)}}</b></p>
          </div>
    </div>
    @endif
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['sales-order.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}



<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of PPU Forms</h3>
        <div class="card-tools">
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>Control Number</th>
                    <th>RTV Date</th>
                    <th>Branch Name</th>
                    <th>Pick-Up Date</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($ppu_form as $sales_order)
                <tr>
                    <td>{{$sales_order->control_number}}</td>
                    <td>{{$sales_order->date_prepared}}</td>
                    <td>{{$sales_order->account_login->account->account_name}}</td>
                    <td>{{$sales_order->pickup_date}}</td>

                    <td>
                        @if(isset($sales_order->upload_status))
                            @if($sales_order->status == 'cancelled')
                                <span class="badge badge-danger">{{$sales_order->status}}</span>
                            @else
                                <span class="badge {{$sales_order->upload_status == 1 ? 'badge-info' : 'badge-warning'}}">{{$sales_order->upload_status == 1 ? 'Uploaded' : 'Upload Error'}}</span>
                            @endif
                        @else
                            @if($sales_order->status == 'cancelled')
                                <span class="badge badge-danger">{{$sales_order->status}}</span>
                            @else
                                <span class="badge {{$sales_order->status == 'draft' ? 'badge-secondary' : 'badge-success'}}">{{$sales_order->status}}</span>
                            @endif
                        @endif
                    </td>
                    <td>{{isset($sales_order->account_login->user) ? $sales_order->account_login->user->fullName() : '-'}}</td>
                    <td class="text-right">
                        @if(empty($cut_off) || (!empty($cut_off) && strtotime($cut_off->date.' '.$cut_off->time) > time()))
                            @if($sales_order->status == 'draft')
                                @can('sales order edit')
                                    <a href="{{route('ppu.edit', $sales_order->id)}}" title="edit">
                                        <i class="fas fa-edit text-success mx-1"></i>
                                    </a>
                                @endcan
                            @endif
                        @endif
                        <a href="{{route('ppu.show', $sales_order->id)}}" title="view">
                            <i class="fa fa-eye text-primary mx-1"></i>
                        </a>
                        @if(auth()->user()->can('ppu form delete') || $sales_order->status == 'draft')
                            <a href="#" title="delete" class="btn-delete" data-id="{{$sales_order->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$ppu_form->links()}}
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
        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('setDeleteModel', 'PPUForm', id);
            $('#modal-delete').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection