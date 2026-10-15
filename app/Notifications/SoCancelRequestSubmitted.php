<?php

namespace App\Notifications;

/** To SCM: a salesman filed a cancel request. */
class SoCancelRequestSubmitted extends SoCancelRequestNotification
{
    protected function subject(): string
    {
        return 'SO Cancel Request ' . $this->cancelRequest->request_number;
    }

    protected function message(): string
    {
        $account = $this->cancelRequest->sales_order->account_login?->account;

        return $this->requestorName() . ' requested to cancel ' . $this->orderLabel()
            . ($account ? ' of ' . $account->short_name : '') . '. Reason: ' . $this->cancelRequest->reason;
    }

    protected function color(): string
    {
        return 'warning';
    }
}
