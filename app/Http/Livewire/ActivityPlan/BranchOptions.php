<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;
use App\Models\Branch;

class BranchOptions extends Component
{
    public $query;
    public $branches;
    public $highlightIndex;
 
    public function mount()
    {
        $this->resetData();
    }
 
    public function resetData()
    {
        $this->query = '';
        $this->branches = [];
        $this->highlightIndex = 0;
    }
 
    public function incrementHighlight()
    {
        if ($this->highlightIndex === count($this->branches) - 1) {
            $this->highlightIndex = 0;
            return;
        }
        $this->highlightIndex++;
    }
 
    public function decrementHighlight()
    {
        if ($this->highlightIndex === 0) {
            $this->highlightIndex = count($this->branches) - 1;
            return;
        }
        $this->highlightIndex--;
    }
 
    public function selectContact()
    {
        $contact = $this->branches[$this->highlightIndex] ?? null;
        if ($contact) {
            $this->redirect(route('show-contact', $contact['id']));
        }
    }
 
    public function updatedQuery()
    {
        $this->branches = Branch::where('branch_name', 'like', '%' . $this->query . '%')
            ->orWhere('branch_code', 'like', '%'.$this->query.'%')
            ->limit(20)
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.activity-plan.branch-options');
    }
}
