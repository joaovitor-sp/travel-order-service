<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Domain\Models\TravelOrder;
use App\Http\Policies\TravelOrderPolicy;
use App\Domain\Events\TravelOrderStatusUpdated;
use App\Application\Listeners\QueueTravelOrderStatusNotification;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(TravelOrder::class, TravelOrderPolicy::class);

        Event::listen(TravelOrderStatusUpdated::class, QueueTravelOrderStatusNotification::class);
    }
}
