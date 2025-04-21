<?php

use App\Models\CustomAttribute;
use App\Models\Inspection;
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
        Schema::create('inspection_lots', function (Blueprint $table) {
            $table->id();
            $table->string('qn');
            $table->string('pn');
            $table->date('inspect_date');
            $table->integer('total_units')->unsigned();
            $table->integer('total_rejects')->unsigned();
            $table->integer('total_reworks')->unsigned();
            $table->foreignIdFor(Inspection::class)->constrained();
            $table->timestamps();
        });

        Schema::create('inspection_lot_attributes', function (Blueprint $table) {
            $table->foreignIdFor(InspectionLot::class)->constrained();
            $table->foreignIdFor(CustomAttribute::class)->constrained();
            $table->string('value');
            $table->timestamps();

            $table->primary(['inspection_lot_id', 'custom_attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_lot_attributes');
        Schema::dropIfExists('inspection_lots');
    }
};
