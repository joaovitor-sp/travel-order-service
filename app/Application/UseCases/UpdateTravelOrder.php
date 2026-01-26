<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\TravelOrderRepositoryImpl;
use App\Domain\Models\TravelOrder;

/**
 * Use case to update a travel order.
 */
class UpdateTravelOrder
{

    public function handle(TravelOrder $travelOrder, array $data): TravelOrder
    {
        $travelOrder->canUpdate();
        $travelOrder->fill($data);
        $travelOrder->save();

        return $travelOrder;
    }
}