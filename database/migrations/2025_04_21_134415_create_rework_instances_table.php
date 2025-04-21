<?php

use App\Models\InspectionLot;
use App\Models\Rework;
use App\Models\ReworkInstance;
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
        Schema::create('rework_instances', function (Blueprint $table) {
            $table->id();
            $table->string('tag');
            $table->foreignIdFor(Rework::class)->constrained();
            $table->foreignIdFor(InspectionLot::class)->constrained();
            $table->timestamps();
        });

        Schema::create('rework_evidences', function (Blueprint $table) {
            $table->id();
            $table->string('photo');
            $table->foreignIdFor(ReworkInstance::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rework_instances');
    }
};
