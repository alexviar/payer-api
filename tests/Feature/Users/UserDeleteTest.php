<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;

describe('User Deletion', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => User::ADMIN_ROLE]);
        $this->user = User::factory()->groupLeader()->create();
        $this->endpoint = "/api/users/{$this->user->id}";
    });

    it('denies access to unauthenticated users', function () {
        deleteJson($this->endpoint)
            ->assertStatus(401);
    });

    it('denies delete when user is group leader', function () {
        $groupLeader = User::factory()->create(['role' => User::GROUP_LEADER_ROLE]);
        Sanctum::actingAs($groupLeader);

        deleteJson($this->endpoint)
            ->assertStatus(403);
    });

    it('prevents deletion of last superadmin', function () {
        $superadmin = User::where('role', User::SUPERADMIN_ROLE)->first();
        Sanctum::actingAs($superadmin);

        expect(User::where('role', User::SUPERADMIN_ROLE)->count())->toBe(1);

        // Intentar eliminar el único superadmin
        deleteJson("/api/users/{$superadmin->id}")
            ->assertStatus(409)
            ->assertJson([
                'message' => 'No se puede eliminar el último superadministrador del sistema.'
            ]);
        $this->assertDatabaseHas('users', ['id' => $superadmin->id]); // Verificar que el superadmin siga existir en la base de datos
    });

    it('deletes user when user is authorized', function ($role) {
        $admin = User::factory()->create(['role' => $role]);
        Sanctum::actingAs($admin);

        $response = deleteJson($this->endpoint);
        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    })->with([User::SUPERADMIN_ROLE, User::ADMIN_ROLE]);

    it('returns 404 when trying to delete non-existent user', function () {
        Sanctum::actingAs($this->admin);

        deleteJson('/api/users/9999')
            ->assertStatus(404);
    });
});
