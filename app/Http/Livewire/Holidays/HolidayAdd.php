<?php

namespace App\Http\Livewire\Holidays;

use App\Models\Holiday;
use Livewire\Component;

class HolidayAdd extends Component
{
    protected $listeners = [
        'setHolidayAdd'  => 'setDate',
        'openAddHoliday' => 'clearDate',
    ];

    public ?int $year  = null;
    public ?int $month = null;
    public ?int $day   = null;
    public ?string $date  = null;
    public ?string $title = null;
    public bool $repeat     = false;
    public bool $is_work_day = false;

    public function setDate(string $date): void
    {
        $this->date  = $date;
        $parts       = explode('-', $date);
        $this->year  = (int) $parts[0];
        $this->month = (int) $parts[1];
        $this->day   = (int) $parts[2];
    }

    public function clearDate(): void
    {
        $this->reset(['date', 'year', 'month', 'day', 'title', 'repeat', 'is_work_day']);
    }

    public function addHoliday(): void
    {
        $this->validate([
            'month'       => ['required', 'integer', 'between:1,12'],
            'day'         => ['required', 'integer', 'between:1,31'],
            'title'       => ['required', 'string', 'max:255'],
            'repeat'      => ['boolean'],
            'is_work_day' => ['boolean'],
        ]);

        Holiday::create([
            'year'        => $this->year,
            'month'       => $this->month,
            'day'         => $this->day,
            'title'       => $this->title,
            'repeat'      => $this->repeat,
            'is_work_day' => $this->is_work_day,
            'source'      => 'custom',
        ]);

        $this->clearDate();
        $this->emit('closeAddHoliday');
        session()->flash('message_success', 'Holiday was added.');
        $this->redirect(request()->header('Referer'));
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.holidays.holiday-add');
    }
}
