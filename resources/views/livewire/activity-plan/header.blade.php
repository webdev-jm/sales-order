<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Plan Details</h3>
            <div class="card-tools">
                <span class="text-sm" wire:loading><i class="fa fa-spinner fa-spin"></i></span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-4">
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="form-group">
                                <label class="text-muted mb-1">Year</label>
                                <input type="number" class="form-control" wire:model="year" wire:change="change_date" min="{{date('Y')}}">
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="form-group">
                                <label class="text-muted mb-1">Month</label>
                                <select class="form-control" wire:model="month" wire:change="change_date">
                                    @foreach($months_arr as $key => $mon)
                                    <option value="{{$key}}">{{$mon}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="form-group">
                        <label class="text-muted mb-1">Objectives for the month</label>
                        <textarea class="form-control @error('objectives') is-invalid @enderror" rows="3" wire:model.lazy="objectives" placeholder="Enter objectives..."></textarea>
                        @error('objectives')
                        <p class="invalid-feedback d-block">{{$message}}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
        });
    </script>
</div>
