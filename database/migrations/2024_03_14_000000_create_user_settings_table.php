<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('language')->default('es');
            $table->boolean('notifications_enabled')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });

        foreach (User::all() as $user) {
            $user->settings()->create([
                'language' => 'es',
                'notifications_enabled' => true
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_settings');
    }
};
