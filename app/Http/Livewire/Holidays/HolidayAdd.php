<?php

namespace App\Http\Livewire\Holidays;

use Livewire\Component;

use App\Models\Holiday;

class HolidayAdd extends Component
{
    protected $listeners = [
        'setHolidayAdd' => 'setDate'
    ];

    public $year, $month, $day, $date;
    public $title, $repeat;

    public function addHoliday() {
        $this->validate([
            'month' => [
                'required'
            ],
            'day' => [
                'required'
            ],
            'title' => [
                'required'
            ],
        ]);

        $holiday = new Holiday([
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'title' => $this->title,
            'repeat' => $this->repeat ?? 0
        ]);
        $holiday->save();

        return redirect(request()->header('Referer'))->with([
            'message_success' => 'Holiday was added'
        ]);
    }

    public function setDate($date) {
        $this->date = $date;
        $date_arr = explode('-', $date);
        $this->year = $date_arr[0];
        $this->month = $date_arr[1];
        $this->day = $date_arr[2];
    }

    public function render()
    {
        return view('livewire.holidays.holiday-add');
    }
}
