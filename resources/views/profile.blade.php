@extends('adminlte::page')

@section('title')
    Profile
@endsection

@section('css')
@endsection

@section('content_header')
    <h1>Profile</h1>
@endsection

@section('content')
<div class="row">
    <div class="col-md-3">
        
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
                        <b>Accounts</b> <a class="float-right">{{$user->accounts()->count()}}</a>
                    </li>
                </ul>
            </div>
            
        </div>

    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="#change-password" data-toggle="tab">Change Password</a></li>
                    <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Activity</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">

                    <div class="active tab-pane" id="settings">
                        <livewire:profile.profile-update/>
                    </div>
                    
                    <div class="tab-pane" id="change-password">
                        <livewire:profile.change-password/>
                    </div>
                    
                    <div class="tab-pane" id="activity">
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection

@section('js')
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
