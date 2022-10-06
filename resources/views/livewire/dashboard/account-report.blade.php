
<div class="modal-content">
    <div wire:loading.class="overlay" wire:loading>
        <i class="fas fa-2x fa-sync fa-spin"></i>
    </div>
    <div class="modal-header">
        <h4 class="modal-title">Account Report</h4>
    </div>
    <div class="modal-body">
        @if(!empty($data))
        <ul class="list-group">
            @foreach($data as $val)
            <li class="list-group-item">
                {{$val['name']}}
                <span class="badge badge-primary float-right">{{$val['count']}}</span>
            </li>
            @endforeach
        </ul>
        @endif

    </div>
    <div class="modal-footer text-right">
        <button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
    </div>
</div>
