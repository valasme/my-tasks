<?php

namespace App\Models;

use Database\Factories\DailyGoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A daily target for task completion with progress tracking.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $date
 * @property int $target_count
 * @property int $completed_count
 * @property string|null $target_priority
 * @property bool $is_met
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
#[Fillable(['user_id', 'date', 'target_count', 'completed_count', 'target_priority', 'is_met'])]
class DailyGoal extends Model
{
    /** @use HasFactory<DailyGoalFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'target_count' => 'integer',
            'completed_count' => 'integer',
            'is_met' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment the completed count and check whether the goal is met.
     */
    public function incrementCompleted(): void
    {
        $this->completed_count++;
        $this->checkIfMet();
        $this->save();
    }

    /**
     * Determine and set whether the goal has been met.
     */
    public function checkIfMet(): void
    {
        $this->is_met = $this->completed_count >= $this->target_count;
    }

    /**
     * Get the completion progress as a percentage.
     */
    public function progressPercent(): int
    {
        if ($this->target_count === 0) {
            return 100;
        }

        return (int) min(100, round(($this->completed_count / $this->target_count) * 100));
    }
}
