<?php

namespace App\Application\UseCases;

use App\Domain\Models\TravelOrder;
use App\Domain\Events\TravelOrderStatusUpdated;
use Illuminate\Support\Facades\Cache;

/**
 * Use case to approve a travel order.
 */
class ApproveTravelOrder
{
    public function handle(TravelOrder $travelOrder): TravelOrder
    {

        $travelOrder->approve();
        $travelOrder->save();

        Cache::tags(['travel_orders'])->flush();

        event(new TravelOrderStatusUpdated($travelOrder));
        
        return $travelOrder;
    }
}