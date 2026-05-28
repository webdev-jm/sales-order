@extends('adminlte::page')

@section('title')
    Weekly Productivity Reports
@endsection

@section('css')
<style>
    .user-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #6c757d;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-right: 6px;
        flex-shrink: 0;
        vertical-align: middle;
    }
    .user-cell { display: flex; align-items: center; }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>Weekly Productivity Reports</h1>
    </div>
    <div class="col-lg-6 text-right">
        @can('war create')
        <a href="{{route('war.create')}}" class="btn btn-primary"><i class="fa fa-plus mr-1"></i>Add Weekly Productivity Reports</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['war.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Weekly Productivity Reports</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                {!! Form::text('search', $search, ['class' => 'form-control float-right', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default" form="search_form">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Entries</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    @php
                        $initials = collect(explode(' ', $user->fullName() ?? ''))
                            ->filter()
                            ->take(2)
                            ->map(fn($w) => strtoupper($w[0]))
                            ->implode('');
                        $entryCount = $user->weekly_activity_reports()->count();
                    @endphp
                    <tr>
                        <td>
                            <div class="user-cell">
                                <span class="user-avatar">{{ $initials ?: '?' }}</span>
                                {{ $user->fullName() ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-primary badge-pill">{{ $entryCount }} {{ Str::plural('entry', $entryCount) }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{route('war.list', $user->id)}}" title="View reports" class="btn btn-xs btn-outline-primary">
                                <i class="fa fa-list mr-1"></i>View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$users->links()}}
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
