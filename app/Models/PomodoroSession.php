<?php

namespace App\Models;

use Database\Factories\PomodoroSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Represents a Pomodoro timer session linked to an optional task.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $task_id
 * @property Carbon $started_at
 * @property Carbon|null $ended_at
 * @property int $duration_minutes
 * @property string $type
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Task|null $task
 */
#[Fillable(['user_id', 'task_id', 'started_at', 'ended_at', 'duration_minutes', 'type', 'status'])]
class PomodoroSession extends Model
{
    /** @use HasFactory<PomodoroSessionFactory> */
    use HasFactory;

    /**
     * The valid type values for a Pomodoro session.
     */
    public const array TYPES = ['work', 'break'];

    /**
     * The valid status values for a Pomodoro session.
     */
    public const array STATUSES = ['active', 'completed', 'cancelled'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    /**
     * Get the user that owns this session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task linked to this session.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Determine whether this session is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Mark the session as completed.
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'ended_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark the session as cancelled.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'ended_at' => Carbon::now(),
        ]);
    }

    /**
     * Get the CSS classes for the status badge.
     */
    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'active' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'cancelled' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
            default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }

    /**
     * Get a human-readable label for the status.
     */
    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Get a human-readable label for the type.
     */
    public function typeLabel(): string
    {
        return ucfirst($this->type);
    }
}
