<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity Plan</h3>
            <div class="card-tools text-sm" wire:loading>
                <i class="fa fa-spinner fa-spin"></i>
            </div>
        </div>
        <div class="card-body">
            
            <div class="row">
                <div class="col-xl-4">
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" class="form-control" wire:model="year" wire:change="change_date" min="{{date('Y')}}">
                            </div>
                        </div>
        
                        <div class="col-xl-6">
                            <div class="form-group">
                                <label>Month</label>
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
                        <label>Objectives for the month</label>
                        <textarea class="form-control @error('objectives') is-invalid @enderror" wire:model.lazy="objectives"></textarea>
                        @error('objectives')
                        <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>
                
            </div>

            {{-- @if(!empty($deadline_message))
            <div class="row">
                <div class="col-12">
                    <span class="text-danger">
                        {{$deadline_message}}
                    </span>
                </div>
            </div>
            @endif --}}

        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:load', function() {
        });
    </script>
</div>
