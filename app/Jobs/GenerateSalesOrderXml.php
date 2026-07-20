<?php

namespace App\Jobs;

use App\Models\SalesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Traits\SoXmlTrait;
use App\Http\Traits\UsesSessionDatabaseConnection;
use Illuminate\Support\Facades\Log;

class GenerateSalesOrderXml implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use SoXmlTrait;
    use UsesSessionDatabaseConnection;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(public SalesOrder $order)
    {
        $this->captureDatabaseConnection();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->withDatabaseConnection(fn() => $this->generateXml($this->order));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateSalesOrderXml failed', [
            'order_id'       => $this->order->id,
            'control_number' => $this->order->control_number,
            'error'          => $exception->getMessage(),
        ]);
    }
}
