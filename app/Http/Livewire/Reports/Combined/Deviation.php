<?php

namespace App\Http\Livewire\Reports\Combined;

use Livewire\Component;
use Livewire\WithPagination;

use DeviationModel;

class Deviation extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $month, $year, $user_id;

    protected $listeners = [
        'setDeviationDate' => 'setDate'
    ];

    public function setDate($month, $year, $user_id) {
        $this->month = $month;
        $this->year = $year;
        $this->user_id = $user_id;
    }

    public function mount() {
        // set default date
        if(empty($this->year)) {
            $this->year = date('Y');
        }
        if(empty($this->month)) {
            $this->month = date('m');
        }
    }

    public function render()
    {
        $deviations = DeviationModel::orderBy('id', 'DESC');
        if(!empty($this->month) && !empty($this->year)) {
            $date_string = $this->year.'-'.$this->month.'-';
            $deviations->where('date', 'like', '%'.$date_string.'%');
        }
        if(!empty($this->user_id)) {
            $deviations->where('user_id', $this->user_id);
        }
        $deviations = $deviations->paginate(5, ['*'], 'deviation-page')
        ->onEachSide(1); 
        
        $status_arr = [
            'submitted' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ];
        
        return view('livewire.reports.combined.deviation')->with([
            'deviations' => $deviations,
            'status_arr' => $status_arr
        ]);
    }
}