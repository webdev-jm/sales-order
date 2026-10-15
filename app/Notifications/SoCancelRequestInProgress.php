<?php

namespace App\Notifications;

/** To the requestor: SCM started processing the cancel request. */
class SoCancelRequestInProgress extends SoCancelRequestNotification
{
    protected function subject(): string
    {
        return 'SO Cancel Request ' . $this->cancelRequest->request_number . ' In Progress';
    }

    protected function message(): string
    {
        $processor = $this->cancelRequest->processor?->fullName() ?? 'SCM';

        return $processor . ' is now processing your cancel request ' . $this->cancelRequest->request_number
            . ' for ' . $this->orderLabel() . '.';
    }

    protected function color(): string
    {
        return 'info';
    }
}
