<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domain\Models\TravelOrder;
use App\Domain\Enums\TravelOrderStatus;
use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

class TravelOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function getTokenForUser(string $userId, string $name, bool $isAdmin = false): string
    {
        $user = new User([
            'id' => $userId,
            'name' => $name,
            'is_admin' => $isAdmin,
        ]);

        return JWTAuth::fromUser($user);
    }

    public function test_can_list_own_travel_orders()
    {
        TravelOrder::withoutEvents(function () {
            DB::table('users')->insert([
                'id' => 'user-123',
                'name' => 'João Silva',
                'is_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('travel_orders')->insert([
                'user_id' => 'user-123',
                'destination' => 'São Paulo',
                'departure_date' => '2026-02-10',
                'return_date' => '2026-02-15',
                'status' => TravelOrderStatus::REQUESTED->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->insert([
                'id' => 'user-456',
                'name' => 'Maria Oliveira',
                'is_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('travel_orders')->insert([
                'user_id' => 'user-456',
                'destination' => 'Rio de Janeiro',
                'departure_date' => '2026-02-20',
                'return_date' => '2026-02-25',
                'status' => TravelOrderStatus::REQUESTED->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $token = $this->getTokenForUser('user-123', 'João Silva');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['destination' => 'São Paulo']);
    }

    public function test_admin_can_list_all_travel_orders()
    {
        TravelOrder::withoutEvents(function () {
            DB::table('users')->insert([
                ['id' => 'user-123', 'name' => 'User 123', 'is_admin' => false, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 'user-456', 'name' => 'User 456', 'is_admin' => false, 'created_at' => now(), 'updated_at' => now()],
            ]);
            
            DB::table('travel_orders')->insert([
                [
                    'user_id' => 'user-123',
                    'destination' => 'São Paulo',
                    'departure_date' => '2026-02-10',
                    'return_date' => '2026-02-15',
                    'status' => TravelOrderStatus::REQUESTED->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => 'user-456',
                    'destination' => 'Rio de Janeiro',
                    'departure_date' => '2026-02-20',
                    'return_date' => '2026-02-25',
                    'status' => TravelOrderStatus::REQUESTED->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        });

        $token = $this->getTokenForUser('admin-123', 'Admin User', true);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_travel_order()
    {
        $token = $this->getTokenForUser('user-123', 'João Silva');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/orders', [
                'destination' => 'Brasília',
                'departure_date' => '2026-02-28',
                'return_date' => '2026-03-05',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'destination' => 'Brasília',
                'user_id' => 'user-123',
                'status' => TravelOrderStatus::REQUESTED->value,
            ]);

        $this->assertDatabaseHas('travel_orders', [
            'destination' => 'Brasília',
            'user_id' => 'user-123',
        ]);
    }

    public function test_admin_can_approve_travel_order()
    {
        TravelOrder::withoutEvents(function () {
            DB::table('users')->insert([
                'id' => 'user-123',
                'name' => 'João Silva',
                'is_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::table('travel_orders')->insert([
                'id' => 1,
                'user_id' => 'user-123',
                'destination' => 'São Paulo',
                'departure_date' => '2026-02-10',
                'return_date' => '2026-02-15',
                'status' => TravelOrderStatus::REQUESTED->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $token = $this->getTokenForUser('admin-123', 'Admin User', true);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/orders/1/approve');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => TravelOrderStatus::APPROVED->value,
            ]);
    }

    public function test_regular_user_cannot_approve_travel_order()
    {
        TravelOrder::withoutEvents(function () {
            DB::table('users')->insert([
                'id' => 'user-123',
                'name' => 'João Silva',
                'is_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::table('travel_orders')->insert([
                'id' => 1,
                'user_id' => 'user-123',
                'destination' => 'São Paulo',
                'departure_date' => '2026-02-10',
                'return_date' => '2026-02-15',
                'status' => TravelOrderStatus::REQUESTED->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $token = $this->getTokenForUser('user-123', 'João Silva', false);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/orders/1/approve');

        $response->assertStatus(403);
    }

    public function test_cannot_cancel_own_travel_order()
    {
        TravelOrder::withoutEvents(function () {
            DB::table('users')->insert([
                'id' => 'user-123',
                'name' => 'João Silva',
                'is_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::table('travel_orders')->insert([
                'id' => 1,
                'user_id' => 'user-123',
                'destination' => 'São Paulo',
                'departure_date' => '2026-02-10',
                'return_date' => '2026-02-15',
                'status' => TravelOrderStatus::REQUESTED->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $token = $this->getTokenForUser('user-123', 'João Silva');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/orders/1/cancel');

        $response->assertStatus(403);
    }

    public function test_cannot_cancel_approved_travel_order()
    {
        TravelOrder::withoutEvents(function () {
            DB::table('users')->insert([
                'id' => 'user-123',
                'name' => 'João Silva',
                'is_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::table('travel_orders')->insert([
                'id' => 1,
                'user_id' => 'user-123',
                'destination' => 'São Paulo',
                'departure_date' => '2026-02-10',
                'return_date' => '2026-02-15',
                'status' => TravelOrderStatus::APPROVED->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $token = $this->getTokenForUser('user-123', 'João Silva', true);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/orders/1/cancel');

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cannot cancel approved order',
            ]);
    }

    public function test_cannot_access_without_token()
    {
        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(401);
    }

    public function test_can_filter_by_status()
    {
        TravelOrder::withoutEvents(function () {
            DB::table('users')->insert([
                'id' => 'user-123',
                'name' => 'João Silva',
                'is_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::table('travel_orders')->insert([
                [
                    'user_id' => 'user-123',
                    'destination' => 'São Paulo',
                    'departure_date' => '2026-02-10',
                    'return_date' => '2026-02-15',
                    'status' => TravelOrderStatus::REQUESTED->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => 'user-123',
                    'destination' => 'Rio',
                    'departure_date' => '2026-02-20',
                    'return_date' => '2026-02-25',
                    'status' => TravelOrderStatus::APPROVED->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        });

        $token = $this->getTokenForUser('user-123', 'João Silva');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/orders?status=' . TravelOrderStatus::APPROVED->value);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['destination' => 'Rio']);
    }
}
