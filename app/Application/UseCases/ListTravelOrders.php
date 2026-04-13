<?php

namespace App\Application\UseCases;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domain\Models\TravelOrder;
use Illuminate\Support\Facades\Cache;

class ListTravelOrders
{
    public function handle(array $filters, int $perPage, string $userId, bool $isAdmin): LengthAwarePaginator
    {
        $cacheKey = 'travel_orders.list:' . md5(json_encode([
            'filters' => $filters,
            'per_page' => $perPage,
            'user_id' => $userId,
            'is_admin' => $isAdmin,
        ]));

        $ttlSeconds = (int) env('CACHE_TTL_SECONDS', 30);

        return Cache::tags(['travel_orders'])->remember($cacheKey, $ttlSeconds, function () use ($filters, $perPage, $userId, $isAdmin) {
            $query = TravelOrder::query();

            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['destination'])) {
                $query->where('destination', 'ilike', '%' . $filters['destination'] . '%');
            }

            if (!empty($filters['created_from'])) {
                $query->whereDate('created_at', '>=', $filters['created_from']);
            }

            if (!empty($filters['created_to'])) {
                $query->whereDate('created_at', '<=', $filters['created_to']);
            }

            if (!empty($filters['departure_date_from'])) {
                $query->whereDate('departure_date', '>=', $filters['departure_date_from']);
            }

            if (!empty($filters['departure_date_to'])) {
                $query->whereDate('departure_date', '<=', $filters['departure_date_to']);
            }

            if (!empty($filters['return_date_from'])) {
                $query->whereDate('return_date', '>=', $filters['return_date_from']);
            }

            if (!empty($filters['return_date_to'])) {
                $query->whereDate('return_date', '<=', $filters['return_date_to']);
            }

            return $query->paginate($perPage);
        });
    }
}