<?php

namespace App\Models;

use Database\Factories\ProductivityLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Records when a task was completed for productivity trend analysis.
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property Carbon $completed_at
 * @property int $day_of_week
 * @property int $hour_of_day
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Task $task
 */
#[Fillable(['user_id', 'task_id', 'completed_at', 'day_of_week', 'hour_of_day'])]
class ProductivityLog extends Model
{
    /** @use HasFactory<ProductivityLogFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'day_of_week' => 'integer',
            'hour_of_day' => 'integer',
        ];
    }

    /**
     * Get the user that owns this log entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task associated with this log entry.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get a human-readable label for the day of week.
     */
    public function dayLabel(): string
    {
        return match ($this->day_of_week) {
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            default => 'Unknown',
        };
    }
}
