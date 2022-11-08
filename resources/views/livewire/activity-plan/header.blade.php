<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity Plan</h3>
            <div class="card-tools text-sm" wire:loading>
                <i class="fa fa-spinner fa-spin"></i>
            </div>
        </div>
        <div class="card-body">

            <div class="row mb-2">
                <div class="col-12">
                    <b>NAME:</b> {{auth()->user()->fullName()}}<br>
                    @if(!empty($position))
                    <b>POSITION:</b> {{implode(', ', $position)}}
                    @endif
                </div>
            </div>

            <hr>
            
            <div class="row">
                <div class="col-xl-4">
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" class="form-control" wire:model="year" wire:change="change_date">
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
                        <textarea class="form-control"></textarea>
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
