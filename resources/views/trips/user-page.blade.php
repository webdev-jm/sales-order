@extends('adminlte::master')

@section('adminlte_css')
<style>
    body {
      padding: 150px;
      background: #ffffff8e url('/images/pexels-pavel-danilyuk-5496464.jpg') no-repeat center center;
    }
    h1 {
        font-size: 60px;
    }

    .login-logo {
        color: white;
        font-weight: 700;
    }

    /* Media Query for Mobile View */
    @media only screen and (max-width: 600px) {
        body {
            padding: 20px; /* Adjust as needed for smaller screens */
        }

        h1 {
            font-size: 40px; /* Adjust font size for smaller screens */
        }

        body {
            font-size: 20px; /* Adjust font size for smaller screens */
        }

        article {
            width: 100%; /* Make the article width 100% for smaller screens */
        }
    }
</style>
@stop

@section('body')
    {{-- Logo --}}
    <div class="{{ $auth_type ?? 'login' }}-logo">

            {{-- Logo Image --}}
            @if (config('adminlte.auth_logo.enabled', false))
                <img src="{{ asset(config('adminlte.auth_logo.img.path')) }}"
                    alt="{{ config('adminlte.auth_logo.img.alt') }}"
                    @if (config('adminlte.auth_logo.img.class', null))
                        class="{{ config('adminlte.auth_logo.img.class') }}"
                    @endif
                    @if (config('adminlte.auth_logo.img.width', null))
                        width="{{ config('adminlte.auth_logo.img.width') }}"
                    @endif
                    @if (config('adminlte.auth_logo.img.height', null))
                        height="{{ config('adminlte.auth_logo.img.height') }}"
                    @endif>
            @else
                <img src="{{ asset(config('adminlte.logo_img')) }}"
                    alt="{{ config('adminlte.logo_img_alt') }}" height="50">
            @endif

            {{-- Logo Label --}}
            {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}

        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">USER TRIPS</h3>
        </div>
        <div class="card-body">
            
            <div class="row">
                <div class="col-lg-12">
                    <strong class="text-uppercase">NAME: {{$user->fullName()}}</strong>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>TRIP CODE</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trips as $trip)
                            <tr>
                                <td>
                                    <a href="{{route('trip.user-trip', encrypt($trip->id, 'trip-id'))}}" class="font-weight-bold">
                                        {{$trip->trip_number}}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-{{$status_arr[$trip->status]}}">{{$trip->status}}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    {{$trips->links()}}
                </div>
            </div>

        </div>
    </div>
@endsection