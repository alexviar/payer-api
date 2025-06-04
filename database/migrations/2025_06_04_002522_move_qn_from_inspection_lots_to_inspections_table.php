<?php

use App\Models\Inspection;
use App\Models\InspectionLot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->string('qn');
        });

        $inspections = Inspection::with('lastInspectedLot')->get();
        foreach ($inspections as $inspection) {
            if (!$inspection->lastInspectedLot) {
                continue;
            }
            $inspection->update([
                'qn' => $inspection->lastInspectedLot->qn
            ]);
        }

        Schema::table('inspection_lots', function (Blueprint $table) {
            $table->dropColumn('qn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspection_lots', function (Blueprint $table) {
            $table->string('qn');
        });

        InspectionLot::query()->join('inspections', 'inspections.id', '=', 'inspection_lots.inspection_id')
            ->query()->update([
                'qn' => DB::raw('inspections.qn'),
            ]);

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn('qn');
        });
    }
};
