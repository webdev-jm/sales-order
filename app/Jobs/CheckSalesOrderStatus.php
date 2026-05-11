<?php

namespace App\Jobs;

use App\Models\SalesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckSalesOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(public SalesOrder $order) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('syspro.token'),
                    'po_number'     => $this->order->po_number,
                    'company'       => $this->order->account_login->account->company->name,
                ])
                ->timeout(30)
                ->get(config('syspro.url') . '/so/sor_master');

            if ($response->failed()) {
                activity('error')->log('Request failed for PO: ' . $this->order->po_number . ' - ' . $response->status());
                $this->failed(new \RuntimeException('HTTP request failed with status: ' . $response->status())); //
                return;
            }

            $json = $response->json();

            if (!empty($json['data'])) {
                $reference = collect($json['data'])
                    ->map(fn($data) => ltrim($data['sales_order'], '0'))
                    ->filter()
                    ->implode(', ');

                activity('info')->log('Reference for PO Number: ' . $this->order->po_number . ' is ' . $reference);

                if (!empty($reference)) {
                    $this->order->update([
                        'upload_status' => 1,
                        'reference'     => $reference,
                    ]);
                }
            } else {
                activity('info')->log('No data found for PO Number: ' . $this->order->po_number . ' ' . $response->body());
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            activity('error')->log('Connection failed for PO: ' . $this->order->po_number . ' - ' . $e->getMessage());
            $this->release(30);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        activity('error')->log('CheckSalesOrderStatus job failed for PO: ' . $this->order->po_number . ' - ' . $exception->getMessage());
    }
}
