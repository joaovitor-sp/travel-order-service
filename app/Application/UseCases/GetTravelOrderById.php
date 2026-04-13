<?php

namespace App\Application\UseCases;

use App\Domain\Models\TravelOrder;
use DomainException;
use Illuminate\Support\Facades\Cache;

/**
 * Use case to get a travel order by its ID.
 */
class GetTravelOrderById
{
    
    public function handle(int|string $id): TravelOrder
    {
        $cacheKey = 'travel_orders.show:' . $id;
        $ttlSeconds = (int) env('CACHE_TTL_SECONDS', 30);

        $travelOrder = Cache::tags(['travel_orders'])->remember($cacheKey, $ttlSeconds, function () use ($id) {
            return TravelOrder::find($id);
        });

        if (!$travelOrder) {
            throw new DomainException("Travel order not found");
        }

        return $travelOrder;
    }
}