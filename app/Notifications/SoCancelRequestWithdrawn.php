<?php

namespace App\Notifications;

/** To SCM: the salesman withdrew a pending cancel request. */
class SoCancelRequestWithdrawn extends SoCancelRequestNotification
{
    protected function subject(): string
    {
        return 'SO Cancel Request ' . $this->cancelRequest->request_number . ' Withdrawn';
    }

    protected function message(): string
    {
        return $this->requestorName() . ' withdrew cancel request ' . $this->cancelRequest->request_number
            . ' for ' . $this->orderLabel() . '. No action is needed.';
    }

    protected function color(): string
    {
        return 'secondary';
    }
}
