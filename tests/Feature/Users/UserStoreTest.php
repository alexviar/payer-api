<?php

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

describe('User Creation', function () {
    beforeEach(function () {
        $this->endpoint = '/api/users';
        $this->admin = User::factory()->create(['role' => User::ADMIN_ROLE]);
        $this->data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => User::GROUP_LEADER_ROLE
        ];
    });

    it('denies access to unauthenticated users', function () {
        postJson($this->endpoint, $this->data)
            ->assertStatus(401);
    });

    it('denies access to unauthorized users', function ($unauthorizedRole) {
        $user = User::factory()->create(['role' => $unauthorizedRole]);
        Sanctum::actingAs($user);

        postJson($this->endpoint, $this->data)
            ->assertStatus(403);
    })->with([User::GROUP_LEADER_ROLE]);

    it('creates a new user with valid data', function () {
        Sanctum::actingAs($this->admin);

        $response = postJson($this->endpoint, $this->data);
        $response->assertStatus(201)
            ->assertJson(Arr::except($this->data, 'password'));

        $user = User::find($response->json('id'));
        expect($user)->not()->toBe(null);
        expect($user->name)->toBe($this->data['name']);
        expect($user->email)->toBe($this->data['email']);
        expect($user->role)->toBe($this->data['role']);
        expect(Hash::check($this->data['password'], $user->password))->toBeTrue();
    });

    it('validates required fields', function () {
        Sanctum::actingAs($this->admin);

        postJson($this->endpoint, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
                'role'
            ]);
    });

    it('validates email uniqueness', function () {
        Sanctum::actingAs($this->admin);

        User::factory()->create(['email' => 'existing@example.com']);

        $data = $this->data;
        $data['email'] = 'existing@example.com';

        postJson($this->endpoint, $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });
});
