<?php

use App\Models\SalesAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

describe('Sales Agent Show', function () {
    it('denies access to unauthenticated users', function () {
        $agent = SalesAgent::factory()->create();
        
        getJson("/api/sales-agents/{$agent->id}")
            ->assertStatus(401);
    });

    it('returns 404 for non-existent sales agent', function () {
        $user = User::factory()->create();
        
        actingAs($user)
            ->getJson('/api/sales-agents/9999')
            ->assertStatus(404);
    });

    it('shows a sales agent to any authenticated user', function () {
        $user = User::factory()->create();
        $agent = SalesAgent::factory()->create();
        
        actingAs($user)
            ->getJson("/api/sales-agents/{$agent->id}")
            ->assertStatus(200)
            ->assertJson([
                'id' => $agent->id,
                'name' => $agent->name,
                'email' => $agent->email,
                'phone' => $agent->phone,
            ]);
    });
});
