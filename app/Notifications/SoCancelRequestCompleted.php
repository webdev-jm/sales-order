<?php

namespace App\Notifications;

/** To the requestor: SCM cancelled the order in SYSPRO and SMS. */
class SoCancelRequestCompleted extends SoCancelRequestNotification
{
    protected function subject(): string
    {
        return 'SO Cancel Request ' . $this->cancelRequest->request_number . ' Completed';
    }

    protected function message(): string
    {
        return 'Your cancel request ' . $this->cancelRequest->request_number . ' is completed. '
            . $this->orderLabel() . ' is now cancelled.'
            . ($this->cancelRequest->remarks ? ' SCM remarks: ' . $this->cancelRequest->remarks : '');
    }

    protected function color(): string
    {
        return 'success';
    }
}
