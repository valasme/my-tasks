<?php

namespace App\Models;

use Database\Factories\WeeklyReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An automated end-of-week summary of task activity.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $week_start
 * @property Carbon $week_end
 * @property int $tasks_completed
 * @property int $tasks_missed
 * @property int $tasks_created
 * @property string|null $summary
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
#[Fillable(['user_id', 'week_start', 'week_end', 'tasks_completed', 'tasks_missed', 'tasks_created', 'summary'])]
class WeeklyReview extends Model
{
    /** @use HasFactory<WeeklyReviewFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'tasks_completed' => 'integer',
            'tasks_missed' => 'integer',
            'tasks_created' => 'integer',
        ];
    }

    /**
     * Get the user that owns this review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the total tasks tracked in this review period.
     */
    public function totalTasks(): int
    {
        return $this->tasks_completed + $this->tasks_missed;
    }

    /**
     * Get the completion rate as a percentage.
     */
    public function completionRate(): int
    {
        $total = $this->totalTasks();

        if ($total === 0) {
            return 100;
        }

        return (int) round(($this->tasks_completed / $total) * 100);
    }
}
