<?php

use App\Models\Client;
use App\Models\Inspection;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

describe('Client Show', function () {
    it('denies access to unauthenticated users', function () {
        $client = Client::factory()->create();

        getJson("/api/clients/{$client->id}")
            ->assertStatus(401);
    });

    it('returns 404 for non-existent client', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        getJson('/api/clients/9999')
            ->assertStatus(404);
    });

    it('returns client details', function () {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $product = Product::factory()->for($client)->create();
        Inspection::factory()->for($product)->create();
        Sanctum::actingAs($user);

        getJson("/api/clients/{$client->id}")
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'address',
                'representative',
                'phone',
                'products_count',
                'last_inspection' => [
                    'submit_date',
                    'complete_date'
                ],
            ]);
    });
});
