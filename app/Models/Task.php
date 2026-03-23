<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A task belonging to a user with optional recurring-daily scheduling.
 *
 * Tasks support filtering by status, priority, workspace, and free-text search.
 * Recurring-daily tasks replace a fixed due date with one or more daily times.
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property string $status One of {@see STATUSES}
 * @property string $priority One of {@see PRIORITIES}
 * @property Carbon|null $due_date
 * @property bool $is_recurring_daily
 * @property array|null $recurring_times Array of "H:i" strings
 * @property Carbon|null $completed_at
 * @property int|null $workspace_id
 * @property string|null $category One of {@see CATEGORIES}
 * @property int|null $estimated_minutes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User                     $user
 * @property-read Workspace|null           $workspace
 * @property-read Collection<int, MoodLog> $moodLogs
 *
 * @method static Builder|Task filterByStatus(string $status)
 * @method static Builder|Task filterByPriority(string $priority)
 * @method static Builder|Task filterByWorkspace(int $workspaceId)
 * @method static Builder|Task search(string $term)
 * @method static Builder|Task overdue()
 * @method static Builder|Task dueToday()
 * @method static Builder|Task incomplete()
 */
#[Fillable([
    'title',
    'description',
    'status',
    'priority',
    'due_date',
    'is_recurring_daily',
    'recurring_times',
    'completed_at',
    'workspace_id',
    'category',
    'estimated_minutes',
])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /** The valid status values for a task. */
    public const array STATUSES = ['pending', 'in_progress', 'completed'];

    /** The valid priority values for a task. */
    public const array PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    /** The valid schedule status values for a task. */
    public const array SCHEDULE_STATUSES = ['pending', 'missed', 'completed_on_time', 'completed_late'];

    /** The valid categories for a task. */
    public const array CATEGORIES = ['someday_maybe'];

    /** The allowed sort options and their query clauses. */
    public const array ALLOWED_SORTS = [
        'newest' => ['created_at', 'desc'],
        'oldest' => ['created_at', 'asc'],
        'title_asc' => ['title', 'asc'],
        'title_desc' => ['title', 'desc'],
        'due_date_asc' => ['due_date', 'asc'],
        'due_date_desc' => ['due_date', 'desc'],
        'priority_desc' => ['priority', 'desc'],
        'priority_asc' => ['priority', 'asc'],
    ];

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
            'estimated_minutes' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    /**
     * Bootstrap the model and register event listeners.
     *
     * Automatically sets or clears `completed_at` when the task status
     * transitions to or from "completed".
     */
    protected static function booted(): void
    {
        static::saving(function (Task $task): void {
            if (! $task->isDirty('status')) {
                return;
            }

            if ($task->status === 'completed' && $task->completed_at === null) {
                $task->completed_at = Carbon::now();
            } elseif ($task->status !== 'completed') {
                $task->completed_at = null;
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** Get the user that owns this task. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Get the workspace this task belongs to (optional). */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /** Get the mood logs recorded against this task. */
    public function moodLogs(): HasMany
    {
        return $this->hasMany(MoodLog::class);
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    /** Filter tasks by a specific status value. */
    public function scopeFilterByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /** Filter tasks by a specific priority value. */
    public function scopeFilterByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /** Filter tasks belonging to a specific workspace. */
    public function scopeFilterByWorkspace(Builder $query, int $workspaceId): Builder
    {
        return $query->where('workspace_id', $workspaceId);
    }

    /** Search tasks by title or description (case-insensitive LIKE). */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /** Filter to overdue tasks: past due date, not completed, not due today. */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today());
    }

    /** Filter to tasks due today. */
    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('due_date', Carbon::today());
    }

    /** Filter to tasks that are not completed. */
    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->where('status', '!=', 'completed');
    }

    /**
     * Apply a validated sort key to the query.
     *
     * Falls back to "newest" (created_at desc) for unknown keys.
     * Priority sorting uses a CASE expression so that
     * urgent > high > medium > low regardless of alphabetical order.
     */
    public function scopeApplySort(Builder $query, ?string $sortKey): Builder
    {
        $sortKey = $sortKey && array_key_exists($sortKey, self::ALLOWED_SORTS) ? $sortKey : 'newest';

        if (str_starts_with($sortKey, 'priority_')) {
            $direction = str_ends_with($sortKey, '_desc') ? 'desc' : 'asc';

            return $query->orderByRaw(
                "CASE priority
                    WHEN 'urgent' THEN 4
                    WHEN 'high'   THEN 3
                    WHEN 'medium' THEN 2
                    WHEN 'low'    THEN 1
                    ELSE 0
                END {$direction}"
            );
        }

        [$column, $direction] = self::ALLOWED_SORTS[$sortKey];

        return $query->orderBy($column, $direction);
    }

    // -------------------------------------------------------------------------
    // Display Helpers
    // -------------------------------------------------------------------------

    /** Get the CSS classes for the priority badge. */
    public function priorityBadgeClasses(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'low' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
            default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }

    /** Get the CSS classes for the status badge. */
    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'pending' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
            'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
        };
    }

    /** Get a human-readable label for the status. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            default => ucfirst($this->status),
        };
    }

    /** Get a human-readable label for the priority. */
    public function priorityLabel(): string
    {
        return ucfirst($this->priority);
    }

    /**
     * Determine whether this task is overdue (past due and incomplete).
     *
     * A task is NOT overdue if it is completed, has no due date, or the
     * due date is today (still time to act).
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
     * Returns one of: pending, missed, completed_on_time, completed_late.
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

    /** Get a human-readable label for the schedule status. */
    public function scheduleStatusLabel(): string
    {
        return match ($this->scheduleStatus()) {
            'pending' => 'Pending',
            'missed' => 'Missed',
            'completed_on_time' => 'On Time',
            'completed_late' => 'Completed Late',
            default => ucfirst($this->scheduleStatus()),
        };
    }

    /** Get the CSS classes for the schedule status badge. */
    public function scheduleStatusBadgeClasses(): string
    {
        return match ($this->scheduleStatus()) {
            'pending' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
            'missed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'completed_on_time' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'completed_late' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
            default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300',
        };
    }

    /** Get a human-readable label for the category. */
    public function categoryLabel(): string
    {
        return match ($this->category) {
            'someday_maybe' => 'Someday / Maybe',
            default => 'Uncategorized',
        };
    }

    /** Get the CSS classes for the category badge. */
    public function categoryBadgeClasses(): string
    {
        return 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300';
    }

    /**
     * Format estimated minutes as a human-readable duration string.
     *
     * Examples: "30m", "1h", "1h 30m", or null if not set.
     */
    public function formattedEstimate(): ?string
    {
        if ($this->estimated_minutes === null) {
            return null;
        }

        $hours = intdiv($this->estimated_minutes, 60);
        $minutes = $this->estimated_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        }

        return $hours > 0 ? "{$hours}h" : "{$minutes}m";
    }
}
