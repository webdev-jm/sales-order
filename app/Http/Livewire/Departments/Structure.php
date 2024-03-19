<?php

namespace App\Http\Livewire\Departments;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\User;
use App\Models\DepartmentStructure;

class Structure extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $department;
    public $tab;
    public $user_select, $designation, $reports_to_ids;
    public $form_type;
    public $edit_structure;
    public $structures_option;
    public $nodes, $chart_data;

    public function cancel() {
        $this->form_type = 'add';

        $this->reset([
            'user_select',
            'designation',
            'reports_to_ids'
        ]);
    }

    public function editStructure($structure_id) {
        $structure = DepartmentStructure::findOrFail($structure_id);
        $this->edit_structure = $structure;
        $this->user_select = $structure->user_id;
        $this->designation = $structure->designation;
        $this->reports_to_ids = explode(',', $structure->reports_to_ids);

        $this->form_type = 'edit';
    }

    public function submitStructure() {
        $this->validate([
            'user_select' => [
                'max:20'
            ],
            'designation' => [
                'required'
            ],
        ]);

        $user_ids = NULL;
        if(!empty($this->reports_to_ids)) {
            $this->reports_to_ids = array_filter($this->reports_to_ids, function ($item) {
                return $item !== 'NULL';
            });
            $user_ids = implode(',', $this->reports_to_ids);
        }

        if($this->form_type == 'add') {
            $structure = new DepartmentStructure([
                'department_id'=> $this->department->id,
                'user_id' => $this->user_select,
                'reports_to_ids' => $user_ids,
                'designation' => $this->designation, 
            ]);
            $structure->save();
        } else if($this->form_type == 'edit') {
            $this->edit_structure->update([
                'user_id' => $this->user_select,
                'reports_to_ids' => $user_ids,
                'designation' => $this->designation,
            ]);
        }

        $this->reset([
            'user_select',
            'designation',
            'reports_to_ids'
        ]);

        $this->form_type = 'add';
    }

    public function selectTab($tab) {
        $this->tab = $tab;
    }

    private function loadChart() {
        $this->structures_option = DepartmentStructure::where('department_id', $this->department->id)    
            ->get();

        $data = [
            ['HEAD', 'ADMIN']
        ];
        $nodes = [
            [
                'id' => 'HEAD',
                'title' => 'DEPARTMENT HEAD',
                'name' => $this->department->department_head->fullName() ?? '-'
            ],
            [
                'id' => 'ADMIN',
                'title' => 'DEPARTMENT ADMIN',
                'name' => $this->department->department_admin->fullName() ?? '-'
            ]
        ];
        
        foreach($this->structures_option as $structure) {
            $nodes[] = [
                'id' => $structure->id,
                'title' => strtoupper($structure->designation),
                'name' => $structure->user->fullName() ?? '-',
            ];

            if(!empty($structure->reports_to_ids)) {
                $ids = explode(',', $structure->reports_to_ids);
                foreach($ids as $id) {
                    $data[] = [(int)$id, $structure->id];
                }
            } else {
                $data[] = ['ADMIN', $structure->id];
            }
        }

        $this->dispatchBrowserEvent('load-chart', [
            'nodes' => $nodes,
            'data' => $data
        ]);

        $this->nodes = $nodes;
        $this->chart_data = $data;
    }

    public function mount($department) {
        $this->department = $department;
        $this->tab = 'detail';
        $this->form_type = 'add';
    }

    public function render()
    {
        $this->loadChart();

        $users = User::orderBy('firstname', 'ASC')
            ->where('department_id', $this->department->id)
            ->get();

        $structures = DepartmentStructure::where('department_id', $this->department->id)
            ->paginate(10, ['*'], 'structure-page')->onEachSide(1);

        return view('livewire.departments.structure')->with([
            'users' => $users,
            'structures' => $structures
        ]);
    }
}
