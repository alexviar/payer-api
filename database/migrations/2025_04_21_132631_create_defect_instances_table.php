<?php

use App\Models\Defect;
use App\Models\DefectInstance;
use App\Models\InspectionLot;
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
        Schema::create('defect_instances', function (Blueprint $table) {
            $table->id();
            $table->string('tag');
            $table->foreignIdFor(Defect::class)->constrained();
            $table->foreignIdFor(InspectionLot::class)->constrained();
            $table->timestamps();
        });

        Schema::create('defect_evidences', function (Blueprint $table) {
            $table->id();
            $table->string('photo');
            $table->foreignIdFor(DefectInstance::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defect_instances');
    }
};
