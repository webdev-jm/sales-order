<?php

namespace App\Http\Livewire\Holidays;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class HolidayIndex extends Component
{
    public string $activeView = 'list';

    public function switchView(string $view): void
    {
        $this->activeView = $view;
    }

    /**
     * Toggle a Philippine holiday between work and no-work.
     * Creates an override record if none exists, or flips is_work_day on the existing one.
     */
    public function toggleWorkDay(string $date, string $title): void
    {
        $parsed = Carbon::parse($date);

        $holiday = Holiday::where('source', 'philippine')
            ->where('year', $parsed->year)
            ->where('month', $parsed->month)
            ->where('day', $parsed->day)
            ->first();

        if ($holiday) {
            $holiday->update(['is_work_day' => ! $holiday->is_work_day]);
        } else {
            Holiday::create([
                'year'        => $parsed->year,
                'month'       => $parsed->month,
                'day'         => $parsed->day,
                'title'       => $title,
                'repeat'      => false,
                'is_work_day' => true,
                'source'      => 'philippine',
            ]);
        }
    }

    /**
     * Delete a custom holiday by ID.
     */
    public function deleteHoliday(int $id): void
    {
        $holiday = Holiday::where('id', $id)->where('source', 'custom')->firstOrFail();
        $holiday->delete();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $currentYear  = Carbon::now()->year;
        $googleHolidays = $this->fetchGoogleHolidays();

        $overrides = Holiday::where('source', 'philippine')
            ->where(function ($query) use ($currentYear) {
                $query->where('year', $currentYear)->orWhere('repeat', true);
            })
            ->get()
            ->keyBy(fn (Holiday $h) => Carbon::createFromDate(
                $h->repeat ? $currentYear : $h->year,
                $h->month,
                $h->day
            )->toDateString());

        $googleList = collect($googleHolidays)->map(function (array $item) use ($overrides): array {
            $date      = $item['start']['date'] ?? ($item['start']['dateTime'] ?? '');
            $override  = $overrides->get($date);

            return [
                'date'        => $date,
                'title'       => $item['summary'] ?? '',
                'type'        => 'philippine',
                'is_work_day' => $override ? (bool) $override->is_work_day : false,
                'id'          => $override ? $override->id : null,
            ];
        });

        $customList = Holiday::where('source', 'custom')
            ->where(function ($query) use ($currentYear) {
                $query->where('year', $currentYear)
                    ->orWhere('repeat', true)
                    ->orWhereNull('year');
            })
            ->orderBy('month')
            ->orderBy('day')
            ->get()
            ->map(fn (Holiday $h): array => [
                'date'        => Carbon::createFromDate(
                    $h->repeat || is_null($h->year) ? $currentYear : $h->year,
                    $h->month,
                    $h->day
                )->toDateString(),
                'title'       => $h->title,
                'type'        => 'custom',
                'is_work_day' => (bool) $h->is_work_day,
                'id'          => $h->id,
            ]);

        $mergedList = $googleList->merge($customList)->sortBy('date')->values();

        $calendarData = $this->buildCalendarData($googleList, $customList);

        return view('livewire.holidays.holiday-index', [
            'mergedList'   => $mergedList,
            'calendarData' => $calendarData,
        ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $googleList
     * @param  Collection<int, array<string, mixed>>  $customList
     * @return array<int, array<string, mixed>>
     */
    protected function buildCalendarData(Collection $googleList, Collection $customList): array
    {
        $events = [];

        foreach ($googleList as $item) {
            $events[] = [
                'title'  => $item['title'],
                'start'  => $item['date'],
                'allDay' => true,
                'color'  => $item['is_work_day'] ? '#27ae60' : '#3498db',
            ];
        }

        foreach ($customList as $item) {
            $events[] = [
                'title'  => $item['title'],
                'start'  => $item['date'],
                'allDay' => true,
                'color'  => '#e74c3c',
            ];
        }

        return $events;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchGoogleHolidays(): array
    {
        $currentYear = Carbon::now()->year;
        $cacheKey    = "google_calendar_holidays_{$currentYear}";
        $items       = Cache::get($cacheKey);

        if ($items === null) {
            $calendarId = urlencode('en.philippines#holiday@group.v.calendar.google.com');
            $response   = Http::withHeaders(['Referer' => config('app.url')])->get(
                "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events",
                [
                    'key'          => config('services.google_calendar.api_key'),
                    'timeMin'      => Carbon::now()->startOfYear()->toRfc3339String(),
                    'timeMax'      => Carbon::now()->endOfYear()->toRfc3339String(),
                    'singleEvents' => 'true',
                    'orderBy'      => 'startTime',
                ]
            );

            if ($response->successful()) {
                $items = $response->json('items') ?? [];
                Cache::put($cacheKey, $items, now()->addDay());
            }
        }

        return $items ?? [];
    }
}
