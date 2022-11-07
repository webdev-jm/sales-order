<?php

namespace App\Http\Livewire\OrganizationalStructure;

use Livewire\Component;

use App\Models\JobTitle;
use App\Models\OrganizationStructure;
use App\Models\User;

class StructureForm extends Component
{

    public $type, $job_titles, $users;
    public $job_title_id, $user_id, $reports_to_id;

    public $structure_data;

    protected $listeners = [
        'setStructureForm' => 'setForm'
    ];

    public function submitForm() {
        $this->validate([
            'job_title_id' => 'required',
            'type' => 'required'
        ]);

        if(!empty($this->structure_data)) {
            $this->structure_data->update([
                'job_title_id' => $this->job_title_id,
                'user_id' => $this->user_id ?? NULL,
                'reports_to_id' => $this->reports_to_id ?? NULL,
                'type' => $this->type
            ]);
        } else {
            $structure = new OrganizationStructure([
                'job_title_id' => $this->job_title_id,
                'user_id' => $this->user_id ?? NULL,
                'reports_to_id' => $this->reports_to_id ?? NULL,
                'type' => $this->type
            ]);
            $structure->save();
        }

        return redirect(request()->header('Referer'));
    }

    public function setForm($structure_id) {
        $this->structure_data = OrganizationStructure::find($structure_id);

        if(!empty($this->structure_data)) {
            $this->job_title_id = $this->structure_data->job_title_id;
            $this->user_id = $this->structure_data->user_id;
            $this->reports_to_id = $this->structure_data->reports_to_id;
        } else {
            $this->reset([
                'job_title_id',
                'user_id',
                'reports_to_id'
            ]);
        }
    }

    public function mount($type) {
        $this->type = $type;

        $this->job_titles = JobTitle::orderBy('id', 'DESC')->get();
        $this->users = User::orderBy('firstname', 'ASC')->get();
    }

    public function render()
    {
        if(!empty($this->structure_data)) {
            $this->structures = OrganizationStructure::orderBy('reports_to_id', 'ASC')
            ->where('id', '<>', $this->structure_data->id)
            ->where('type', $this->type)->get();
        } else {
            $this->structures = OrganizationStructure::orderBy('reports_to_id', 'ASC')
            ->where('type', $this->type)->get();
        }
        
        return view('livewire.organizational-structure.structure-form');
    }
}
