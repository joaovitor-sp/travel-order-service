<?php

namespace App\Application\Listeners;

use App\Domain\Events\TravelOrderStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Application\Jobs\SendTravelOrderStatusNotificationJob;

class QueueTravelOrderStatusNotification implements ShouldQueue
{
    public function handle(TravelOrderStatusUpdated $event): void
    {
        $order = $event->order;
        Log::info('Notification: travel order approved', [
            'order_id' => $order->id,
            'requester_id' => $order->requester_id ?? $order->user_id ?? null,
            'destination' => $order->destination,
            'status' => $order->status?->value ?? (string) $order->status,
        ]);

        SendTravelOrderStatusNotificationJob::dispatch($order);
    }
}