<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\postJson;

describe('Public Registration', function () {
    beforeEach(function () {
        $this->endpoint = '/api/auth/register';
        $this->validData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+52 555 123 4567',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];
    });

    it('creates a new user with group leader role', function () {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+52 555 123 4567',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = postJson('/api/auth/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'role',
                    'is_active'
                ],
                'access_token'
            ]);

        $user = User::where('email', 'test@example.com')->first();
        expect($user)->not()->toBe(null);
        expect($user->role)->toBe(User::GROUP_LEADER_ROLE);
        expect($user->is_active)->toBe(true);
    });

    it('validates required fields', function () {
        postJson($this->endpoint, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'phone',
                'password'
            ]);
    });

    it('validates email format', function () {
        $data = $this->validData;
        $data['email'] = 'invalid-email';

        postJson($this->endpoint, $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('validates email uniqueness', function () {
        User::factory()->create(['email' => 'test@example.com']);

        postJson($this->endpoint, $this->validData)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('validates password confirmation', function () {
        $data = $this->validData;
        $data['password_confirmation'] = 'different-password';

        postJson($this->endpoint, $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    it('validates password minimum length', function () {
        $data = $this->validData;
        $data['password'] = 'short';
        $data['password_confirmation'] = 'short';

        postJson($this->endpoint, $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    it('validates phone number length', function () {
        $data = $this->validData;
        $data['phone'] = str_repeat('1', 20); // Too long

        postJson($this->endpoint, $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    });

    it('creates user settings automatically', function () {
        $response = postJson($this->endpoint, $this->validData);

        $response->assertStatus(201);

        $user = User::where('email', 'test@example.com')->first();
        expect($user->settings)->not()->toBe(null);
    });
});
