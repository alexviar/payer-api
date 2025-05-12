<?php

use App\Models\SalesAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\putJson;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

describe('Sales Agent Update', function () {
    it('denies access to unauthenticated users', function () {
        $agent = SalesAgent::factory()->create();

        putJson("/api/sales-agents/{$agent->id}", [])
            ->assertStatus(401);
    });

    it('denies access to non-admin users', function () {
        $user = User::factory()->groupLeader()->create();
        $agent = SalesAgent::factory()->create();

        actingAs($user)
            ->putJson("/api/sales-agents/{$agent->id}", [])
            ->assertStatus(403);
    });

    it('returns 404 for non-existent sales agent', function () {
        $user = User::factory()->admin()->create();
        actingAs($user);

        putJson('/api/sales-agents/9999', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '9876543210'
        ])->assertStatus(404);
    });

    it('validates required fields', function () {
        $user = User::factory()->admin()->create();
        $agent = SalesAgent::factory()->create();
        actingAs($user)
            ->putJson("/api/sales-agents/{$agent->id}", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone']);
    });

    it('validates email format', function () {
        $user = User::factory()->admin()->create();
        $agent = SalesAgent::factory()->create();
        $this->actingAs($user);

        putJson("/api/sales-agents/{$agent->id}", [
            'name' => 'Updated Name',
            'email' => 'invalid-email',
            'phone' => '9876543210'
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('validates email is unique', function () {
        $user = User::factory()->admin()->create();
        $agent1 = SalesAgent::factory()->create(['email' => 'agent1@example.com']);
        $agent2 = SalesAgent::factory()->create(['email' => 'agent2@example.com']);
        $this->actingAs($user);

        // Intentar actualizar el email del agente 1 al email del agente 2
        putJson("/api/sales-agents/{$agent1->id}", [
            'name' => 'Updated Name',
            'email' => 'agent2@example.com',
            'phone' => '9876543210'
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('allows updating own email to same value', function () {
        $user = User::factory()->admin()->create();
        $agent = SalesAgent::factory()->create(['email' => 'agent@example.com']);
        $this->actingAs($user);

        // Actualizar solo el nombre, manteniendo el mismo email
        putJson("/api/sales-agents/{$agent->id}", [
            'name' => 'Updated Name',
            'email' => 'agent@example.com',
            'phone' => '9876543210'
        ])
            ->assertStatus(200);
    });

    it('updates a sales agent with valid data', function () {
        $user = User::factory()->admin()->create();
        $agent = SalesAgent::factory()->create();
        $this->actingAs($user);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '9876543210'
        ];

        $response = putJson("/api/sales-agents/{$agent->id}", $updateData)
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'phone',
                'created_at',
                'updated_at'
            ]);

        // Verificar que la respuesta contenga los datos actualizados
        $response->assertJson($updateData);

        // Verificar que los cambios se hayan guardado en la base de datos
        $this->assertDatabaseHas('sales_agents', array_merge(['id' => $agent->id], $updateData));
    });
});
