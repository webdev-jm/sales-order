<div>
    <div class="modal-content">
        <div class="modal-header bg-light">
            <h4 class="modal-title">
                <i class="fas fa-tasks mr-2"></i>Activities
                <i wire:loading class="fa fa-spinner fa-spin ml-2 fa-sm"></i>
            </h4>
            <div class="ml-auto mr-3">
                <span class="badge badge-secondary">{{$date ?? ''}}</span>
            </div>
        </div>
        <div class="modal-body">

            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" placeholder="Search activities..." wire:model="search">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Operation Process</th>
                            <th>Description</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                        <tr>
                            <td class="text-center align-middle">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="switch{{$activity->id}}" wire:model="activity_data.{{$activity->id}}" value="{{$activity->id}}">
                                    <label class="custom-control-label" for="switch{{$activity->id}}"></label>
                                </div>
                            </td>
                            <td class="p-1 align-middle">
                                {{$activity->operation_process->operation_process}}
                            </td>
                            <td class="p-1 align-middle">
                                {{$activity->description}}
                            </td>
                            <td class="p-1 align-middle">
                                {{$activity->remarks}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-12">
                    {{$activities->links()}}
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal" wire:click="clear">Cancel</button>
            <button type="button" class="btn btn-primary" data-dismiss="modal" wire:click="save">
                <i class="fas fa-save mr-1"></i> Save
            </button>
        </div>

        <script>
            document.addEventListener('livewire:load', function () {
            });
        </script>
    </div>
</div>
