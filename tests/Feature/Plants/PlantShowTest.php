<?php

use App\Models\Plant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

describe('Plant Show', function () {
    it('denies access to unauthenticated users', function () {
        getJson('/api/plants/1')
            ->assertStatus(401);
    });

    it('returns 404 for non-existent plant', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        getJson('/api/plants/9999')
            ->assertStatus(404);
    });

    it('returns plant details', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plant = Plant::factory()->create([
            'name' => 'Planta de Prueba',
            'address' => 'Calle Falsa 123',
            'status' => Plant::ACTIVE_STATUS
        ]);

        getJson("/api/plants/{$plant->id}")
            ->assertStatus(200)
            ->assertJson([
                'id' => $plant->id,
                'name' => 'Planta de Prueba',
                'address' => 'Calle Falsa 123',
                'status' => Plant::ACTIVE_STATUS
            ]);
    });

    it('includes related inspections when requested', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plant = Plant::factory()->create();
        \App\Models\Inspection::factory()->for($plant)->create();

        getJson("/api/plants/{$plant->id}?with=inspections")
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'address',
                'status',
                'inspections' => [
                    '*' => [
                        'id',
                        'description',
                        'status'
                    ]
                ]
            ]);
    });
});
