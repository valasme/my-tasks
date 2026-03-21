<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A task belonging to a user with optional recurring-daily scheduling.
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property string $priority
 * @property Carbon|null $due_date
 * @property bool $is_recurring_daily
 * @property array|null $recurring_times
 * @property Carbon|null $completed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int|null $workspace_id
 * @property-read User                  $user
 * @property-read Workspace|null         $workspace
 */
#[Fillable(['title', 'description', 'status', 'priority', 'due_date', 'is_recurring_daily', 'recurring_times', 'completed_at', 'workspace_id'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * The valid status values for a task.
     */
    public const array STATUSES = ['pending', 'in_progress', 'completed'];

    /**
     * The valid priority values for a task.
     */
    public const array PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    /**
     * The valid schedule status values for a task.
     */
    public const array SCHEDULE_STATUSES = ['pending', 'missed', 'completed_on_time', 'completed_late'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_recurring_daily' => 'boolean',
            'recurring_times' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Bootstrap the model and register event listeners.
     *
     * Automatically sets or clears the completed_at timestamp
     * when the task status transitions to or from completed.
     */
    protected static function booted(): void
    {
        static::saving(function (Task $task): void {
            if ($task->isDirty('status')) {
                if ($task->status === 'completed' && $task->completed_at === null) {
                    $task->completed_at = Carbon::now();
                } elseif ($task->status !== 'completed') {
                    $task->completed_at = null;
                }
            }
        });
    }

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the workspace this task belongs to.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the CSS classes for the priority badge.
     */
    public function priorityBadgeClasses(): string
    {
        return match ($this->priority) {
            'low' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'medium' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
            'urgent' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }

    /**
     * Get the CSS classes for the status badge.
     */
    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
            'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }

    /**
     * Get a human-readable label for the status.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get a human-readable label for the priority.
     */
    public function priorityLabel(): string
    {
        return ucfirst($this->priority);
    }

    /**
     * Determine whether this task's schedule is missed (overdue and incomplete).
     */
    public function isMissed(): bool
    {
        return $this->status !== 'completed'
            && $this->due_date !== null
            && $this->due_date->isPast()
            && ! $this->due_date->isToday();
    }

    /**
     * Get the computed schedule status for this task.
     *
     * Combines the stored status with due-date logic to produce
     * one of the four schedule statuses: pending, missed,
     * completed_on_time, or completed_late.
     */
    public function scheduleStatus(): string
    {
        if ($this->status === 'completed') {
            if ($this->due_date && $this->completed_at && $this->completed_at->startOfDay()->gt($this->due_date)) {
                return 'completed_late';
            }

            return 'completed_on_time';
        }

        return $this->isMissed() ? 'missed' : 'pending';
    }

    /**
     * Get a human-readable label for the schedule status.
     */
    public function scheduleStatusLabel(): string
    {
        $status = $this->scheduleStatus();

        return match ($status) {
            'pending' => 'Pending',
            'missed' => 'Missed',
            'completed_on_time' => 'On Time',
            'completed_late' => 'Completed Late',
            default => ucfirst($status),
        };
    }

    /**
     * Get the CSS classes for the schedule status badge.
     */
    public function scheduleStatusBadgeClasses(): string
    {
        $status = $this->scheduleStatus();

        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
            'missed' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            'completed_on_time' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'completed_late' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }
}
