<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">PRE-PLANS</h4>
        </div>
        <div class="modal-body">

            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover pb-0">
                    <thead>
                        <tr class="text-center">
                            <th class="align-middle p-0">PRE-PLAN NUMBER</th>
                            <th class="align-middle p-0">ACCOUNT</th>
                            <th class="align-middle p-0">SUPPORT TYPE</th>
                            <th class="align-middle p-0">YEAR</th>
                            <th class="align-middle p-0">START DATE</th>
                            <th class="align-middle p-0">END DATE</th>
                            <th class="align-middle p-0">TITLE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pre_plans as $pre_plan)
                            <tr class="text-center{{!empty($selected) && $selected->id == $pre_plan->id ? ' bg-info' : ''}}" wire:click="select({{$pre_plan->id}})">
                                <td class="align-middle p-0">{{$pre_plan->pre_plan_number}}</td>
                                <td class="align-middle p-0 pl-2 text-left">{{$pre_plan->account->account_code ?? ''}} - {{$pre_plan->account->short_name ?? ''}}</td>
                                <td class="align-middle p-0">{{$pre_plan->support_type->support ?? '-'}}</td>
                                <td class="align-middle p-0">{{$pre_plan->year}}</td>
                                <td class="align-middle p-0">{{$pre_plan->start_date}}</td>
                                <td class="align-middle p-0">{{$pre_plan->end_date}}</td>
                                <td class="align-middle p-0 pl-2 text-left">{{$pre_plan->title}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-12">
                    {{$pre_plans->links()}}
                </div>
            </div>

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
            <button type="button" class="btn btn-primary" wire:click.prevent="selectPrePlan">SELECT</button>
        </div>
    </div>
</div>
 