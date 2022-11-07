<?php

namespace App\Http\Livewire\OrganizationalStructure;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\OrganizationStructure;
use App\Models\JobTitle;

class StructureList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $type;

    public function mount($type) {
        $this->type = $type;
    }

    public function render()
    {
        $structures = OrganizationStructure::orderBy('reports_to_id', 'ASC')
        ->where('type', $this->type)
        ->paginate(10, ['*'], 'structure-page')->onEachSide(1);

        $reports_to_arr = [];
        foreach($structures as $structure) {
            if(!empty($structure->reports_to_id)) {
                $structure_data = OrganizationStructure::findOrFail($structure->reports_to_id);
                $reports_to_arr[$structure->id] = $structure_data->job_title->job_title.' - '.(!empty($structure_data->user_id) ? $structure_data->user->fullName() : 'Vacant');
            } else {
                $reports_to_arr[$structure->id] = '';
            }
        }
        
        return view('livewire.organizational-structure.structure-list')->with([
            'structures' => $structures,
            'reports_to_arr' => $reports_to_arr
        ]);
    }
}
