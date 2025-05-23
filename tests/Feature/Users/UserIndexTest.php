<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

describe('User Listing', function () {
    beforeEach(function () {
        $this->endpoint = '/api/users';
    });

    it('denies access to unauthenticated users', function () {
        getJson($this->endpoint)
            ->assertStatus(401);
    });

    it('returns paginated list of users', function () {
        $admin = User::factory()->create();
        Sanctum::actingAs($admin);

        User::factory()->count(3)->create();

        $response = getJson($this->endpoint);
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'current_page',
                'last_page',
                'total'
            ]);
    });

    it('filters users by search term', function () {
        $admin = User::factory()->create();
        Sanctum::actingAs($admin);

        User::factory()->create(['name' => 'Test User']);
        User::factory()->create(['name' => 'Another User']);
        User::factory()->create(['email' => 'test@example.com']);

        getJson("$this->endpoint?search=Test")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('filters users by role', function () {
        $admin = User::factory()->create();
        Sanctum::actingAs($admin);

        User::factory()->create(['role' => User::ADMIN_ROLE]);
        User::factory()->create(['role' => User::GROUP_LEADER_ROLE]);
        User::factory()->create(['role' => User::SUPERADMIN_ROLE]);

        getJson("$this->endpoint?filter[role]=" . User::ADMIN_ROLE)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.role', User::ADMIN_ROLE);
    });
});
