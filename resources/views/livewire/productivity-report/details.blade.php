<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title mb-1">Details<i class="fa fa-spinner fa-spin ml-1" wire:loading></i></h3>
            <div class="card-tools">
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-upload" wire:model="upload_file" wire:loading.attr="readonly">
                        <label for="file-upload" class="custom-file-label">Choose file</label>
                    </div>
                    <div class="input-group-append">
                        <button class="btn btn-info" wire:click.prevent="uploadFile" wire:loading.attr="disabled"><i class="fa fa-download mr-1"></i>Upload</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive">
            
            @if(!empty($err))
                <ul class="list-group mb-2">
                @foreach($err as $err_msg)
                    <li class="list-group-item p-1 bg-danger text-center">{{$err_msg}}</li>
                @endforeach
                </ul>
            @endif
            
            <table class="table table-sm table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>Date</th>
                        <th>Salesman</th>
                        <th>Store</th>
                        <th>Classification</th>
                        <th>Visited</th>
                        <th>Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @if(empty($details))
                        <tr>
                            <td colspan="6" class="text-center">
                                No data has been uploaded yet.
                            </td>
                        </tr>
                    @else
                        @foreach($paginatedData as $detail)
                        <tr class="text-center">
                            <td>{{$detail['date']}}</td>
                            <td>{{$detail['salesman']}}</td>
                            <td>{{$detail['store']}}</td>
                            <td>{{$detail['classification']}}</td>
                            <td>
                                <span class="badge badge-{{$detail['visited'] == 1 ? 'success' : 'danger'}}">
                                    {{$detail['visited'] == 1 ? 'YES' : 'NO'}}
                                </span>
                            </td>
                            <td class="text-right">{{number_format($detail['sales'], 2)}}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
    
        </div>
        <div class="card-footer pb-0">
            {{$paginatedData->links()}}
        </div>
    </div>
</div>
