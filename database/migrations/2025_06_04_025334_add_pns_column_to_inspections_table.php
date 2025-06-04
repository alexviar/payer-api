<?php

use App\Models\Inspection;
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
        Schema::table('inspections', function (Blueprint $table) {
            $table->json('pns')->nullable();
        });

        foreach (Inspection::get() as $inspection) {
            if (!$inspection->lastInspectedLot) continue;
            $inspection->update([
                'pns' => $inspection->lots()
                    ->select('pn')
                    ->distinct()
                    ->pluck('pn')
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn('pns');
        });
    }
};
