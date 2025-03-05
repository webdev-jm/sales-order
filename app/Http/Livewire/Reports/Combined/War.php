<?php

namespace App\Http\Livewire\Reports\Combined;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\WeeklyActivityReport;

class War extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $month, $year, $user_id;

    protected $listeners = [
        'setWarDate' => 'setDate'
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
        $weekly_activity_reports = WeeklyActivityReport::orderBy('id', 'DESC')
            ->where('status', '<>', 'draft')
            ->when(!empty($this->month) && !empty($this->year), function($query) {
                $query->where(function($qry) {
                    $date_string = $this->year.'-'.$this->month.'-';
                    $qry->where('date_from', 'like', '%'.$date_string.'%')
                        ->orWhere('date_to', 'like', '%'.$date_string.'%');
                });
            })
            ->when(!empty($this->user_id), function($query) {
                $query->where('user_id', $this->user_id);
            })
            ->paginate(5, ['*'], 'war-page')->onEachSide(1);

        $status_arr = [
            'draft' => 'secondary',
            'submitted' => 'primary',
            'approved' => 'success',
            'rejected' => 'danger',
        ];

        return view('livewire.reports.combined.war')->with([
            'weekly_activity_reports' => $weekly_activity_reports,
            'status_arr' => $status_arr
        ]);
    }
}
