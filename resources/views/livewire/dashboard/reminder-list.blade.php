<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Reminders</h3>
        </div>
        <div class="card-body">
            <ul class="list-group">
                @foreach($reminders as $reminder)
                <li class="list-group-item">
                    <a href="{{$reminder->link}}">[{{$reminder->user->fullName()}} {{date('F Y', strtotime($reminder->model->year.'-'.$reminder->model->month.'-1'))}}]</a>
                    {{$reminder->message}}

                    <span class="float-right">{{\Carbon\Carbon::parse($reminder->date)->diffForHumans()}}</span>
                </li>
                @endforeach
            </ul>
        </div>
        <div class="card-footer">
            {{$reminders->links()}}
        </div>
    </div>
</div>
