@extends('adminlte::page')

@section('title')
    Reports
@endsection

@section('css')
<style>
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Reports</h1>
    </div>
    <div class="col-md-6 text-right">
    </div>
</div>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <livewire:reports.combined.header/>
    </div>
    <div class="col-lg-12">
        <livewire:reports.combined.activity-plan/>
    </div>
    <div class="col-lg-12">
        <livewire:reports.combined.war/>
    </div>
    <div class="col-lg-12">
        <livewire:reports.combined.deviation/>
    </div>
</div>


<div class="modal fade" id="deviation-approval-modal">
    <div class="modal-dialog modal-xl">
        <livewire:schedules.schedule-deviation-approval/>
    </div>
</div>
@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '.btn-detail', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('setDeviationApproval', id);
            $('#deviation-approval-modal').modal('show');
        });
    })
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
