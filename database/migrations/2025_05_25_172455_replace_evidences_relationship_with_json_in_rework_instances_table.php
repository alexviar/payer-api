<?php

use App\Models\DefectInstance;
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
        Schema::dropIfExists('rework_evidences');
        Schema::table('rework_instances', function (Blueprint $table) {
            $table->json('evidences')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rework_instances', function (Blueprint $table) {
            $table->dropColumn('evidences');
        });
        Schema::create('rework_evidences', function (Blueprint $table) {
            $table->id();
            $table->string('photo');
            $table->foreignIdFor(DefectInstance::class)->constrained();
            $table->timestamps();
        });
    }
};
