<?php

namespace App\Http\Livewire\OrganizationalStructure;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\JobTitle;

class JobTitles extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $job_titles = JobTitle::orderBy('id', 'DESC')
        ->paginate(10, ['*'], 'job-title-page')->onEachSide(1);

        return view('livewire.organizational-structure.job-titles')->with([
            'job_titles' => $job_titles
        ]);
    }
}
