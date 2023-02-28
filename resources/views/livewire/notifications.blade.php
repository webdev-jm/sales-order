<div>
    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            @if($count > 0)
            <span class="badge badge-danger navbar-badge">{{$count}}</span>
            @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-item dropdown-header">{{$count}} Notification{{$count > 1 ? 's' : ''}}</span>
            <div class="dropdown-divider"></div>
            @foreach($notifications as $notification)
                <a href="#" class="dropdown-item" wire:click.prevent="readNotif('{{$notification->id}}')">
                    <!-- Message Start -->
                    <div class="media">
                        <div class="media-body">
                            <h3 class="dropdown-item-title font-weight-bold">
                                {{$notification->data['module']}}
                            </h3>
                            <p class="text-sm">{{$notification->data['message']}}</p>
                            <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> {{$notification->created_at->diffForHumans()}}</p>
                        </div>
                    </div>
                    <!-- Message End -->
                </a>

                <div class="dropdown-divider"></div>
            @endforeach
            
            <a href="{{route('notifications')}}" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
    </li>
    
</div>
