<?php

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
        Schema::table('rework_instances', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('tag');
            $table->dropColumn('tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rework_instances', function (Blueprint $table) {
            $table->string('tag')->nullable()->after('tags');
            $table->dropColumn('tags');
        });
    }
};
