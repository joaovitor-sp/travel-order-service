<?php

namespace Database\Factories;

use App\Domain\Models\TravelOrder;
use App\Domain\Enums\TravelOrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelOrderFactory extends Factory
{
    protected $model = TravelOrder::class;

    public function definition(): array
    {
        return [
            'destination' => $this->faker->city(),
            'departure_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'return_date' => $this->faker->dateTimeBetween('+2 weeks', '+2 months'),
        ];
    }

    /**
     * Define requester manually (bypasses Model events).
     */
    public function forRequester(string $id, string $name): static
    {
        return $this;
    }

    /**
     * Set status.
     */
    public function withStatus(TravelOrderStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status->value,
        ]);
    }
}