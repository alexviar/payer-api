<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

describe('User Show', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->targetUser = User::factory()->create();
        $this->endpoint = "/api/users/{$this->targetUser->id}";
    });

    it('denies access to unauthenticated users', function () {
        getJson($this->endpoint)
            ->assertStatus(401);
    });

    it('returns 404 for non-existent user', function () {
        Sanctum::actingAs($this->user);

        getJson('/api/users/9999')
            ->assertStatus(404);
    });

    it('returns user details', function () {
        Sanctum::actingAs($this->user);

        getJson($this->endpoint)
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'role',
                'created_at',
                'updated_at'
            ]);
    });
});