<?php

namespace App\Application\UseCases;

use App\Domain\Models\TravelOrder;
use DomainException;

/**
 * Use case to get a travel order by its ID.
 */
class GetTravelOrderById
{
    
    public function handle(int|string $id): TravelOrder
    {
        $travelOrder = TravelOrder::find($id);
        if (!$travelOrder) {
            throw new DomainException("Travel order not found");
        }
        return $travelOrder;
    }
}