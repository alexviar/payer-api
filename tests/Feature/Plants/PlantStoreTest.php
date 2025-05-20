<?php

use App\Models\Plant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

describe('Plant Creation', function () {
    $endpoint = '/api/plants';

    it('denies access to unauthenticated users', function () use ($endpoint) {
        postJson($endpoint, [])
            ->assertStatus(401);
    });

    it('allows superadmins to create plants', function () use ($endpoint) {
        $user = User::factory()->superadmin()->create();
        Sanctum::actingAs($user);

        $plantData = Plant::factory()->raw();

        postJson($endpoint, $plantData)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => $plantData['name']]);

        $this->assertDatabaseHas('plants', $plantData);
    });

    it('allows admins to create plants', function () use ($endpoint) {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $plantData = Plant::factory()->raw();

        postJson($endpoint, $plantData)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => $plantData['name']]);
    });

    it('denies group leaders from creating plants', function () use ($endpoint) {
        $user = User::factory()->groupLeader()->create();
        Sanctum::actingAs($user);

        $plantData = Plant::factory()->raw();

        postJson($endpoint, $plantData)
            ->assertStatus(403);

        $this->assertDatabaseMissing('plants', $plantData);
    });

    it('requires all mandatory fields', function () use ($endpoint) {

        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        postJson($endpoint, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'address']);
    });

    it('can create a plant with valid data', function () use ($endpoint) {
        $plantData = Plant::factory()->raw();

        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        postJson($endpoint, $plantData)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => $plantData['name']]);

        $this->assertDatabaseHas('plants', $plantData);
    });
});
