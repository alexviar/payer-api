<?php

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
        DB::table('inspections')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $original = json_decode($row->collaborators, true);
                    $converted = array_map(fn($name) => ['name' => $name, 'hours' => 0], $original);

                    DB::table('inspections')->where('id', $row->id)->update([
                        'collaborators' => json_encode($converted),
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('inspections')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $original = json_decode($row->collaborators, true);
                    $converted = array_map(fn($collaborator) => $collaborator['name'], $original);

                    DB::table('inspections')->where('id', $row->id)->update([
                        'collaborators' => json_encode($converted),
                    ]);
                }
            });
    }
};
