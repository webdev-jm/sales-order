<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Activities<i wire:loading class="fa fa-spinner fa-spin ml-2 fa-sm"></i></h4>
            <div class="card-tools">
                {{$date ?? ''}}
            </div>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" placeholder="search" wire:model="search">
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th></th>
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
                            <td class="p-1">
                                {{$activity->operation_process->operation_process}}
                            </td>
                            <td class="p-1">
                                {{$activity->description}}
                            </td>
                            <td class="p-1">
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
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal" wire:click="clear">Close</button>
            <button type="button" class="btn btn-primary" data-dismiss="modal" wire:click="save">Save</button>
        </div>
    
        <script>
            document.addEventListener('livewire:load', function () {
            });
        </script>
    </div>
</div>
