<?php

use App\Models\Client;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

describe('Client Creation', function () {
    beforeEach(function () {
        $this->endpoint = '/api/clients';
    });

    it('denies access to unauthenticated users', function () {
        postJson($this->endpoint, [])
            ->assertStatus(401);
    });

    it('allows superadmins to create clients', function () {
        $user = User::factory()->superadmin()->create();
        Sanctum::actingAs($user);

        $clientData = Client::factory()->raw();

        postJson($this->endpoint, $clientData)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => $clientData['name']]);

        $this->assertDatabaseHas('clients', $clientData);
    });

    it('allows admins to create clients', function () {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $clientData = Client::factory()->raw();

        postJson($this->endpoint, $clientData)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => $clientData['name']]);
    });

    it('denies group leaders from creating clients', function () {
        $user = User::factory()->groupLeader()->create();
        Sanctum::actingAs($user);

        $clientData = Client::factory()->raw();

        postJson($this->endpoint, $clientData)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => $clientData['name']]);
    });

    it('requires all mandatory fields', function () {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        postJson($this->endpoint, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'address',
                'representative',
                'phone',
                'email'
            ]);
    });

    it('can create a client with valid data', function () {
        $clientData = Client::factory()->raw();

        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        postJson($this->endpoint, $clientData)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => $clientData['name']]);

        $this->assertDatabaseHas('clients', $clientData);
    });
});
