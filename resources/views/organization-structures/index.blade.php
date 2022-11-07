@extends('adminlte::page')

@section('title')
    Organization Structure
@endsection

@section('css')
<link rel="stylesheet" href="{{asset('/vendor/orgchart/src/css/jquery.orgchart.css')}}">
<style>
    #chart-container {
        position: relative;
        height: 420px;
        border: 1px solid #aaa;
        margin: 0.5rem;
        overflow: auto;
        text-align: center;
    }
    .title {
        width: 100% !important;
        min-width: 160px;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-12">
        <h1>Organization Structure</h1>
    </div>

    <div class="col-md-6">
        <a href="{{route('organization-structure.index', ['type' => 'NKAG'])}}" class="btn {{$type == 'NKAG' ? 'btn-primary' : 'btn-default'}}">NKAG</a>
        <a href="{{route('organization-structure.index', ['type' => 'RDG'])}}" class="btn {{$type == 'RDG' ? 'btn-primary' : 'btn-default'}}">RDG</a>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Organizational Chart</h3>
            </div>
            <div class="card-body">
                <div id="chart-container"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <livewire:organizational-structure.structure-list :type="$type"/>
    </div>
    <div class="col-lg-4">
        <livewire:organizational-structure.job-titles/>
    </div>
    
</div>

<div class="modal fade" id="job-title-modal">
    <div class="modal-dialog">
        <livewire:organizational-structure.job-title-form/>
    </div>
</div>

<div class="modal fade" id="structure-modal">
    <div class="modal-dialog modal-lg">
        <livewire:organizational-structure.structure-form :type="$type"/>
    </div>
</div>
@endsection

@section('js')
<script src="{{asset('/vendor/orgchart/src/js/jquery.orgchart.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    $(function() {
        $('#btn-job-title-add').on('click', function(e) {
            e.preventDefault();
            $('#job-title-modal').modal('show');
        });

        $('#btn-structure-add').on('click', function(e) {
            e.preventDefault();
            Livewire.emit('setStructureForm', 0);
            $('#structure-modal').modal('show');
        });

        $('body').on('click', '.btn-structure-edit', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('setStructureForm', id);
            $('#structure-modal').modal('show');
        });
    })

    // chart
    $(function() {

        var datasource = @php echo json_encode($chart_data); @endphp;

        $('#chart-container').orgchart({
            'data' : datasource,
            'depth': 2,
            'nodeTitle': 'name',
            'nodeContent': 'title',
            'exportButton': true,
            'exportFileExtension': 'pdf',
            'exportFilename': 'OrgChart-{{$type}}',
            'pan': true,
            'zoom': true
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
