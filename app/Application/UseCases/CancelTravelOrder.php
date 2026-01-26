<?php

namespace App\Application\UseCases;

use App\Domain\Models\TravelOrder;
use App\Domain\Events\TravelOrderStatusUpdated;

/**
 * Use case to cancel a travel order.
 */
class CancelTravelOrder
{

    public function handle( TravelOrder $travelOrder): TravelOrder
    {

        $travelOrder->cancel();
        $travelOrder->save();

        event(new TravelOrderStatusUpdated($travelOrder));

        return $travelOrder;
    }
}