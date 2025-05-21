<?php

use App\Models\Client;
use App\Models\CustomAttribute;
use App\Models\Inspection;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

describe('Product Show', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->client = Client::factory()->create();
        $this->product = Product::factory()
            ->for($this->client)
            ->has(Inspection::factory()->count(1))
            ->has(CustomAttribute::factory()->count(1), 'attributes')
            ->create();

        $this->endpoint = "/api/products/{$this->product->id}";
    });

    it('denies access to unauthenticated users', function () {
        getJson($this->endpoint)
            ->assertStatus(401);
    });

    it('returns 404 for non-existent product', function () {
        Sanctum::actingAs($this->user);

        getJson('/api/products/9999')
            ->assertStatus(404);
    });

    it('returns product details with client and last inspection', function () {
        Sanctum::actingAs($this->user);

        getJson($this->endpoint)
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'manufacturer',
                'client' => [
                    'id',
                    'name',
                ],
                'inspections_count',
                'last_inspection' => [
                    'submit_date',
                    'complete_date'
                ],
                'attributes' => [
                    '*' => [
                        'id',
                        'name'
                    ]
                ]
            ]);
    });
});
