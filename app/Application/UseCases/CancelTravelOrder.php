<?php

namespace App\Application\UseCases;

use App\Domain\Models\TravelOrder;
use App\Domain\Events\TravelOrderStatusUpdated;
use Illuminate\Support\Facades\Cache;

/**
 * Use case to cancel a travel order.
 */
class CancelTravelOrder
{

    public function handle( TravelOrder $travelOrder): TravelOrder
    {

        $travelOrder->cancel();
        $travelOrder->save();

        Cache::tags(['travel_orders'])->flush();

        event(new TravelOrderStatusUpdated($travelOrder));

        return $travelOrder;
    }
}