<?php

namespace App\Models;

use Database\Factories\MoodLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Tracks mood/energy level when completing tasks.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $task_id
 * @property string $mood
 * @property string|null $note
 * @property Carbon $logged_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Task|null $task
 */
#[Fillable(['user_id', 'task_id', 'mood', 'note', 'logged_at'])]
class MoodLog extends Model
{
    /** @use HasFactory<MoodLogFactory> */
    use HasFactory;

    /**
     * The valid mood values.
     */
    public const array MOODS = ['energized', 'neutral', 'drained'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this mood log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task associated with this mood log.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get a human-readable label for the mood.
     */
    public function moodLabel(): string
    {
        return ucfirst($this->mood);
    }

    /**
     * Get the CSS classes for the mood badge.
     */
    public function moodBadgeClasses(): string
    {
        return match ($this->mood) {
            'energized' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'neutral' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'drained' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }

    /**
     * Get the emoji for the mood.
     */
    public function moodEmoji(): string
    {
        return match ($this->mood) {
            'energized' => '⚡',
            'neutral' => '😐',
            'drained' => '😴',
            default => '❓',
        };
    }
}
