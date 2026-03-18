<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A task belonging to a user with optional recurring-daily scheduling.
 *
 * @property int                        $id
 * @property int                        $user_id
 * @property string                     $title
 * @property string|null                $description
 * @property string                     $status
 * @property string                     $priority
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property bool                       $is_recurring_daily
 * @property array|null                 $recurring_times
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User                  $user
 */
#[Fillable(['title', 'description', 'status', 'priority', 'due_date', 'is_recurring_daily', 'recurring_times'])]
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
        ];
    }

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
