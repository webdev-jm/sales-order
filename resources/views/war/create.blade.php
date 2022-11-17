@extends('adminlte::page')

@section('title')
    Weekly Activity Reports - Form
@endsection

@section('css')
<style>
    .w200 {
        width: 200px !important; 
    }
    .w300 {
        width: 300px !important;
    }
    .war-title {
        font-size: 25px;
    }
    .war-label {
        background-color: rgb(202, 202, 202);
    }

    th, td {
        border: 1.5px solid black !important;
    }
    .section-header {
        background-color: black;
        color: white;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>Weekly Activity Reports / Add</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('war.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['war.store'], 'id' => 'add_war']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Weekly Activity Report Form</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th class="w200 text-center align-middle px-0">
                        <img src="{{asset('/assets/images/bevi-logo.png')}}" alt="bevi logo">
                    </th>
                    <th class="text-center align-middle war-title" colspan="10">WEEKLY ACTIVITY REPORT</th>
                    <th class="w300 align-top" colspan="3">
                        DATE SUBMITTED: <br>
                        <p class="text-center mb-0 mt-2">2022-11-17</p>
                    </th>
                </tr>
                {{-- space --}}
                <tr>
                    <th class="border-0" colspan="12"></th>
                </tr>
            </thead>
            <tbody>
                {{-- header --}}
                    <tr>
                        <th class="war-label">NAME:</th>
                        <td colspan="6">{{auth()->user()->fullName()}}</td>

                        {{-- space --}}
                        <td class="border-0" colspan="3"></td>

                        <th class="war-label">DATE:</th>
                        <td class="p-0">
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control border-0" value="{{date('Y-m-d')}}">
                            </div>
                        </td>
                        <td>to</td>
                        <td class="p-0">
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control border-0" value="{{date('Y-m-d')}}">
                            </div>
                         </td>
                    </tr>
                    <tr>
                        <th class="war-label">AREA:</th>
                        <td colspan="6" class="p-0">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control border-0">
                            </div>
                        </td>

                        {{-- space --}}
                        <td class="border-0" colspan="3"></td>

                        <th class="war-label">WEEK:</th>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control border-0">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="war-label">AREA VISITED:</th>
                        <td colspan="6" class="p-0">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control border-0">
                            </div>
                        </td>

                        {{-- space --}}
                        <td class="border-0" colspan="7"></td>
                    </tr>
                {{-- spacing --}}
                    <tr>
                        <th class="border-0" colspan="14"></th>
                    </tr>
                {{-- objectives --}}
                    <tr>
                        <th class="align-middle war-label" colspan="14">I. OBJECTIVE/S</th>
                    </tr>
                    <tr>
                        <td class="p-0" colspan="14">
                            <textarea class="form-control border-0"></textarea>
                        </td>
                    </tr>
                {{-- areas --}}
                    <tr>
                        <th class="align-middle war-label pr-1" colspan="14">
                            II. AREAS
                            <button class="btn btn-primary btn-xs float-right"><i class="fa fa-plus mr-1"></i>Add Line</button>
                        </th>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="2">DATE</th>
                        <th colspan="2">DAY</th>
                        <th colspan="3">AREA COVERED</th>
                        <th colspan="3">IN/OUT BASE</th>
                        <th colspan="4">ACTIVITIES/REMARKS</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="2" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="3" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="3" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="4" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                    </tr>
                {{-- Highlights --}}
                    <tr>
                        <th class="align-middle war-label" colspan="14">III. Highlight(s) of week’s field visit (use 2nd page for more highlights when necessary):</th>
                    </tr>
                    <tr>
                        <td class="p-0" colspan="14">
                            <textarea class="form-control border-0"></textarea>
                        </td>
                    </tr>
                {{-- collections --}}
                    <tr class="text-center section-header">
                        <th colspan="3">BEGINNING AR</th>
                        <th colspan="4">DUE FOR COLLECTION</th>
                        <th colspan="3">BEGINNING HANGING BALANCE</th>
                        <th colspan="4">TARGET RECONCILIATIONS</th>
                    </tr>
                    <tr>
                        <td colspan="3" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="4" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="3" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="4" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="3">WEEK TO DATE</th>
                        <th colspan="4">MONTH TO DATE</th>
                        <th colspan="3">MONTH TARGET</th>
                        <th colspan="4">BALANCE TO SELL</th>
                    </tr>
                    <tr>
                        <td colspan="3" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="4" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="3" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="4" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                    </tr>
                {{-- action plans --}}
                    <tr>
                        <th class="align-middle war-label pr-1" colspan="14">
                            IV. SALES ACTION PLAN (to achieve sales/collection targets/to accomplish a project):
                            <button class="btn btn-primary btn-xs float-right"><i class="fa fa-plus mr-1"></i>Add Line</button>
                        </th>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="6">ACTION PLAN/S</th>
                        <th colspan="2">TIMETABLE</th>
                        <th colspan="6">PERSON/S RESPONSIBLE</th>
                    </tr>
                    <tr>
                        <td colspan="6" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="2" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="6" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                    </tr>
                {{-- activities --}}
                    <tr>
                        <th class="align-middle war-label pr-1" colspan="14">
                            V. ACTIVITIES
                            <button class="btn btn-primary btn-xs float-right"><i class="fa fa-plus mr-1"></i>Add Line</button>
                        </th>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="4">ACTIVITY</th>
                        <th>NO. OF DAYS (Weekly)</th>
                        <th>NO. OF DAYS (MTD)</th>
                        <th colspan="4">AREA/REMARKS</th>
                        <th>NO. OF DAYS (YTD)</th>
                        <th colspan="4">% to TOTAL WORKING DAYS</th>
                    </tr>
                    <tr>
                        <td colspan="4" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="4" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                        <td colspan="4" class="p-0">
                            <input type="text" class="form-control border-0">
                        </td>
                    </tr>
                    <tr class="text-center">
                        <th colspan="4" class="">TOTAL</th>
                        <td></td>
                        <td></td>
                        <td colspan="4"></td>
                        <td></td>
                        <td colspan="4"></td>
                    </tr>
                {{-- spacing --}}
                    <tr>
                        <th class="border-0" colspan="14"></th>
                    </tr>
                {{--  --}}
            </tbody>
        </table>
    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Weekly Activity Report', ['class' => 'btn btn-primary', 'form' => 'add_war']) !!}
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
