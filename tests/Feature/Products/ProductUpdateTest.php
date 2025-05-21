<?php

use App\Models\CustomAttribute;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Arr;
use Tests\TestCase;

use function Pest\Laravel\patchJson;

describe('Unautenticated', function () {
    it('denies access to unauthenticated users', function () {
        patchJson("/api/products/999", [
            'name' => 'Updated Name',
        ])->assertStatus(401);
    });
});

describe('Unauthorized', function () {
    it('returns 404 for non-existent product', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        patchJson('/api/products/9999', [
            'name' => 'Test',
        ])->assertStatus(404);
    });

    it('denies access to unauthorized users', function ($unauthorizedRole) {
        $product = Product::factory()->create();
        $user = User::factory()->create(['role' => $unauthorizedRole]);
        $this->actingAs($user);

        patchJson("/api/products/{$product->id}", [])
            ->assertStatus(403);
    })->with([User::GROUP_LEADER_ROLE]);
});

describe('Authorized', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->product = Product::factory()
            ->create([
                'name' => 'Original Name',
                'manufacturer' => 'Original Manufacturer',
            ]);
        $this->actingAs($this->user);

        $this->endpoint = "/api/products/{$this->product->id}";
    });

    it('validates required fields when present', function () {
        patchJson($this->endpoint, [
            'name' => '',
            'manufacturer' => '',
            'client_id' => null,
            'attributes' => null,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name' => 'required',
                'manufacturer' => 'required',
                'client_id' => 'required',
                'attributes' => 'required'
            ]);
    });

    it('validates client exists', function () {
        patchJson($this->endpoint, [
            'client_id' => 9999,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['client_id']);
    });

    it('validates attributes field', function () {
        patchJson($this->endpoint, [
            'attributes' => 'invalid'
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['attributes']);

        patchJson($this->endpoint, [
            'attributes' => [999]
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['attributes.0']);
    });

    it('updates product with partial data', function (...$keys) {
        $data = Product::factory()->raw();
        $data['attributes'] = CustomAttribute::factory()->count(2)->create()->pluck('id');
        $data = Arr::only($data, $keys);

        $response = patchJson($this->endpoint, $data)
            ->assertOk()
            ->assertJson(Arr::except($data, 'attributes'))
            ->assertJson([
                'inspections_count' => 0,
            ]);
        if (isset($data['attributes'])) {
            $response->assertJson([
                'attributes' => collect($data['attributes'])->map(fn($id) => ['id' => $id])->toArray(),
            ]);
            $this->product->refresh();
            expect($this->product->attributes)->toHaveCount(2)
                ->and($this->product->attributes->pluck('id'))->toEqualCanonicalizing($data['attributes']);
        }

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            ...Arr::except($data, 'attributes')
        ]);
    })->with([
        ['name'],
        ['manufacturer'],
        ['attributes'],
        ['client_id'],
        ['name', 'manufacturer', 'attributes', 'client_id']
    ]);
});
