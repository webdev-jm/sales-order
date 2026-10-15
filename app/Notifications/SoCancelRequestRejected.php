<?php

namespace App\Notifications;

/** To the requestor: SCM rejected the cancel request; the order stays as it is. */
class SoCancelRequestRejected extends SoCancelRequestNotification
{
    protected function subject(): string
    {
        return 'SO Cancel Request ' . $this->cancelRequest->request_number . ' Rejected';
    }

    protected function message(): string
    {
        return 'Your cancel request ' . $this->cancelRequest->request_number . ' for ' . $this->orderLabel()
            . ' was rejected. SCM remarks: ' . $this->cancelRequest->remarks;
    }

    protected function color(): string
    {
        return 'danger';
    }
}
