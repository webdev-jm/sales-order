<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Job Titles</h3>
            <div class="card-tools">
                <a href="" class="btn btn-primary btn-sm" id="btn-job-title-add"><i class="fa fa-plus mr-1"></i>Add Job Title</a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-borderd table-sm">
                <thead>
                    <tr>
                        <th>Job Title</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($job_titles as $job_title)
                    <tr>
                        <td>{{$job_title->job_title}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$job_titles->links()}}
        </div>
    </div>
</div>
