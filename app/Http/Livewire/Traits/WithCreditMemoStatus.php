<?php

namespace App\Http\Livewire\Traits;

trait WithCreditMemoStatus
{
    public $status_arr = [
        'draft' => 'secondary',
        'submitted' => 'info',
        'returned' => 'warning',
        'for approval' => 'primary',
        'rejected' => 'danger',
        'approved' => 'success'
    ];
}
