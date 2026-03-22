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
        Schema::create('productivity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->datetime('completed_at');
            $table->tinyInteger('day_of_week');
            $table->tinyInteger('hour_of_day');
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'day_of_week']);
            $table->index(['user_id', 'hour_of_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productivity_logs');
    }
};
