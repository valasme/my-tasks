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
 */
#[Fillable(['user_id', 'body', 'is_processed', 'task_id', 'workspace_id'])]
class InboxItem extends Model
{
    /** @use HasFactory<InboxItemFactory> */
    use HasFactory;

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
}
