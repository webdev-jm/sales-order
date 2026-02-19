<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SearchDropdown extends Component
{
    public $model;
    public $searchFields;
    public $displayField; // Can now be String or Array
    public $valueField;
    public $label;
    public $placeholder;
    public $emitEvent;

    public $selected;

    public $search = '';
    public $results = [];
    public $showDropdown = false;

    public function mount($model, $searchFields = ['name'], $displayField = 'name', $valueField = 'id', $label = 'Search', $placeholder = 'Search...', $emitEvent = 'itemSelected', $selected = null)
    {
        $this->model = $model;
        $this->searchFields = $searchFields;
        $this->displayField = $displayField;
        $this->valueField = $valueField;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->emitEvent = $emitEvent;
        $this->selected = $selected;

        if ($this->selected) {
            $this->loadSelected();
        }
    }

    public function loadSelected()
    {
        $item = app($this->model)->find($this->selected);

        if ($item) {
            // Convert to array so our helper works
            $this->search = $this->getDisplayValue($item->toArray());
        }
    }

    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->results = [];
            $this->showDropdown = false;
            return;
        }

        $query = app($this->model)->query();

        $query->where(function($q) {
            foreach ($this->searchFields as $field) {
                $q->orWhere($field, 'like', '%' . $this->search . '%');
            }
        });

        $this->results = $query->take(10)->get()->toArray();
        $this->showDropdown = true;
    }

    // Helper to concatenate fields if array, or return single field if string
    public function getDisplayValue($item)
    {
        if (is_array($this->displayField)) {
            // Map over the fields and get values from the item array
            $values = array_map(function ($field) use ($item) {
                return $item[$field] ?? '';
            }, $this->displayField);

            // Join with a hyphen (e.g. "CUST001 - John Doe")
            return implode(' - ', array_filter($values));
        }

        return $item[$this->displayField];
    }

    public function selectItem($id)
    {
        // Find the selected item from results to get its full name again
        $selectedItem = collect($this->results)->firstWhere($this->valueField, $id);

        if ($selectedItem) {
            $displayText = $this->getDisplayValue($selectedItem);
            $this->search = $displayText;
        }

        $this->showDropdown = false;
        $this->emitUp($this->emitEvent, $id);
    }

    public function render()
    {
        return view('livewire.search-dropdown');
    }
}
