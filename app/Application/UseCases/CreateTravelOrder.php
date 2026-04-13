<?php

namespace App\Application\UseCases;

use App\Domain\Models\TravelOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use App\Domain\Models\User;
use Illuminate\Support\Facades\Cache;

class CreateTravelOrder
{
    public function handle(array $data, User $user): TravelOrder
    {
        if (!$user || empty($user->id)) {
            throw new AuthenticationException();
        }

        // Always trust the authenticated user context
        $data['user_id'] = $user->id;

        $order = TravelOrder::create($data);
        Cache::tags(['travel_orders'])->flush();

        return $order;
    }
}