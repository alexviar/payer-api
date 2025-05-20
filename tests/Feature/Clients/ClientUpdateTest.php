<?php

use App\Models\Client;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\patchJson;

describe('Client Update', function () {
    beforeEach(function () {
        $this->client = Client::factory()->create();
        $this->endpoint = "/api/clients/{$this->client->id}";
        $this->updateData = Client::factory()->raw();
    });

    it('denies access to unauthenticated users', function () {
        patchJson($this->endpoint, $this->updateData)
            ->assertStatus(401);
    });

    it('returns 404 for non-existent client', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        patchJson('/api/clients/9999', $this->updateData)
            ->assertStatus(404);
    });

    it('validates required fields', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        patchJson($this->endpoint, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'address',
                'representative',
                'phone',
                'email',
            ]);
    });

    it('validates email format', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        patchJson($this->endpoint, array_merge($this->updateData, ['email' => 'invalid-email']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('updates client with valid data', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = patchJson($this->endpoint, $this->updateData)
            ->assertStatus(200);

        // Verify response
        $response->assertJson($this->updateData);

        // Verify database
        $this->assertDatabaseHas('clients', array_merge(
            ['id' => $this->client->id],
            $this->updateData
        ));
    });

    it('allows updating with same email for same client', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = array_merge($this->updateData, [
            'email' => $this->client->email,
        ]);

        patchJson($this->endpoint, $data)
            ->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => $this->client->id,
            'email' => $this->client->email,
        ]);
    });
});
