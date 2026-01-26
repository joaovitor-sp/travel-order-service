<?php

namespace App\Application\UseCases;

use App\Domain\Models\TravelOrder;
use App\Domain\Events\TravelOrderStatusUpdated;

/**
 * Use case to approve a travel order.
 */
class ApproveTravelOrder
{
    public function handle(TravelOrder $travelOrder): TravelOrder
    {

        $travelOrder->approve();
        $travelOrder->save();

        event(new TravelOrderStatusUpdated($travelOrder));
        
        return $travelOrder;
    }
}