<?php

namespace App\Models;

use Database\Factories\WorkspaceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A workspace (department) belonging to a user for grouping tasks.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Collection<int, Task> $tasks
 *
 * @method static Builder|Workspace search(string $term)
 * @method static Builder|Workspace filterByTaskPresence(string $option)
 * @method static Builder|Workspace applySort(?string $sortKey)
 */
#[Fillable(['name'])]
class Workspace extends Model
{
    /** @use HasFactory<WorkspaceFactory> */
    use HasFactory;

    /** The allowed sort options and their query clauses. */
    public const array ALLOWED_SORTS = [
        'name_asc' => ['name', 'asc'],
        'name_desc' => ['name', 'desc'],
        'newest' => ['created_at', 'desc'],
        'oldest' => ['created_at', 'asc'],
        'tasks_desc' => ['tasks_count', 'desc'],
        'tasks_asc' => ['tasks_count', 'asc'],
    ];

    /** The valid "has tasks" filter options. */
    public const array HAS_TASKS_OPTIONS = ['with_tasks', 'without_tasks'];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Get the user that owns the workspace.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tasks assigned to this workspace.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    /** Search workspaces by name (case-insensitive LIKE). */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);

        return $query->where('name', 'like', "%{$term}%");
    }

    /**
     * Filter workspaces by task presence.
     *
     * Requires `withCount('tasks')` to be applied to the query.
     */
    public function scopeFilterByTaskPresence(Builder $query, string $option): Builder
    {
        return match ($option) {
            'with_tasks' => $query->has('tasks'),
            'without_tasks' => $query->doesntHave('tasks'),
            default => $query,
        };
    }

    /**
     * Apply a validated sort key to the query.
     *
     * Falls back to "name_asc" for unknown keys.
     * Sorting by tasks_count requires `withCount('tasks')`.
     */
    public function scopeApplySort(Builder $query, ?string $sortKey): Builder
    {
        $sortKey = $sortKey && array_key_exists($sortKey, self::ALLOWED_SORTS) ? $sortKey : 'name_asc';

        [$column, $direction] = self::ALLOWED_SORTS[$sortKey];

        return $query->orderBy($column, $direction);
    }
}
