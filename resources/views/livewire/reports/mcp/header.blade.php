<div>
    <div class="row">
        <div class="col-lg-3">
            
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><i class="fa fa-spinner fa-spin" wire:loading></i><span wire:loading.remove>{{$schedules_count}}</span></h3>
    
                    <p>Total Schedules</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>

        </div>

        <div class="col-lg-3">
            
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><i class="fa fa-spinner fa-spin" wire:loading></i><span wire:loading.remove>{{$visited_count}}</span></h3>
    
                    <p>Total Visited</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>

        </div>

        <div class="col-lg-3">
            
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 class="text-white"><i class="fa fa-spinner fa-spin" wire:loading></i><span wire:loading.remove>{{$reschedule_count}}</span></h3>
    
                    <p class="text-white">Total Reschedule Requests</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
            </div>
            
        </div>

        <div class="col-lg-3">
            
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3><i class="fa fa-spinner fa-spin" wire:loading></i><span wire:loading.remove>{{$delete_count}}</span></h3>
    
                    <p>Total Delete Requests</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
            </div>
            
        </div>
    </div>
</div>
