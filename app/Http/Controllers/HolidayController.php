<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Holiday;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        $calendar_data = [];
        $currentYear   = Carbon::now()->year;

        $calendarId = urlencode('en.philippines#holiday@group.v.calendar.google.com');
        $timeMin    = Carbon::now()->startOfYear()->toRfc3339String();
        $timeMax    = Carbon::now()->endOfYear()->toRfc3339String();

        $cacheKey    = "google_calendar_holidays_{$currentYear}";
        $googleItems = Cache::get($cacheKey);

        if ($googleItems === null) {
            $response = Http::withHeaders([
                'Referer' => config('app.url'),
            ])->get("https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events", [
                'key'          => config('services.google_calendar.api_key'),
                'timeMin'      => $timeMin,
                'timeMax'      => $timeMax,
                'singleEvents' => 'true',
                'orderBy'      => 'startTime',
            ]);

            if ($response->successful()) {
                $googleItems = $response->json('items') ?? [];
                Cache::put($cacheKey, $googleItems, now()->addDay());
            }
        }

        if ($googleItems !== null) {
            foreach ($googleItems as $item) {
                $calendar_data[] = [
                    'title'  => $item['summary'] ?? '',
                    'start'  => $item['start']['date'] ?? ($item['start']['dateTime'] ?? ''),
                    'allDay' => isset($item['start']['date']),
                    'color'  => '#3498db',
                ];
            }
        }

        Holiday::where('year', $currentYear)
            ->orWhere('repeat', 1)
            ->get()
            ->each(function (Holiday $holiday) use (&$calendar_data, $currentYear) {
                $year = $holiday->repeat ? $currentYear : $holiday->year;
                $calendar_data[] = [
                    'title'  => $holiday->title,
                    'start'  => Carbon::createFromDate($year, $holiday->month, $holiday->day)->toDateString(),
                    'allDay' => true,
                    'color'  => '#e74c3c',
                ];
            });

        return view('holidays.index', compact('calendar_data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreHolidayRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreHolidayRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function show(Holiday $holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function edit(Holiday $holiday)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateHolidayRequest  $request
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateHolidayRequest $request, Holiday $holiday)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function destroy(Holiday $holiday)
    {
        //
    }
}
