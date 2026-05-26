<?php

namespace App\Http\Livewire\War;

use Livewire\Component;

use App\Models\Holiday;
use App\Models\User;
use App\Models\Area;
use App\Models\ActivityPlanDetail;
use App\Models\BranchLogin;
use App\Models\WeeklyActivityReport;
use App\Models\WeeklyActivityReportBranch;
use App\Models\UserBranchSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WarForm extends Component
{
    public $areas, $user, $weekly_activity_report, $area_lines;
    public $date_from, $date_to;
    public $accounts_visited, $highlights;
    public $type = 'add_war';
    public $war;
    public $attachment_view;
    public $accounts_arr = [];

    public function showAttachments($model_id, $type) {
        if(isset($this->attachment_view[$type][$model_id]) && $this->attachment_view[$type][$model_id] == 1) {
            $this->attachment_view[$type][$model_id] = 0;
        } else {
            $this->attachment_view[$type][$model_id] = 1;
        }
    }

    public function showDetail($login_id) {
        $this->emit('showDetail', $login_id);
        $this->dispatchBrowserEvent('showDetail');
    }

    public function changeDate() {
        $this->reset('area_lines');

        $from = new \DateTime($this->date_from);
        $to = new \DateTime($this->date_to);
        $interval = $from->diff($to);

        $days = $interval->d;

        $holiday_map = $this->buildHolidayMap($this->date_from, $this->date_to);

        $start_date = $this->date_from;
        for($i = 0; $i <= $days; $i++) {

            // get schedules
            $schedules = UserBranchSchedule::with('branch')
                ->where('date', $start_date)
                ->where('user_id', $this->user->id)
                ->get();

            // get actual logins
            $branch_logins = BranchLogin::with('branch')
                ->where('user_id', $this->user->id)
                ->where('time_in', 'like', $start_date.'%')
                ->get();
            
            $area_arr = [];
            $deviations = [];
            foreach($branch_logins as $login) {
                $area_arr[] = $login->branch->area->area_name ?? '';
                $schedule = $schedules->where('branch_id', $login->branch_id);
                if(empty($schedule->count())) {
                    $deviations[] = $login;
                }

                $this->accounts_arr[$login->branch->account_id] = $login->branch->account->short_name;
            }

            $schedules_data = [];
            $schedules_visited = [];
            foreach($schedules as $schedule) {
                $visited = $branch_logins->where('branch_id', $schedule->branch_id)->first();
                if(!empty($visited)) {
                    $schedules_data[] = $schedule;
                    $schedules_visited[$schedule->id] = $visited;
                } else {
                    $schedules_data[] = $schedule;
                    $schedules_visited[$schedule->id] = null;
                }

                if ($schedule->branch_id !== null && $schedule->branch && $schedule->branch->account) {
                    $this->accounts_arr[$schedule->branch->account->id] = $schedule->branch->account->short_name;
                }
            }

            $action_points_arr = [];
            $attachments_arr = [];
            $activities = '';
            if(!empty($this->war)) {
                $area = $this->war->areas()->where('date', $start_date)->first();
                if(!empty($area)) {
                    $activities = $area->remarks ?? '';
                    $war_branches = $area->war_branches;
                    $action_points_arr[$start_date][] = $war_branches ?? NULL;

                    if(empty($war_branches->count())) {

                    }

                    if(!empty($war_branches)) {
                        foreach($war_branches as $war_branch) {
                            $attachments = $war_branch->attachments;
                            $attachments_arr[$start_date][$war_branch->branch_id][] = $attachments ?? NULL;
                        }
                    }
                }
            }

            // clean array
            $area_arr = array_unique(array_filter($area_arr));

            $is_on_leave = ActivityPlanDetail::where('is_on_leave', true)
                ->where('date', $start_date)
                ->whereHas('activity_plan', function ($q) {
                    $q->where('status', 'approved')
                      ->where('user_id', $this->user->id);
                })
                ->exists();

            $this->area_lines[] = [
                'date'             => $start_date,
                'day'              => date('l', strtotime($start_date)),
                'area'             => implode(', ', $area_arr),
                'activities'       => $activities,
                'schedules'        => $schedules_data,
                'schedules_visited' => $schedules_visited,
                'deviations'       => $deviations,
                'action_points_arr' => $action_points_arr,
                'attachments_arr'  => $attachments_arr,
                'on_leave'         => $is_on_leave,
                'holiday'          => $holiday_map[$start_date] ?? null,
            ];

            $start_date = date('Y-m-d', strtotime($start_date.' + 1 days'));
        }
    }

    public function mount($user_id, $war) {
        $this->war = $war;
        if(!empty($war)) {
            $this->date_from = $war->date_from;
            $this->date_to = $war->date_to;
            $this->accounts_visited = $war->accounts_visited;
            $this->type = 'update_war';
            $this->highlights = $war->highlights;
        }

        // area options
        $areas = Area::orderBy('area_code', 'ASC')
            ->get();

        $areas_arr = [
            '' => ''
        ];
        foreach($areas as $area) {
            $areas_arr[$area->id] = '['.$area->area_code.'] '.$area->area_name;
        }
        $this->areas = $areas_arr;

        if(empty($this->date_from)) {
            $this->date_from = date('Y-m-d');
        }
        if(empty($this->date_to)) {
            $this->date_to = date('Y-m-d');
        }

        $this->user = User::find($user_id);

        $this->changeDate();
    }

    /**
     * @return array<string, string>
     */
    private function buildHolidayMap(string $date_from, string $date_to): array
    {
        $holidays    = [];
        $currentYear = Carbon::parse($date_from)->year;
        $cacheKey    = "google_calendar_holidays_{$currentYear}";

        $googleItems = Cache::get($cacheKey);

        if ($googleItems === null) {
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
                $googleItems = $response->json('items') ?? [];
                Cache::put($cacheKey, $googleItems, now()->addDay());
            }
        }

        $googleItems = $googleItems ?? [];

        $overrides = Holiday::where('source', 'philippine')
            ->where(function ($q) use ($currentYear) {
                $q->where('year', $currentYear)->orWhere('repeat', true);
            })
            ->get()
            ->keyBy(fn (Holiday $h) => Carbon::createFromDate(
                $h->repeat ? $currentYear : $h->year,
                $h->month,
                $h->day
            )->toDateString());

        foreach ($googleItems as $item) {
            $date = $item['start']['date'] ?? ($item['start']['dateTime'] ?? '');

            if ($date < $date_from || $date > $date_to) {
                continue;
            }

            $override  = $overrides->get($date);
            $isWorkDay = $override ? (bool) $override->is_work_day : false;

            if (! $isWorkDay) {
                $holidays[$date] = $item['summary'] ?? '';
            }
        }

        $customHolidays = Holiday::where('source', 'custom')
            ->where(function ($q) use ($currentYear) {
                $q->where('year', $currentYear)
                  ->orWhere('repeat', true)
                  ->orWhereNull('year');
            })
            ->get();

        foreach ($customHolidays as $holiday) {
            $date = Carbon::createFromDate(
                ($holiday->repeat || is_null($holiday->year)) ? $currentYear : $holiday->year,
                $holiday->month,
                $holiday->day
            )->toDateString();

            if ($date < $date_from || $date > $date_to) {
                continue;
            }

            if (! $holiday->is_work_day) {
                $holidays[$date] = $holiday->title;
            }
        }

        return $holidays;
    }

    public function render()
    {
        return view('livewire.war.war-form');
    }
}
