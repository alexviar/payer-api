<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Sales Agent Creation', function () {
    it('denies access to unauthenticated users', function () {
        postJson('/api/sales-agents', [])
            ->assertStatus(401);
    });

    it('denies access to non-admin users', function () {
        $user = User::factory()->create(['role' => User::GROUP_LEADER_ROLE]);
        $this->actingAs($user);

        postJson('/api/sales-agents', [])
            ->assertStatus(403);
    });

    it('requires all mandatory fields', function () {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        postJson('/api/sales-agents', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone']);
    });

    it('validates email format', function () {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        postJson('/api/sales-agents', [
            'name' => 'Test Agent',
            'email' => 'invalid-email',
            'phone' => '1234567890'
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('validates email is unique', function () {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $existingAgent = \App\Models\SalesAgent::factory()->create([
            'email' => 'test@example.com'
        ]);

        postJson('/api/sales-agents', [
            'name' => 'Test Agent',
            'email' => 'test@example.com',
            'phone' => '1234567890'
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('creates a sales agent with valid data', function () {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $agentData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890'
        ];

        $response = postJson('/api/sales-agents', $agentData)
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'phone',
                'created_at',
                'updated_at'
            ]);

        // Verify the response contains the correct data
        $response->assertJson($agentData);

        // Verify the agent was saved in the database
        $this->assertDatabaseHas('sales_agents', $agentData);
    });
});
