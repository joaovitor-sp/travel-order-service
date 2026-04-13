<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\TravelOrderRepositoryImpl;
use App\Domain\Models\TravelOrder;
use Illuminate\Support\Facades\Cache;

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

        Cache::tags(['travel_orders'])->flush();

        return $travelOrder;
    }
}