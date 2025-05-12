<?php

use App\Models\SalesAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

describe('Sales Agent Deletion', function () {
    it('denies access to unauthenticated users', function () {
        $agent = SalesAgent::factory()->create();

        deleteJson("/api/sales-agents/{$agent->id}")
            ->assertStatus(401);
    });

    it('denies access to non-admin users', function () {
        $user = User::factory()->groupLeader()->create();
        $agent = SalesAgent::factory()->create();

        actingAs($user)
            ->deleteJson("/api/sales-agents/{$agent->id}")
            ->assertStatus(403);
    });

    it('returns 404 for non-existent sales agent', function () {
        $user = User::factory()->admin()->create();

        actingAs($user)
            ->deleteJson('/api/sales-agents/9999')
            ->assertStatus(404);
    });

    it('deletes a sales agent', function () {
        $user = User::factory()->admin()->create();
        $agent = SalesAgent::factory()->create();

        actingAs($user)
            ->deleteJson("/api/sales-agents/{$agent->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('sales_agents', ['id' => $agent->id]);
    });

    it('prevents deletion of sales agent with related inspections', function () {
        $user = User::factory()->admin()->create();
        $agent = SalesAgent::factory()->hasInspections(1)->create();

        actingAs($user)
            ->deleteJson("/api/sales-agents/{$agent->id}")
            ->assertConflict();

        $this->assertDatabaseHas('sales_agents', ['id' => $agent->id]);
    });
});
