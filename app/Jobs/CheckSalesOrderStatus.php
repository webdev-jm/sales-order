<?php

namespace App\Jobs;

use App\Http\Traits\UsesSessionDatabaseConnection;
use App\Models\SalesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

/**
 * The order page queues this on every view while the order has no upload status. ShouldBeUnique keeps one
 * check per order waiting at a time, so refreshing the page no longer fills the queue with duplicates.
 */
class CheckSalesOrderStatus implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use UsesSessionDatabaseConnection;

    public int $tries   = 3;
    public int $timeout = 60;

    /**
     * Seconds the lock is kept if the job never runs, so a stuck queue does not block checks for good.
     */
    public int $uniqueFor = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(public SalesOrder $order)
    {
        $this->captureDatabaseConnection();
    }

    /**
     * Order ids repeat across the live and test databases, so the connection is part of the key.
     */
    public function uniqueId(): string
    {
        return $this->db_connection . ':' . $this->order->getKey();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->withDatabaseConnection(fn() => $this->checkStatus());
    }

    private function checkStatus(): void
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
                throw new \RuntimeException('HTTP request failed with status: ' . $response->status());
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
