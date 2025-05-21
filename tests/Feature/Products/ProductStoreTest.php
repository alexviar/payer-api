<?php

use App\Models\Client;
use App\Models\CustomAttribute;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

use function Pest\Laravel\postJson;

describe('Product Creation', function () {
    beforeEach(function () {
        $this->endpoint = '/api/products';
        $this->user = User::factory()->create();
        $this->client = Client::factory()->create();
        $this->data = Product::factory()
            ->for($this->client)
            ->raw();
        $this->data['attributes'] = CustomAttribute::factory()->count(2)->create()->pluck('id');
    });

    it('denies access to unauthenticated users', function () {

        $data = $this->data;

        postJson($this->endpoint, $data)
            ->assertStatus(401);
    });

    it('denies access to unauthorized users', function ($unauthorizedRole) {
        $user = $this->user;
        $user->update(['role' => $unauthorizedRole]);
        $data = $this->data;
        $this->actingAs($user);

        postJson($this->endpoint, $data)
            ->assertStatus(403);
    })->with([User::GROUP_LEADER_ROLE]);

    it('creates a new product with valid data', function () {
        $data = $this->data;
        $this->actingAs($this->user);

        $response = postJson($this->endpoint, $data);
        $response->assertStatus(201)
            ->assertJson(Arr::except($data, 'attributes'))
            ->assertJson([
                'inspections_count' => 0,
            ])
            ->assertJson([
                'attributes' => collect($data['attributes'])->map(fn($id) => ['id' => $id])->toArray(),
            ]);

        $product = Product::find($response->json('id'));
        expect($product)->not()->toBe(null);
        expect($product->attributes)->toHaveCount(2);
        expect($product->getAttributes())->toMatchArray(Arr::except($data, 'attributes'));
    });

    it('validates required fields', function () {
        $this->actingAs($this->user);

        postJson($this->endpoint, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'manufacturer',
                'client_id',
                'attributes'
            ]);
    });

    it('validates client exists', function () {
        $data = $this->data;
        $data['client_id'] = 9999;
        $this->actingAs($this->user);

        postJson($this->endpoint, $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['client_id']);
    });
});
