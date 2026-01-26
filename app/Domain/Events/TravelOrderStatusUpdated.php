<?php

namespace App\Domain\Events;

use App\Domain\Models\TravelOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TravelOrderStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public TravelOrder $order) {}
}