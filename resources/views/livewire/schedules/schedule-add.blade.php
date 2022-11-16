<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Schedule Request</h4>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" class="form-control @error('date') is-invalid @enderror" wire:model="date">
                        @error('date')
                            <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label for="">Objective</label>
                        <textarea class="form-control @error('objective') is-invalid @enderror" wire:model="objective"></textarea>
                        @error('objective')
                            <p class="text-danger">{{$message}}</p>
                        @enderror
                    </div>
                </div>

                {{-- search --}}
                <div class="col-12">
                    <hr class="mb-1">
                    <input type="text" class="form-control @error('branch_id') is-invalid @enderror" placeholder="Search" wire:model="search">
                    @error('branch_id')
                    <p class="text-danger">{{$message}}</p>
                    @enderror
                </div>

                <div class="col-lg-12">
                    <div class="row">
                        @foreach($branches as $branch)
                        <div class="col-lg-6 my-1">
                            <button class="btn btn-block {{isset($branch_id) && $branch_id == $branch->id ? 'btn-primary' : 'btn-default'}} h-100" wire:click.prevent="selectBranch({{$branch->id}})"  wire:loading.attr="disabled">{{$branch->branch_code}} {{$branch->branch_name}}</button>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-12 mt-2">
                    {{$branches->links()}}
                </div>

            </div>

        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button class="btn btn-primary" wire:click.prevent="submitRequest" wire:loading.attr="disabled">Request</button>
        </div>
    
        <script>
            document.addEventListener('livewire:load', function () {
            });
        </script>
    </div>
</div>