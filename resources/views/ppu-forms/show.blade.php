@extends('adminlte::page')

@section('title')
    PPU Form - Details
@endsection

@section('css')
<style>
    .bg-thead {
        background-color: #b1b1b1;
        color: rgb(255, 255, 255);
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>PPU Form / Details</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{URL::previous()}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                {{$ppu_form->control_number}}
                <small class="float-right">
                  
                    <span class="badge {{$ppu_form->status == 'draft' ? 'badge-secondary' : 'badge-success'}}">{{$ppu_form->status}}</span>

                </small>
            </h4>
        </div>
    </div>
    
    <div class="row invoice-info">
      
        
        <div class="col-sm-4 invoice-col">
            <b>Account:</b> [{{$ppu_form->account_login->account->account_code}}] {{$ppu_form->account_login->account->short_name}}<br>
            <b>Date Prepared:</b> {{$ppu_form->date_prepared}}<br>

            <b>Prepared By:</b> 
            <p>
                {{$ppu_form->account_login->user->fullName()}}
            </p>

        </div>

        <div class="col-sm-4 invoice-col">
            <b>Submitted Date:</b><br>
            <p>
                {{$ppu_form->date_submitted}}
            </p>
            <b>Pick-up Date:</b><br>
            <p>
                {{$ppu_form->pickup_date}}
            </p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-sm table-bordered text-center">
                <thead class="bg-thead">
                    <tr>
                        <th>#</th>
                        <th class="align-middle">RTV/RS No.</th>
                        <th class="align-middle">RTV Date</th>
                        <th class="align-middle">Branch Name</th>
                        <th class="align-middle">Quantity</th>
                        <th class="align-middle">Amount</th>
                        <th class="align-middle">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $num = 0;
                        $quantity_total = 0;
                        $sales_total = 0;
                        $sales_total_less_disc = 0;
                    @endphp
                    @foreach($items as $ppu_item)
                    @php
                        $num++;
                    @endphp
                    <tr>
                        <td class="align-middle">{{$num}}</td>
                        <td class="align-middle">{{$ppu_item->rtv_number}}</td>
                        <td class="align-middle">{{$ppu_item->rtv_date}}</td>
                        <td class="align-middle">{{$ppu_item->branch_name}}</td>
                        <td class="align-middle">{{$ppu_item->total_quantity}}</td>
                        <td class="align-middle">{{$ppu_item->total_amount}}</td>
                        <td class="align-middle">{{$ppu_item->remarks}}</td>
                    </tr>
                        
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <p class="lead">PPU Summary</p>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th style="width:50%">Total Quantity:</th>
                        <td class="text-right">{{number_format($ppu_form->total_quantity)}}</td>
                    </tr>
                    <tr>
                        <th style="width:50%">Total Amount:</th>
                        <td class="text-right">{{number_format($ppu_form->total_amount, 2)}}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="row no-print">
        <div class="col-12 ">
          <a type="button" href="{{route('ppu.pdf', $ppu_form->id)}}" target="_blank" class="btn btn-primary float-right" style="margin-right: 5px;">
            <i class="fas fa-download"></i> Generate PDF
          </a>
        </div>
      </div>
    
</div>

@endsection

@section('js')

@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection