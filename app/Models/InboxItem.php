<?php

namespace App\Models;

use Database\Factories\InboxItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A quick-capture GTD inbox item that can be processed into a task.
 *
 * @property int $id
 * @property int $user_id
 * @property string $body
 * @property bool $is_processed
 * @property int|null $task_id
 * @property int|null $workspace_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Task|null $task
 * @property-read Workspace|null $workspace
 *
 * @method static Builder|InboxItem search(string $term)
 * @method static Builder|InboxItem applySort(?string $sortKey)
 */
#[Fillable(['user_id', 'body', 'is_processed', 'task_id', 'workspace_id'])]
class InboxItem extends Model
{
    /** @use HasFactory<InboxItemFactory> */
    use HasFactory;

    public const array FILTER_STATUSES = ['all', 'unprocessed', 'processed'];

    public const array ALLOWED_SORTS = [
        'newest' => ['created_at', 'desc'],
        'oldest' => ['created_at', 'asc'],
        'title_asc' => ['body', 'asc'],
        'title_desc' => ['body', 'desc'],
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_processed' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this inbox item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task this item was converted to.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the workspace assigned to this item.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Scope a query to only include unprocessed items.
     */
    public function scopeUnprocessed(Builder $query): Builder
    {
        return $query->where('is_processed', false);
    }

    /**
     * Scope a query to only include processed items.
     */
    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('is_processed', true);
    }

    /**
     * Search inbox items by body content (case-insensitive).
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);

        return $query->where('body', 'like', "%{$term}%");
    }

    /**
     * Apply a validated sort key to the query.
     *
     * Falls back to "newest" for unknown keys.
     */
    public function scopeApplySort(Builder $query, ?string $sortKey): Builder
    {
        $sortKey = $sortKey && array_key_exists($sortKey, self::ALLOWED_SORTS) ? $sortKey : 'newest';

        [$column, $direction] = self::ALLOWED_SORTS[$sortKey];

        return $query->orderBy($column, $direction);
    }
}
