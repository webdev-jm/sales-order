<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\User;
use App\Models\Branch;

class Detail extends Component
{
    public $year, $month, $last_day, $lines;

    protected $listeners = [
        'setDate' => 'setDate'
    ];

    public function addLine($date) {
        $this->lines[$date]['lines'][] = [
            'location' => '',
            'account_id' => '',
            'purpose' => '',
            'user_id' => ''
        ];
    }

    public function setDate($year, $month) {
        $this->year = $year;
        $this->month = $month;

        $this->last_day = date('t', strtotime($this->year.'-'.$this->month.'-01'));

        $this->setLine();
    }

    public function setLine() {
        $lines = [];
        for($i = 1; $i <= (int)$this->last_day; $i++) {
            $date = $this->year.'-'.$this->month.'-'.($i < 10 ? '0'.$i : $i);
            $day = date('D', strtotime($date));
            $class = '';
            if($day == 'Sun') {
                $class = 'bg-navy';
            } else if($day == 'Sat') {
                $class = 'bg-secondary';
            }

            $lines[$date] = [
                'day' => $day,
                'date' => date('M', strtotime($this->year.'-'.$this->month.'-01')).'. '.($i < 10 ? '0'.$i : $i),
                'class' => $class,
                'lines' => [
                    [
                        'location' => '',
                        'account_id' => '',
                        'purpose' => '',
                        'user_id' => ''
                    ]
                ]
            ];
        }
        $this->lines = $lines;
    }

    public function mount() {
        if(empty($this->year)) {
            $this->year = date('Y');
        }

        if(empty($this->month)) {
            $this->month = date('m');
        }

        $this->last_day = date('t', strtotime($this->year.'-'.$this->month.'-01'));

        $this->setLine();
        
    }

    public function render()
    {
        $branches = Branch::orderBy('branch_code')
        ->whereHas('account', function($query) {
            $query->whereHas('users', function($qry) {
                $qry->where('id', auth()->user()->id);
            });
        })
        ->limit(10)->get();
        
        $users = User::orderBy('firstname', 'ASC')
        ->get();

        return view('livewire.activity-plan.detail')->with([
            'branches' => $branches,
            'users' => $users,
        ]);
    }
}
