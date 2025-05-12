<?php

use App\Models\Plant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;

describe('Plant Deletion', function () {
    it('denies access to unauthenticated users', function () {
        $plant = Plant::factory()->create();
        
        deleteJson("/api/plants/{$plant->id}")
            ->assertStatus(401);
    });

    it('denies access to non-admin users', function () {
        $plant = Plant::factory()->create();
        $user = User::factory()->groupLeader()->create();
        
        Sanctum::actingAs($user);
        
        deleteJson("/api/plants/{$plant->id}")
            ->assertStatus(403);
    });

    it('deletes a plant', function () {
        $plant = Plant::factory()->create();
        $user = User::factory()->admin()->create();
        
        Sanctum::actingAs($user);
        
        deleteJson("/api/plants/{$plant->id}")
            ->assertStatus(204);
            
        $this->assertDatabaseMissing('plants', ['id' => $plant->id]);
    });

    it('returns 404 for non-existent plant', function () {
        $user = User::factory()->admin()->create();
        
        Sanctum::actingAs($user);
        
        deleteJson("/api/plants/9999")
            ->assertStatus(404);
    });
});
