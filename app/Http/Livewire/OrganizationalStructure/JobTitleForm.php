<?php

namespace App\Http\Livewire\OrganizationalStructure;

use Livewire\Component;

use App\Models\JobTitle;

use Illuminate\Validation\Rule;

class JobTitleForm extends Component
{
    public $job_title;

    public function submitForm() {
        $this->validate([
            'job_title' => [
                'required', Rule::unique((new JobTitle)->getTable())
            ]
        ]);

        $job_title = new JobTitle([
            'job_title' => $this->job_title
        ]);
        $job_title->save();

        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.organizational-structure.job-title-form');
    }
}
