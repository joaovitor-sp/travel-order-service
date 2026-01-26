<?php

namespace App\Application\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Domain\Models\TravelOrder;

class SendTravelOrderStatusNotificationJob implements ShouldQueue
{
    use Queueable;

    public TravelOrder $travelOrder;

    /**
     * Create a new job instance.
     */
    public function __construct(TravelOrder $travelOrder)
    {
        $this->travelOrder = $travelOrder;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $details = $this->formatTravelOrderDetails($this->travelOrder);
        // Lógica para enviar a notificação sobre o status da ordem de viagem
        Log::info("Job processado: [ " . json_encode($details) . " ]");
        echo "Job processado: [ " . json_encode($details) . " ]" . PHP_EOL;
    }

    private function formatTravelOrderDetails(TravelOrder $order): array
    {
        return [
            'id' => $order->id,
            'user_id' => $order->user_id,
            'destination' => $order->destination,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }
}
