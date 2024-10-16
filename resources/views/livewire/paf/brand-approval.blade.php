<div>
    <div class="card card-danger card-outline">
        <div class="card-header">
            <h3 class="card-title">BRAND APPROVAL</h3>
        </div>
        <div class="card-body">

            <div class="row">
                @foreach($brands as $brand)
                    <div class="col-lg-3">
                        <div class="card mb-0">
                            <div class="card-header">
                                <h3 class="card-title">{{$brand->brand}}</h3>
                            </div>
                            <div class="card-body p-1">
                                <label class="mb-0">REMARKS</label>
                                @if(!empty($brand_approvals[$brand->id]))
                                    <p class="ml-2">
                                        {{$brand_approvals[$brand->id]->remarks ?? ''}}
                                    </p>
                                @else
                                    <textarea rows="3" class="form-control" wire:model="remarks.{{$brand->id}}"></textarea>
                                @endif
                            </div>
                            <div class="card-footer py-1">
                                @if(empty($brand_approvals[$brand->id]))
                                    <button class="btn btn-sm btn-primary" wire:click.prevent="approve({{$brand->id}})">
                                        APPROVE
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
        <div class="card-footer">
            
        </div>
    </div>
</div>
