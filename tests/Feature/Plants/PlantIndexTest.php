<?php

use App\Models\Plant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

describe('Plant Index', function () {
    it('denies access to unauthenticated users', function () {
        getJson('/api/plants')
            ->assertStatus(401);
    });

    it('returns empty when no plants exist', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        getJson('/api/plants')
            ->assertStatus(200)
            ->assertJson([
                'data' => [],
                'current_page' => 1,
                'per_page' => 15,
                'total' => 0
            ]);
    });

    it('returns paginated list of plants', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plants = Plant::factory()->count(3)->create();

        $response = getJson('/api/plants')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'address', 'created_at', 'updated_at']
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

    it('filters plants by search term', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plant1 = Plant::factory()->create(['name' => 'Planta Norte']);
        $plant2 = Plant::factory()->create(['name' => 'Planta Sur']);
        $plant3 = Plant::factory()->create(['name' => 'Otra Planta']);

        getJson('/api/plants?search=Norte')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $plant1->id]);
    });

    it('respects page size parameter', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Plant::factory()->count(5)->create();

        getJson('/api/plants?page_size=2')
            ->assertStatus(200)
            ->assertJson([
                'per_page' => 2,
                'current_page' => 1,
                'total' => 5
            ])
            ->assertJsonCount(2, 'data');
    });
});
