<div>
    <div class="row">
        <div class="col-lg-3">
            <div class="form-group">
                <label>Operation Process</label>
                <select class="form-control" wire:change="selectOperationProcess" wire:model="operation_process_id">
                    <option value=""></option>
                    @foreach($operation_processes as $operation_process)
                    <option {{$operation_process_id == $operation_process->id ? 'selected' : ''}} value="{{$operation_process->id}}">{{$operation_process->operation_process}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if(isset($activities))
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activities</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th></th>
                        <th>Description</th>
                        <th>Notes</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                    <tr class="{{isset($activity_val[$activity->id]['number']) && $activity_val[$activity->id]['number'] == true ? '' : 'bg-light'}}">
                        <td class="p-0 text-center align-middle">
                            <div class="form-group m-0">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="switch{{$activity->id}}" wire:model="activity_val.{{$activity->id}}.number" checked="{{$activity_val[$activity->id]['number'] ?? 'false'}}" wire:change="updateData">
                                    <label class="custom-control-label" for="switch{{$activity->id}}"></label>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">{{$activity->number}}. {{$activity->description}}</td>
                        <td class="align-middle">{{$activity->remarks}}</td>
                        <td class="p-0">
                            <textarea class="form-control border-0" {{isset($activity_val[$activity->id]['number']) && $activity_val[$activity->id]['number'] == true ? '' : 'disabled'}} wire:model.lazy="activity_val.{{$activity->id}}.remarks" wire:change="updateData" wire:loading.attr="disabled"></textarea>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
