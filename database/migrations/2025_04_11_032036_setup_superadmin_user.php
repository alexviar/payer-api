<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        User::create([
            'name' => config('auth.super_admin.name'),
            'email' => config('auth.super_admin.email'),
            'phone' => config('auth.super_admin.phone'),
            'password' => config('auth.super_admin.password'),
            'role' => User::SUPERADMIN_ROLE,
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('superadmin_user');
    }
};
