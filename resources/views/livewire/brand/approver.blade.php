<div>
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Brand Approver</h3>
            <div class="card-tools">
                <button class="btn btn-primary btn-sm" wire:click.prevent="btnAdd">
                    <i class="fa fa-plus"></i>
                    Add Approver
                </button>
            </div>
        </div>
        <div class="card-body pt-1">

            @if($add_form)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="mb-0">Approver</label>
                            <select class="form-control form-control-sm" wire:model="approver_id">
                                @foreach($select_users as $option)
                                    <option value="{{$option->id}}">{{$option->fullName()}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <button class="btn btn-default btn-sm" wire:click.prevent="btnAdd">
                            <i class="fa fa-ban mr-1"></i>
                            Cancel
                        </button>
                        <button class="btn btn-primary btn-sm" wire:click.prevent="addApprover">
                            <i class="fa fa-save mr-1"></i>
                            Add Approver
                        </button>
                    </div>
                </div>

                <hr>
            @endif
            
            <ul class="list-group">
                @foreach($users as $user)
                    <li class="list-group-item py-1 pr-1">
                        {{$user->fullName()}}
                        <span class="float-right">
                            <button class="btn btn-xs btn-danger" wire:click.prevent="deleteApprover({{$user->id}})">
                                <i class="fa fa-trash-alt"></i>
                            </button>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
