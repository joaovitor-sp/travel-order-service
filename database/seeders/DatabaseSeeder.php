<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Domain\Models\TravelOrder;
use App\Domain\Enums\TravelOrderStatus;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        TravelOrder::withoutEvents(function () {
            $now = now()->format('Y-m-d H:i:s');
            
            DB::table('travel_orders')->insert([
                [
                    'user_id' => 'user-123',
                    'requester_name' => 'João Silva',
                    'destination' => 'São Paulo',
                    'departure_date' => '2026-02-10',
                    'return_date' => '2026-02-15',
                    'status' => TravelOrderStatus::REQUESTED->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'user_id' => 'user-123',
                    'requester_name' => 'João Silva',
                    'destination' => 'Rio de Janeiro',
                    'departure_date' => '2026-02-20',
                    'return_date' => '2026-02-25',
                    'status' => TravelOrderStatus::APPROVED->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'user_id' => 'user-123',
                    'requester_name' => 'João Silva',
                    'destination' => 'São Paulo',
                    'departure_date' => '2026-02-20',
                    'return_date' => '2026-02-20',
                    'status' => TravelOrderStatus::APPROVED->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'user_id' => 'admin-456',
                    'requester_name' => 'Admin User',
                    'destination' => 'Brasília',
                    'departure_date' => '2026-02-05',
                    'return_date' => '2026-02-08',
                    'status' => TravelOrderStatus::REQUESTED->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        });

        $this->command->info('Travel orders seeded!');
    }
}
