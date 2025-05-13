<?php

use App\Models\Plant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\patchJson;

describe('Plant Update', function () {
    it('denies access to unauthenticated users', function () {
        $plant = Plant::factory()->create();

        patchJson("/api/plants/{$plant->id}", [])
            ->assertStatus(401);
    });

    it('denies access to non-admin users', function () {
        $plant = Plant::factory()->create();
        $user = User::factory()->groupLeader()->create();

        Sanctum::actingAs($user);

        patchJson("/api/plants/{$plant->id}", [
            'name' => 'Updated Name'
        ])->assertStatus(403);
    });

    it('validates required fields', function () {
        $plant = Plant::factory()->create();
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user);

        patchJson("/api/plants/{$plant->id}", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'address']);
    });

    it('updates a plant with valid data', function () {
        $plant = Plant::factory()->create();
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user);

        $updateData = [
            'name' => 'Updated Plant Name',
            'address' => '123 Updated St'
        ];

        patchJson("/api/plants/{$plant->id}", $updateData)
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Plant Name',
                'address' => '123 Updated St'
            ]);

        $this->assertDatabaseHas('plants', array_merge(['id' => $plant->id], $updateData));
    });

    it('returns 404 for non-existent plant', function () {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user);

        patchJson("/api/plants/9999", [
            'name' => 'Non-existent',
            'address' => 'Nowhere'
        ])->assertStatus(404);
    });
});
