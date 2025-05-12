<?php

use App\Models\SalesAgent;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

describe('Sales Agent Index', function () {
    it('denies access to unauthenticated users', function () {
        getJson('/api/sales-agents')
            ->assertStatus(401);
    });

    it('returns empty when no sales agents exist', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        getJson('/api/sales-agents')
            ->assertStatus(200)
            ->assertJson([
                'data' => [],
                'current_page' => 1,
                'per_page' => 15,
                'total' => 0
            ]);
    });


    it('returns paginated list of sales agents', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $agents = SalesAgent::factory()->count(3)->create();

        $response = getJson('/api/sales-agents')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'phone', 'created_at', 'updated_at']
                ],
                'current_page',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links' => [
                    '*' => ['url', 'label', 'active']
                ],
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ]);
            
        $response->assertJsonCount(3, 'data');
    });

    it('filters sales agents by search term', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $agent1 = SalesAgent::factory()->create(['name' => 'John Doe']);
        $agent2 = SalesAgent::factory()->create(['name' => 'Jane Smith']);
        $agent3 = SalesAgent::factory()->create(['name' => 'Robert Johnson']);

        getJson('/api/sales-agents?search=John')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $agent1->id])
            ->assertJsonFragment(['id' => $agent3->id]);
    });

    it('respects page size parameter', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        SalesAgent::factory()->count(5)->create();

        getJson('/api/sales-agents?page_size=2')
            ->assertStatus(200)
            ->assertJson([
                'per_page' => 2,
                'current_page' => 1,
                'total' => 5
            ])
            ->assertJsonCount(2, 'data');
    });
});
