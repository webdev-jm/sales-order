@extends('adminlte::page')

@section('title')
    User - Details
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>User / Details</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{URL::previous()}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
    
<div class="row">

    <div class="col-lg-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                {{-- <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle"
                        src="../../dist/img/user4-128x128.jpg"
                        alt="User profile picture">
                </div> --}}

                <h3 class="profile-username text-center">{{$user->fullName()}}</h3>
                <p class="text-muted text-center">{{$user->email}}</p>
                <p class="text-muted text-center">{{implode(', ', $user->getRoleNames()->toArray())}}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Email</b> <a class="float-right">{{$user->notify_email ?? '-'}}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Last Activity</b> <span class="float-right">{{!empty($user->last_activity) ? date('F, j Y H:i:s a', strtotime($user->last_activity)) : '-'}}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- SUBORDINATES --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Subordinates</h3>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-unbordered">
                    @if(!empty($user->getSubordinateIds()))
                        @foreach($user->getSubordinateIds() as $level => $ids)
                            <li class="list-group-item py-1">
                                <b>{{$level}}</b>
                            </li>
                            @foreach($ids as $id)
                                <li class="list-group-item py-1">
                                    <a href="{{route('user.show', $id)}}" class="float-right">{{\App\Models\User::find($id)->fullName()}}</a>
                                </li>
                            @endforeach
                        @endforeach
                    @else
                        <li class="list-group-item py-1 text-center">
                            <b>No subordinates found</b>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    {{-- SUPERVISORS --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Supervisors</h3>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-unbordered">
                    @if(!empty($user->getSupervisorIds()))
                        @foreach($user->getSupervisorIds() as $level => $id)
                        <li class="list-group-item py-1">
                            <b>{{$level}} Level</b> <a href="{{route('user.show', $id)}}" class="float-right">{{\App\Models\User::find($id)->fullName()}}</a>
                        </li>
                        @endforeach
                    @else
                    <li class="list-group-item py-1 text-center">
                        <b>No supervisors found</b>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    {{-- ACCOUNTS --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assigned Accounts</h3>
                <div class="card-tools">
                    <a href="#" class="btn btn-primary btn-assign-account" data-id="{{$user->id}}" title="user accounts"><i class="fas fa-wrench mr-1"></i> Assign Accounts</a>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-unbordered">
                    @if(!empty($user->accounts))
                        @foreach($user->accounts as $account)
                        <li class="list-group-item py-1">
                            <b>{{$account->account_code}} - {{$account->short_name}}</b>
                            <span class="float-right"><b>{{$account->branches()->count()}}</b> branch</span>
                        </li>
                        @endforeach
                    @else
                        <li class="list-group-item py-1 text-center">
                            <b>No accounts found</b>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modal-accounts">
    <div class="modal-dialog modal-xl">
        <livewire:users.user-account/>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '.btn-assign-account', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('userAccount', id);
            $('#modal-accounts').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection