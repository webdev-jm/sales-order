<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Reminders</h3>
        </div>
        <div class="card-body">
            <ul class="list-group">
                @if(!empty($reminders->count()))
                    @foreach($reminders as $reminder)
                    <li class="list-group-item">
                        <a href="{{$reminder->link}}">[{{$reminder->user->fullName()}}]</a>
                        {{$reminder->message}}

                        <span class="float-right">{{\Carbon\Carbon::parse($reminder->date)->diffForHumans()}}</span>
                    </li>
                    @endforeach
                @else
                    <li class="list-group-item text-center">
                        no reminders as of the moment
                    </li>
                @endif
            </ul>
        </div>
        <div class="card-footer">
            {{$reminders->links()}}
        </div>
    </div>
</div>
