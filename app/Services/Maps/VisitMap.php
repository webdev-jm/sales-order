<?php

namespace App\Services\Maps;

use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Data for the MCP MAP report: where each branch sign-in happened compared with the branch's pinned address,
 * and which scheduled branches were visited.
 *
 * A schedule counts the way the MCP dashboard counts it: no status and an objective other than on-leave.
 * It is visited when the same user signed in to the same branch on the same day.
 */
final class VisitMap
{
    /**
     * Sign-ins this close to the branch pin are on site; up to NEAR_M they are near; further away, off site.
     */
    public const ON_SITE_M = 200;

    public const NEAR_M = 1000;

    public const STATUSES = [
        'on_site'  => 'On site',
        'near'     => 'Near',
        'off_site' => 'Off site',
        'no_pin'   => 'Branch not pinned',
        'no_gps'   => 'No GPS',
    ];

    /**
     * @param array<int>|null $userIds users the viewer may see, or null for everyone
     * @return array{visits: array<int, array<string, mixed>>, branches: array<int, array<string, mixed>>, summary: array<string, int|float|null>, users: array<int, array<string, mixed>>}
     */
    public static function build(?string $dateFrom, ?string $dateTo, ?string $userId, ?array $userIds): array
    {
        $logins = BranchLogin::query()
            ->select(['id', 'user_id', 'branch_id', 'latitude', 'longitude', 'accuracy', 'time_in', 'time_out'])
            ->with(['user:id,firstname,lastname', 'branch:id,account_id,branch_code,branch_name', 'branch.account:id,short_name', 'branch.addresses'])
            ->when(!empty($dateFrom), fn ($query) => $query->where('time_in', '>=', $dateFrom . ' 00:00:00'))
            ->when(!empty($dateTo), fn ($query) => $query->where('time_in', '<=', $dateTo . ' 23:59:59'))
            ->when(!empty($userId), fn ($query) => $query->where('user_id', $userId))
            ->when(!is_null($userIds), fn ($query) => $query->whereIn('user_id', $userIds))
            ->orderBy('time_in')
            ->get();

        $schedules = UserBranchSchedule::query()
            ->with(['user:id,firstname,lastname', 'branch:id,account_id,branch_code,branch_name', 'branch.account:id,short_name', 'branch.addresses'])
            ->when(!empty($dateFrom), fn ($query) => $query->where('date', '>=', $dateFrom))
            ->when(!empty($dateTo), fn ($query) => $query->where('date', '<=', $dateTo))
            ->when(!empty($userId), fn ($query) => $query->where('user_id', $userId))
            ->when(!is_null($userIds), fn ($query) => $query->whereIn('user_id', $userIds))
            ->get();

        $visitKeys = $logins->mapWithKeys(fn (BranchLogin $login) => [self::key($login->user_id, $login->branch_id, Carbon::parse($login->time_in)->toDateString()) => true]);
        $countedKeys = $schedules->filter(fn (UserBranchSchedule $s) => self::isCounted($s))
            ->mapWithKeys(fn (UserBranchSchedule $s) => [self::key($s->user_id, $s->branch_id, (string) $s->date) => true]);

        $visits = $logins->map(fn (BranchLogin $login) => self::visit($login, $countedKeys))->values()->all();
        $branches = $schedules->map(fn (UserBranchSchedule $s) => self::scheduledBranch($s, $visitKeys))->filter()->values()->all();

        return [
            'visits'   => $visits,
            'branches' => $branches,
            'summary'  => self::summary($visits, $schedules, $visitKeys),
            'users'    => self::perUser($visits, $schedules, $visitKeys),
        ];
    }

    /**
     * @param Collection<string, bool> $countedKeys
     * @return array<string, mixed>
     */
    private static function visit(BranchLogin $login, Collection $countedKeys): array
    {
        $pin = self::pin($login->branch);
        $hasGps = Geo::isUsable($login->latitude, $login->longitude);
        $distance = ($hasGps && $pin) ? Geo::distance((float) $login->latitude, (float) $login->longitude, $pin['latitude'], $pin['longitude']) : null;

        $status = match (true) {
            !$hasGps          => 'no_gps',
            $pin === null     => 'no_pin',
            $distance <= self::ON_SITE_M => 'on_site',
            $distance <= self::NEAR_M    => 'near',
            default           => 'off_site',
        };

        $date = Carbon::parse($login->time_in)->toDateString();

        return [
            'id'         => $login->id,
            'user_id'    => $login->user_id,
            'user'       => self::userName($login->user),
            'branch'     => self::branchName($login->branch),
            'lat'        => $hasGps ? (float) $login->latitude : null,
            'lon'        => $hasGps ? (float) $login->longitude : null,
            'pin_lat'    => $pin['latitude'] ?? null,
            'pin_lon'    => $pin['longitude'] ?? null,
            'time_in'    => (string) $login->time_in,
            'time_out'   => (string) $login->time_out,
            'accuracy_m' => Geo::accuracyMeters($login->accuracy),
            'distance_m' => $distance === null ? null : (int) round($distance),
            'status'     => $status,
            'scheduled'  => $countedKeys->has(self::key($login->user_id, $login->branch_id, $date)),
        ];
    }

    /**
     * @param Collection<string, bool> $visitKeys
     * @return array<string, mixed>|null
     */
    private static function scheduledBranch(UserBranchSchedule $schedule, Collection $visitKeys): ?array
    {
        $pin = self::pin($schedule->branch);

        if ($pin === null) {
            return null;
        }

        $state = !self::isCounted($schedule) ? 'not_counted'
            : ($visitKeys->has(self::key($schedule->user_id, $schedule->branch_id, (string) $schedule->date)) ? 'visited' : 'missed');

        return [
            'lat'       => $pin['latitude'],
            'lon'       => $pin['longitude'],
            'name'      => self::branchName($schedule->branch),
            'user'      => self::userName($schedule->user),
            'date'      => (string) $schedule->date,
            'objective' => (string) $schedule->objective,
            'source'    => (string) $schedule->source,
            'state'     => $state,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $visits
     * @param Collection<int, UserBranchSchedule> $schedules
     * @param Collection<string, bool> $visitKeys
     * @return array<string, int|float|null>
     */
    private static function summary(array $visits, Collection $schedules, Collection $visitKeys): array
    {
        $statuses = array_count_values(array_column($visits, 'status'));
        $counted = $schedules->filter(fn (UserBranchSchedule $s) => self::isCounted($s));
        $visited = $counted->filter(fn (UserBranchSchedule $s) => $visitKeys->has(self::key($s->user_id, $s->branch_id, (string) $s->date)))->count();
        $distances = array_values(array_filter(array_column($visits, 'distance_m'), fn ($d) => $d !== null));
        sort($distances);
        $measured = count($distances);

        return [
            'visits'          => count($visits),
            'users'           => count(array_unique(array_column($visits, 'user_id'))),
            'on_site'         => $statuses['on_site'] ?? 0,
            'near'            => $statuses['near'] ?? 0,
            'off_site'        => $statuses['off_site'] ?? 0,
            'no_pin'          => $statuses['no_pin'] ?? 0,
            'no_gps'          => $statuses['no_gps'] ?? 0,
            'on_site_rate'    => $measured ? round(100 * ($statuses['on_site'] ?? 0) / $measured, 1) : null,
            'median_distance' => $measured ? $distances[intdiv($measured, 2)] : null,
            'scheduled'       => $counted->count(),
            'visited'         => $visited,
            'missed'          => $counted->count() - $visited,
            'coverage_rate'   => $counted->count() ? round(100 * $visited / $counted->count(), 1) : null,
            'unscheduled'     => count(array_filter($visits, fn (array $v) => !$v['scheduled'])),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $visits
     * @param Collection<int, UserBranchSchedule> $schedules
     * @param Collection<string, bool> $visitKeys
     * @return array<int, array<string, mixed>>
     */
    private static function perUser(array $visits, Collection $schedules, Collection $visitKeys): array
    {
        $rows = [];

        foreach ($visits as $visit) {
            $rows[$visit['user_id']] ??= ['user' => $visit['user'], 'visits' => 0, 'on_site' => 0, 'near' => 0, 'off_site' => 0, 'no_pin' => 0, 'no_gps' => 0, 'scheduled' => 0, 'visited' => 0];
            $rows[$visit['user_id']]['visits']++;
            $rows[$visit['user_id']][$visit['status']]++;
        }

        foreach ($schedules->filter(fn (UserBranchSchedule $s) => self::isCounted($s)) as $schedule) {
            $rows[$schedule->user_id] ??= ['user' => self::userName($schedule->user), 'visits' => 0, 'on_site' => 0, 'near' => 0, 'off_site' => 0, 'no_pin' => 0, 'no_gps' => 0, 'scheduled' => 0, 'visited' => 0];
            $rows[$schedule->user_id]['scheduled']++;
            if ($visitKeys->has(self::key($schedule->user_id, $schedule->branch_id, (string) $schedule->date))) {
                $rows[$schedule->user_id]['visited']++;
            }
        }

        usort($rows, fn (array $a, array $b) => strcasecmp($a['user'], $b['user']));

        return $rows;
    }

    private static function isCounted(UserBranchSchedule $schedule): bool
    {
        return is_null($schedule->status) && $schedule->objective !== 'on-leave';
    }

    private static function key(mixed $userId, mixed $branchId, string $date): string
    {
        return $userId . '|' . $branchId . '|' . substr($date, 0, 10);
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private static function pin(mixed $branch): ?array
    {
        $address = $branch?->addresses?->first(fn ($address) => Geo::isUsable($address->latitude, $address->longitude));

        return $address ? ['latitude' => (float) $address->latitude, 'longitude' => (float) $address->longitude] : null;
    }

    private static function userName(mixed $user): string
    {
        return $user ? trim($user->firstname . ' ' . $user->lastname) : '-';
    }

    private static function branchName(mixed $branch): string
    {
        return $branch ? trim(($branch->account->short_name ?? '') . ' ' . $branch->branch_code . ' ' . $branch->branch_name) : '-';
    }
}
