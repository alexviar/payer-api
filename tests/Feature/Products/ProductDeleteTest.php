<?php

use App\Models\Client;
use App\Models\Inspection;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;

describe('Product Deletion', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->client = Client::factory()->create();
        $this->product = Product::factory()
            ->for($this->client)
            ->create();

        $this->endpoint = "/api/products/{$this->product->id}";
    });

    it('denies access to unauthenticated users', function () {
        deleteJson($this->endpoint)
            ->assertStatus(401);
    });

    it('prevents deletion with existing inspections', function () {
        Sanctum::actingAs($this->user);

        Inspection::factory()->for($this->product)->create();

        deleteJson($this->endpoint)
            ->assertStatus(409)
            ->assertJson([
                'message' => 'No se puede eliminar el producto porque tiene inspecciones registradas.'
            ]);
    });

    it('deletes a product', function () {
        Sanctum::actingAs($this->user);

        deleteJson($this->endpoint)
            ->assertStatus(204);

        $this->assertDatabaseMissing('products', [
            'id' => $this->product->id,
        ]);
    });

    it('returns 404 when trying to delete non-existent product', function () {
        Sanctum::actingAs($this->user);

        deleteJson('/api/products/9999')
            ->assertStatus(404);
    });
});
