<?php

use App\Models\Client;
use App\Models\Inspection;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

describe('Client Listing', function () {
    beforeEach(function () {
        $this->endpoint = '/api/clients';
    });

    it('denies access to unauthenticated users', function () {
        getJson($this->endpoint)
            ->assertStatus(401);
    });

    it('returns empty array when no clients exist', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        getJson($this->endpoint)
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('returns paginated list of clients', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $clients = Client::factory()->count(3)->create();
        foreach ($clients as $client) {
            $product = Product::factory()->for($client)->create();
            Inspection::factory()->for($product)->create();
        }

        getJson($this->endpoint)
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'address',
                        'representative',
                        'phone',
                        'email',
                        'products_count',
                        'last_inspection' => [
                            'submit_date',
                            'complete_date'
                        ],
                        'created_at',
                        'updated_at',
                    ]
                ],
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                'to',
                'total',
            ]);
    });

    it('returns paginated results with custom page size', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Client::factory()->count(15)->create();

        getJson("$this->endpoint?page_size=5")
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJson([
                'per_page' => 5,
                'total' => 15,
                'current_page' => 1
            ]);
    });

    it('filters clients by search term', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $client1 = Client::factory()->create(['name' => 'Acme Corporation']);
        $client2 = Client::factory()->create(['name' => 'Globex Corporation']);
        Client::factory()->create(['name' => 'Other Company']);

        getJson("$this->endpoint?search=Corporation")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Acme Corporation'])
            ->assertJsonFragment(['name' => 'Globex Corporation']);
    });
});
