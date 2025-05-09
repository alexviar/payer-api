<?php

use App\Models\Defect;
use App\Models\Inspection;
use App\Models\Plant;
use App\Models\Product;
use App\Models\Rework;
use App\Models\SalesAgent;
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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->date('submit_date');
            $table->string('description');
            $table->integer('inventory')->unsigned();
            $table->date('start_date')->nullable();
            $table->date('complete_date')->nullable();
            $table->tinyInteger('status');
            $table->foreignIdFor(Plant::class)->constrained();
            $table->foreignIdFor(Product::class)->constrained();
            $table->foreignIdFor(User::class, 'group_leader_id')->constrained();
            $table->datetimes();
        });

        Schema::create('inspection_sales_agent', function (Blueprint $table) {
            $table->foreignIdFor(Inspection::class)->constrained();
            $table->foreignIdFor(SalesAgent::class)->constrained();
            $table->timestamps();

            $table->primary(['inspection_id', 'sales_agent_id']);
        });

        Schema::create('inspection_defects', function (Blueprint $table) {
            $table->foreignIdFor(Inspection::class)->constrained();
            $table->foreignIdFor(Defect::class)->constrained();
            $table->timestamps();

            $table->primary(['inspection_id', 'defect_id']);
        });

        Schema::create('inspection_reworks', function (Blueprint $table) {
            $table->foreignIdFor(Inspection::class)->constrained();
            $table->foreignIdFor(Rework::class)->constrained();
            $table->timestamps();

            $table->primary(['inspection_id', 'rework_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_defects');
        Schema::dropIfExists('inspection_reworks');
        Schema::dropIfExists('inspections');
    }
};
