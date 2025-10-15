<div>
    <div class="modal-content">
        <div class="modal-header bg-white">
            <h4 class="modal-title" id="orderSummaryModalLabel">PPU SUMMARY</h4>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <h4 class="fw-bold"> {{ $control_number }}</h4>
            </div>
            <hr class="my-0">
                <div class="row">
                    <div class="col-lg-6">
                        <b class="text-uppercase" >{{$account_name}}</b>
                        <br>
                        <b>DATE SUBMITTED:</b> <span>{{$submitted}}</span>
                        <br>
                        <b>PICK-UP DATE:</b> <span>{{$pickup}}</span>
                    </div>
                </div>
            <hr class="my-1">

            <table class="table table-striped" id="summaryTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>RTV/RS No.</th>
                        <th>RTV Date</th>
                        <th>Branch Name</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['rs'] }}</td>
                            <td>{{ $item['rtv'] }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['qty'] }}</td>
                            <td>{{ number_format($item['amount'], 2) }}</td>
                            <td>{{ $item['remarks'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">TOTAL</th>
                        <th>{{ $total_qty }}</th>
                        <th>{{ number_format($total_amount, 2) }}</th>
                        <th></th>

                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="modal-footer">
            <button id="btn-finalize" class="btn btn-success">Finalize</button>
            <button id="btn-draft" class="btn btn-secondary">Save as Draft</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>

<script>
window.addEventListener('order-confirmed', () => {
    Swal.fire({
        title: 'PPU saved!',
        text: 'The ppu has been stored in session.',
        icon: 'success',
    }).then(() => {
        // Optionally close modal or redirect
        document.querySelector('#reviewModal .btn-close').click();
    });
});
</script>
