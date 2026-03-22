<?php

namespace App\Models;

use Database\Factories\HabitStreakFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Tracks consecutive-day completion streaks for recurring tasks.
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property int $current_streak
 * @property int $longest_streak
 * @property Carbon|null $last_completed_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Task $task
 */
#[Fillable(['user_id', 'task_id', 'current_streak', 'longest_streak', 'last_completed_date'])]
class HabitStreak extends Model
{
    /** @use HasFactory<HabitStreakFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'last_completed_date' => 'date',
        ];
    }

    /**
     * Get the user that owns this streak.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task this streak belongs to.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Record a completion for today and update the streak accordingly.
     */
    public function recordCompletion(): void
    {
        $today = Carbon::today();

        if ($this->last_completed_date?->isSameDay($today)) {
            return;
        }

        $yesterday = $today->copy()->subDay();

        if ($this->last_completed_date?->isSameDay($yesterday)) {
            $this->current_streak++;
        } else {
            $this->current_streak = 1;
        }

        if ($this->current_streak > $this->longest_streak) {
            $this->longest_streak = $this->current_streak;
        }

        $this->last_completed_date = $today;
        $this->save();
    }

    /**
     * Reset the current streak to zero.
     */
    public function resetStreak(): void
    {
        $this->current_streak = 0;
        $this->save();
    }

    /**
     * Determine whether the streak is currently active (completed yesterday or today).
     */
    public function isActive(): bool
    {
        if ($this->last_completed_date === null) {
            return false;
        }

        return $this->last_completed_date->isToday()
            || $this->last_completed_date->isYesterday();
    }
}
