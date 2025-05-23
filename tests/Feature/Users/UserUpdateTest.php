<?php

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\patchJson;

describe('Unautenticated', function () {
    it('denies access to unauthenticated users', function () {
        patchJson("/api/users/999", [
            'name' => 'Updated Name',
        ])->assertStatus(401);
    });
});

describe('Unauthorized', function () {
    it('returns 404 for non-existent user', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        patchJson('/api/users/9999', [
            'name' => 'Test',
        ])->assertStatus(404);
    });

    it('denies access to unauthorized users', function ($unauthorizedRole) {
        $targetUser = User::factory()->create();
        $user = User::factory()->create(['role' => $unauthorizedRole]);
        $this->actingAs($user);

        patchJson("/api/users/{$targetUser->id}", [])
            ->assertStatus(403);
    })->with([User::GROUP_LEADER_ROLE]);
});

describe('Authorized', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => User::ADMIN_ROLE]);
        $this->user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => User::GROUP_LEADER_ROLE
        ]);
        $this->actingAs($this->admin);

        $this->endpoint = "/api/users/{$this->user->id}";
    });

    it('validates required fields when present', function () {
        patchJson($this->endpoint, [
            'name' => '',
            'email' => '',
            'role' => '',
            'password' => ''
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name' => 'required',
                'email' => 'required',
                'role' => 'required',
                'password' => 'required'
            ]);
    });

    it('updates user with partial data', function (...$keys) {
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => User::ADMIN_ROLE,
            'password' => 'newpassword123'
        ];
        $data = Arr::only($data, $keys);

        $response = patchJson($this->endpoint, $data)
            ->assertOk();

        if (isset($data['password'])) {
            $this->user->refresh();
            expect(Hash::check($data['password'], $this->user->password))->toBeTrue();
            unset($data['password']);
        }

        $response->assertJson($data);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            ...Arr::except($data, 'password')
        ]);
    })->with([
        ['name'],
        ['email'],
        ['role'],
        ['password'],
        ['name', 'email', 'role', 'password']
    ]);
});
