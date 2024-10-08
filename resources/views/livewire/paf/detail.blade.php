<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">PAF DETAIL</h4>
        </div>
        <div class="modal-body">
            @if(!empty($paf_data))
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="mb-0">PRODUCT</label>
                            <select class="form-control form-control-sm{{$errors->has('detail.product_id') ? ' is-invalid' : ''}}" wire:model="detail.product_id">
                                @foreach($paf_data['products'] as $product)
                                    <option value="{{$product['id']}}">{{$product['stock_code']}} - {{$product['description']}}</option>
                                @endforeach
                            </select>
                            <small class="text-danger">{{$errors->first('detail.product_id')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="mb-0">BRANCH</label>
                            <input type="text" class="form-control form-control-sm{{$errors->has('detail.branch') ? ' is-invalid' : ''}}" wire:model="detail.branch">
                            <small class="text-danger">{{$errors->first('detail.branch')}}</small>
                        </div>
                    </div>
                
                </div>

                <hr>

                <div class="row">

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="type" class="mb-0">TYPE</label>
                            <select id="type" class="form-control form-control-sm{{$errors->has('type') ? ' is-invalid' : ''}}" wire:model="detail.type">
                                <option value="">- SELECT TYPE -</option>
                                <option value="SELL IN">SELL IN</option>
                                <option value="EXPENSE">EXPENSE</option>
                            </select>
                            <small class="text-danger">{{$errors->first('detail.type')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="mb-0">QUANTITY</label>
                            <input type="number" class="form-control form-control-sm{{$errors->has('detail.quantity') ? ' is-invalid' : ''}}" wire:model="detail.quantity">
                            <small class="text-danger">{{$errors->first('detail.quantity')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="mb-0">SRP</label>
                            <input type="number" class="form-control form-control-sm{{$errors->has('detail.srp') ? ' is-invalid' : ''}}" wire:model="detail.srp">
                            <small class="text-danger">{{$errors->first('detail.srp')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="mb-0">PERCENTAGE</label>
                            <input type="number" class="form-control form-control-sm{{$errors->has('detail.percentage') ? ' is-invalid' : ''}}" wire:model="detail.percentage">
                            <small class="text-danger">{{$errors->first('detail.percentage')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="mb-0">AMOUNT</label>
                            <input type="number" class="form-control form-control-sm{{$errors->has('detail.amount') ? ' is-invalid' : ''}}" wire:model="detail.amount">
                            <small class="text-danger">{{$errors->first('detail.amount')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="mb-0">EXPENSE</label>
                            <input type="number" class="form-control form-control-sm{{$errors->has('detail.expense') ? ' is-invalid' : ''}}" wire:model="detail.expense">
                            <small class="text-danger">{{$errors->first('detail.expense')}}</small>
                        </div>
                    </div>
                </div>
            @endif

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
            <button type="button" class="btn btn-primary" wire:click.prevent="save">SAVE</button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('closeModal', () => {
                $('#modal-summary').modal('hide'); // Replace with your actual modal ID or closing method
            });
        });
    </script>
</div>
