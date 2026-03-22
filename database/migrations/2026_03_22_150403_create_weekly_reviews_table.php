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
        Schema::create('weekly_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');
            $table->date('week_end');
            $table->unsignedInteger('tasks_completed')->default(0);
            $table->unsignedInteger('tasks_missed')->default(0);
            $table->unsignedInteger('tasks_created')->default(0);
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'week_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_reviews');
    }
};
