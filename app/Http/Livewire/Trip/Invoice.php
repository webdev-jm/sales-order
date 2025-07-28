<?php

namespace App\Http\Livewire\Trip;

use Livewire\Component;
use App\Models\ActivityPLanDetailTrip;

class Invoice extends Component
{
    public $trip;
    public $invoice, $supplier;

    protected $listeners = ['setTripId'];

    public function setTripId($id)
    {
        $this->trip = ActivityPLanDetailTrip::findOrFail($id);
        if (isset($this->trip)) {
            $this->invoice = $this->trip->invoice_number ?? '';
            $this->supplier = $this->trip->supplier ?? '';
        }
    }

    public function saveInvoice() {
        $this->validate([
            'invoice' => [
                'required',
                'max:255',
                'string',
            ],
            'supplier' => [
                'max:255',
                'string',
            ]
        ]);

        $this->trip->update([
            'invoice_number' => $this->invoice,
            'supplier' => $this->supplier,
        ]);

        return redirect(request()->header('Referer'));
    }
    
    public function render()
    {
        return view('livewire.trip.invoice');
    }
}
