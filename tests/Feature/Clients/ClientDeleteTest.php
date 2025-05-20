<?php

use App\Models\Client;
use App\Models\User;
use App\Models\Product;
use App\Models\Inspection;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;

describe('Client Delete', function () {
    beforeEach(function () {
        $this->client = Client::factory()->create();
        $this->endpoint = "/api/clients/{$this->client->id}";
    });

    it('denies access to unauthenticated users', function () {
        deleteJson($this->endpoint)
            ->assertStatus(401);
    });

    it('returns 404 for non-existent client', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        deleteJson('/api/clients/9999')
            ->assertStatus(404);
    });

    it('denies delete when user is group leader', function () {
        $user = User::factory()->groupLeader()->create();
        Sanctum::actingAs($user);

        deleteJson($this->endpoint)
            ->assertStatus(403);
    });

    it('deletes client when user is authorized', function ($role) {
        $product1 = Product::factory()->for($this->client)->create();
        $product2 = Product::factory()->for($this->client)->create();
        $admin = User::factory()->create(['role' => $role]);
        Sanctum::actingAs($admin);

        deleteJson($this->endpoint)
            ->assertStatus(204);


        $this->assertDatabaseMissing('clients', ['id' => $this->client->id]);
        $this->assertDatabaseMissing('products', ['id' => $product1->id]);
        $this->assertDatabaseMissing('products', ['id' => $product2->id]);
    })->with([User::SUPERADMIN_ROLE, User::ADMIN_ROLE]);

    it('prevent form deleting when there are inspections for this client', function () {
        $admin = User::factory()->superadmin()->create();
        Sanctum::actingAs($admin);

        $product = Product::factory()->for($this->client)->create();
        Inspection::factory()->for($product)->create();

        deleteJson($this->endpoint)
            ->assertStatus(409)
            ->assertJson([
                'message' => 'No se puede eliminar el cliente porque tiene inspecciones registradas.',
            ]);

        $this->assertDatabaseHas('clients', ['id' => $this->client->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    });
});
