<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Add Custom Holiday</h4>
            <button type="button" class="close" data-dismiss="modal">
                <span>&times;</span>
            </button>
        </div>
        <div class="modal-body">

            @if($date)
                <h5 class="mb-3">
                    <i class="fas fa-calendar-day mr-1"></i>
                    {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                </h5>
            @else
                <div class="form-group">
                    <label for="holiday-date">Date</label>
                    <input type="date" id="holiday-date" class="form-control{{ $errors->has('month') || $errors->has('day') ? ' is-invalid' : '' }}"
                        wire:change="setDate($event.target.value)">
                    @error('month') <p class="text-danger">{{ $message }}</p> @enderror
                    @error('day') <p class="text-danger">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="form-group">
                <label for="holiday-title">Title</label>
                <input type="text" id="holiday-title"
                    class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}"
                    wire:model.lazy="title"
                    placeholder="Holiday name">
                @error('title') <p class="text-danger">{{ $message }}</p> @enderror
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="switch-repeat"
                            wire:model="repeat" value="1">
                        <label class="custom-control-label" for="switch-repeat">Repeat annually</label>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="switch-work-day"
                            wire:model="is_work_day" value="1">
                        <label class="custom-control-label" for="switch-work-day">Work day</label>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary"
                wire:loading.attr="disabled"
                wire:click.prevent="addHoliday">
                <span wire:loading wire:target="addHoliday" class="spinner-border spinner-border-sm mr-1"></span>
                Add Holiday
            </button>
        </div>
    </div>
</div>
