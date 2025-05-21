<?php

use App\Models\Client;
use App\Models\CustomAttribute;
use App\Models\Inspection;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

describe('Product Listing', function () {
    beforeEach(function () {
        $this->endpoint = '/api/products';
    });

    it('denies access to unauthenticated users', function () {
        getJson($this->endpoint)
            ->assertStatus(401);
    });

    it('returns empty array when no products exist', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        getJson($this->endpoint)
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('returns paginated list of products with client and last inspection', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $client = Client::factory()->create();
        Product::factory()
            ->count(3)
            ->for($client)
            ->has(Inspection::factory()->count(1))
            ->has(CustomAttribute::factory()->count(1), 'attributes')
            ->create();

        $response = getJson($this->endpoint);

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'current_page',
                'last_page',
                'total',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'manufacturer',
                        'client_id',
                        'client' => [
                            'id',
                            'name',
                        ],
                        'last_inspection' => [
                            'id',
                            'complete_date',
                            'total_approved'
                        ],
                        'inspections_count',
                        'attributes' => [
                            '*' => [
                                'id',
                                'name'
                            ]
                        ]
                    ]
                ]
            ]);
    });

    it('filters products by search term', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $client = Client::factory()->create();
        $product1 = Product::factory()
            ->for($client)
            ->create(['name' => 'Test Product']);

        $product2 = Product::factory()
            ->for($client)
            ->create(['name' => 'Another Product']);

        getJson("$this->endpoint?search=Test")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Test Product');
    });

    it('filters products by client_id', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();

        $product1 = Product::factory()
            ->for($client1)
            ->create(['name' => 'Product 1']);

        $product2 = Product::factory()
            ->for($client2)
            ->create(['name' => 'Product 2']);

        getJson("$this->endpoint?filter[client_id]={$client1->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Product 1');
    });
});
