<?php

namespace App\Support;

use App\Models\SalesOrderCancelRequest;
use App\Services\SalesOrderCancelRequestService;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

/**
 * Puts the number of Pending cancel requests on the "SO Cancel Requests" sidebar item, for the
 * SCM users who process them.
 */
class SoCancelRequestMenuFilter implements FilterInterface
{
    public const MENU_KEY = 'so-cancel-request';

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public function transform($item)
    {
        if (($item['key'] ?? null) !== self::MENU_KEY) {
            return $item;
        }

        $user = auth()->user();
        if (!$user || !$user->can(SalesOrderCancelRequestService::PROCESS_PERMISSION)) {
            return $item;
        }

        $pending = SalesOrderCancelRequest::where('status', SalesOrderCancelRequest::STATUS_PENDING)->count();
        if ($pending > 0) {
            $item['label']       = $pending;
            $item['label_color'] = 'warning';
        }

        return $item;
    }
}
