<div class="modal-content">
    @if(!empty($user))
    <div class="modal-header">
        <h4 class="modal-title">Assign User Branch</h4>
        <div class="card-tools">
            <span class="badge badge-primary">{{$user->firstname}} {{$user->lastname}}</span>
        </div>
    </div>
    <div class="modal-body">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Accounts</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                        <input type="text" class="form-control float-right" placeholder="Search" wire:model="search">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-12">
                        {{$accounts->links()}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-5 col-sm-3">
                        <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                            @foreach($accounts as $key => $account)
                                <a class="nav-link" id="vert-tabs-{{$account->id}}-tab" data-toggle="pill" href="#vert-tabs-{{$account->id}}" role="tab" aria-controls="vert-tabs-{{$account->id}}" aria-selected="true">{{$account->account_code}} {{$account->short_name}}</a>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-7 col-sm-9">
                        <div class="tab-content" id="vert-tabs-tabContent">
                            @foreach($accounts as $key => $account)
                            <div class="tab-pane text-left fade" id="vert-tabs-{{$account->id}}" role="tabpanel" aria-labelledby="vert-tabs-{{$account->id}}-tab">
                                <label>Branches</label>
                                <div class="row">
                                    @foreach($account->branches as $branch)
                                    <div class="col-lg-4">
                                        <button class="btn btn-default btn-block">{{$branch->branch_code}} {{$branch->branch_name}}</button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
        
            </div>
        </div>

    </div>
    <div class="modal-footer text-right">
        <button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
    </div>
    @endif
</div>
