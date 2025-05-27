<?php

use App\Models\Inspection;
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
        Schema::dropIfExists('inspection_reviews');
        Schema::create('inspection_reviews', function (Blueprint $table) {
            $table->id();
            $table->date('review_date');
            $table->tinyInteger('review_outcome')->unsigned();
            $table->json('corrective_actions');
            $table->foreignIdFor(User::class, 'reviewer_id')->constrained();
            $table->foreignIdFor(Inspection::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_reviews');
    }
};
