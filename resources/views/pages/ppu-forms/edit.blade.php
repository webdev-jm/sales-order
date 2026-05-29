@extends('adminlte::page')

@section('title')
    PPU Forms / Edit
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1 id="so-title">PPU Forms / Edit</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('ppu.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
   
</div>
@endsection


@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['ppu.update', $ppu_form->id], 'id' => 'update_ppu']) !!}
<input type="hidden" name="control_number" form="update_ppu" value="{{$control_number}}">
{!! Form::close() !!}


<div class="card">
    <div class="card-header bg-black card-danger card-outline">
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                @if($ppu_form->account_login->account->company_id == 1)
                <img src="{{asset('/images/bevi-logo-white (1).png')}}" alt="logo" height="60px"> 
                @elseif($ppu_form->account_login->account->company_id == 2)
                <img src="{{asset('/images/bevalogo.jpg')}}" alt="logo" height="100px" width="150px"> 
                @else
                @endif
            </div>
            <div class="col-12 col-sm-6 col-md-6">
                <h1 class="text-bold text-center"> PROPOSAL FOR PICK-UP (PPU) FORM</h1>
            </div>
        </div>
    </div>
    <div class="card-body card-black card-outline">


       <div class="row">

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('ship_to_name', 'CUSTOMER NAME:') !!}
                    {!! Form::text('ship_to_name', $ppu_form->account_login->account->account_name, ['class' => 'form-control form-control-sm bg-white'.($errors->has('ship_to_name') ? ' is-invalid' : ''), 'readonly']) !!}
                    <p class="text-danger">{{$errors->first('ship_to_name')}}</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('control_number', 'PPU NO:') !!}
                    {!! Form::text('control_number', $control_number, ['class' => 'form-control form-control-sm bg-white'.($errors->has('control_number') ? ' is-invalid' : ''), 'readonly']) !!}
                    <p class="text-danger">{{$errors->first('control_number')}}</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('fullName', 'PREPARED BY::') !!}
                    {!! Form::text('fullName', $ppu_form->account_login->user->fullName(), ['class' => 'form-control form-control-sm bg-white'.($errors->has('name') ? ' is-invalid' : ''), 'readonly']) !!}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('date_submitted', 'DATE SUBMITTED (SCM):') !!}
                    {!! Form::date('date_submitted', $ppu_form->date_submitted, ['class' => 'form-control form-control-sm'.($errors->has('date_submitted') ? ' is-invalid' : ''), 'form' => 'update_ppu']) !!}
                    <p class="text-danger">{{$errors->first('date_submitted')}}</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('date_prepared', 'DATE PREPARED:') !!}
                    {!! Form::date('date_prepared', $ppu_form->date_prepared, ['class' => 'form-control form-control-sm bg-white'.($errors->has('date_prepared') ? ' is-invalid' : ''), 'form' => 'update_ppu', 'readonly']) !!}
                    <p class="text-danger">{{$errors->first('date_prepared')}}</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('pickup_date', 'PROPOSE PICK-UP DATE:') !!}
                    {!! Form::date('pickup_date', $ppu_form->pickup_date, ['class' => 'form-control form-control-sm'.($errors->has('pickup_date') ? ' is-invalid' : ''), 'form' => 'update_ppu']) !!}
                    <p class="text-danger">{{$errors->first('pickup_date')}}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <form action="" method="POST">
                    @csrf
                   

                    <table class="table table-bordered text-center" id="dynamicTable">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>RTV/RS No.</th>
                                <th>RTV Date</th>
                                <th>Branch Name</th>
                                <th>Total Qty</th>
                                <th>Total Amount</th>
                                <th>Remarks</th>
                                <th><button type="button" name="add" id="addBtn" class="btn btn-success"><i class="fa fa-plus"></i></button></th>
                            </tr>
                        </thead>
                        @php
                            $num = 1;
                        @endphp

                        <tbody >
                            @foreach ($ppuform_item as $index => $item)
                            <tr>
                                <td class="row-number">{{ $index + 1 }}</td>
                                <td><input type="text" name="items[{{ $index }}][rs]"  value="{{ $item['rtv_number'] }}" class="form-control text-center rs" /></td>
                                <td><input type="date" name="items[{{ $index }}][rtvdate]" value="{{ $item['rtv_date'] }}" class="form-control text-center rtv" /></td>
                                <td><input type="text" name="items[{{ $index }}][name]" value="{{ $item['branch_name'] }}" class="form-control text-center name" /></td>
                                <td><input type="number" name="items[{{ $index }}][qty]" value="{{ $item['total_quantity'] }}" class="form-control text-center qty" value="0"/></td>
                                <td><input type="number" name="items[{{ $index }}][amount]" value="{{ $item['total_amount'] }}" class="form-control text-center amount" value="0"/></td>
                                <td><input type="text" name="items[{{ $index }}][remarks]" value="{{ $item['remarks'] }}" class="form-control text-center remarks" /></td>
                                <td><button type="button" class="btn btn-danger removeRow">x</button></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th> 
                                    <p class="text-danger">{{$errors->first('items')}}</p>
                                </th>

                                <th colspan="2" class="text-right">TOTAL</th>
                                <th id="totalQty">0</th>
                                <th id="totalAmount">0.00</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- <button type="submit" class="btn btn-primary">Save</button> -->
                </form>
            </div>
        
        </div>
        
    </div>
    <div class="card-footer">
        <span class="badge {{$ppu_form->status == 'draft' ? 'badge-secondary' : 'badge-success'}}">{{$ppu_form->status}}</span>

        <div class="col-md-12 text-right">
                {!! Form::submit('Save as Draft', ['class' => 'btn btn-secondary btn-submit', 'form' => 'update_ppu']) !!}

                <button type="button" id="reviewOrderBtn" class="btn btn-primary">Review</button>

                {!! Form::hidden('status', 'draft', ['form' => 'update_ppu', 'id' => 'status']) !!}
        </div>
    </div>
</div>


<div class="modal fade" id="orderSummaryModal" tabindex="-1" aria-labelledby="orderSummaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
        <livewire:ppu-form.ppu-summary/>
  </div>
</div>

@endsection

@section('js')

<script>
    let i = 0;

    document.getElementById("addBtn").addEventListener("click", function () {
        i++;
        let table = document.querySelector("#dynamicTable tbody");
        let newRow = document.createElement("tr");
        newRow.innerHTML = `
            <td class="row-number"></td>
            <td><input type="text" name="items[${i}][rs]" placeholder="Enter RTV/RS No." class="form-control text-center rs" /></td>
            <td><input type="date" name="items[${i}][rtvdate]" placeholder="Enter RTV Date" class="form-control text-center rtv" /></td>
            <td><input type="text" name="items[${i}][name]" placeholder="Enter Branch Name" class="form-control text-center name" /></td>
            <td><input type="number" name="items[${i}][qty]" placeholder="Enter Qty" class="form-control text-center qty" value="0" /></td>
            <td><input type="number" name="items[${i}][amount]" placeholder="Enter Amount" class="form-control text-center amount" value="0" /></td>
            <td><input type="text" name="items[${i}][remarks]" placeholder="Enter Remarks" class="form-control text-center remarks" /></td>
            <td><button type="button" class="btn btn-danger removeRow">x</button></td>
        `;
        table.appendChild(newRow);

        const controlNumber = document.querySelector('input[name="control_number"]').value;
        const account_name = document.querySelector('input[name="ship_to_name"]').value;
        const submitted = document.querySelector('input[name="date_submitted"]').value;
        const pickup = document.querySelector('input[name="pickup_date"]').value;

        const items = [];

        document.querySelectorAll('#dynamicTable tbody tr').forEach(row => {
            let rs = row.querySelector(".rs").value || "-";
            let rtv = row.querySelector(".rtv").value || "2025-01-01";
            let name = row.querySelector(".name").value || "-";
            let qty = parseFloat(row.querySelector(".qty").value) || 0;
            let amount = parseFloat(row.querySelector(".amount").value) || 0;
            let remarks = row.querySelector(".remarks").value || "-";

            items.push({ rs, rtv, name, qty, amount, remarks });
        });

        Livewire.emit('loadOrderSummary', controlNumber, items, account_name, submitted, pickup);
        
        updateRowNumbers();
        calculateTotals();
    });

    document.addEventListener("click", function (e) {
        if (e.target && e.target.classList.contains("removeRow")) {
            e.target.closest("tr").remove();
            updateRowNumbers();
            calculateTotals();
            
        const controlNumber = document.querySelector('input[name="control_number"]').value;
        const account_name = document.querySelector('input[name="ship_to_name"]').value;
        const submitted = document.querySelector('input[name="date_submitted"]').value;
        const pickup = document.querySelector('input[name="pickup_date"]').value;

        const items = [];

        document.querySelectorAll('#dynamicTable tbody tr').forEach(row => {
            let rs = row.querySelector(".rs").value || "-";
            let rtv = row.querySelector(".rtv").value || "2025-01-01";
            let name = row.querySelector(".name").value || "-";
            let qty = parseFloat(row.querySelector(".qty").value) || 0;
            let amount = parseFloat(row.querySelector(".amount").value) || 0;
            let remarks = row.querySelector(".remarks").value || "-";

            items.push({ rs, rtv, name, qty, amount, remarks });
        });

        Livewire.emit('loadOrderSummary', controlNumber, items, account_name, submitted, pickup);

        }
    });


    document.addEventListener("input", function (e) {
        if (e.target.classList.contains("qty") || e.target.classList.contains("amount")) {
            calculateTotals();

        }
        if (e.target.name.includes("[rs]")) {
                checkDuplicateNames();
        }
    });

    function updateRowNumbers() {
        document.querySelectorAll("#dynamicTable tbody tr").forEach((row, index) => {
            row.querySelector(".row-number").textContent = index + 1;
        });
    }

    document.getElementById("reviewOrderBtn").addEventListener("click", function () {
        const controlNumber = document.querySelector('input[name="control_number"]').value;
        const account_name = document.querySelector('input[name="ship_to_name"]').value;
        const submitted = document.querySelector('input[name="date_submitted"]').value;
        const pickup = document.querySelector('input[name="pickup_date"]').value;

        const items = [];

        document.querySelectorAll('#dynamicTable tbody tr').forEach(row => {
            let rs = row.querySelector(".rs").value || "-";
            let rtv = row.querySelector(".rtv").value || "2025-01-01";
            let name = row.querySelector(".name").value || "-";
            let qty = parseFloat(row.querySelector(".qty").value) || 0;
            let amount = parseFloat(row.querySelector(".amount").value) || 0;
            let remarks = row.querySelector(".remarks").value || "-";

            items.push({ rs, rtv, name, qty, amount, remarks });
        });

        Livewire.emit('loadOrderSummary', controlNumber, items, account_name, submitted, pickup);
        let modal = new bootstrap.Modal(document.getElementById('orderSummaryModal'));
        modal.show();
    });

    function calculateTotals() {
        let totalQty = 0;
        let totalAmount = 0;

        document.querySelectorAll("#dynamicTable tbody tr").forEach(row => {
            let qty = parseFloat(row.querySelector(".qty").value) || 0;
            let price = parseFloat(row.querySelector(".amount").value) || 0;
            totalQty += qty;
            totalAmount += price;
        });

        document.getElementById("totalQty").textContent = totalQty;
        document.getElementById("totalAmount").textContent = totalAmount.toFixed(2);
    }

        $('body').on('click', '#btn-finalize', function(e) {
            e.preventDefault();
            if(confirm('Are you sure to finalize this ppu?')) {
                var status_val = 'finalized';
                $('#status').val(status_val);
                $('#update_ppu').submit();
            }
        });

        $('body').on('click', '#btn-draft', function(e) {
            e.preventDefault();

            var status_val = 'draft';
            $('#status').val(status_val);
            $('#update_ppu').submit();
            
        });

        function checkDuplicateNames() {
            const names = [];
            const inputs = document.querySelectorAll('input[name*="[rs]"]');
            let hasDuplicate = false;

            inputs.forEach(input => {
                const name = input.value.trim().toLowerCase();
                if (name !== "") {
                    if (names.includes(name)) {
                        input.classList.add("is-invalid");
                        hasDuplicate = true;
                    } else {
                        input.classList.remove("is-invalid");
                        names.push(name);
                    }
                }
            });

            if (hasDuplicate) {
                alert("Duplicate RTV No. are not allowed in the same ppu!");
            }
        }



    updateRowNumbers();
    calculateTotals();
</script>

@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection