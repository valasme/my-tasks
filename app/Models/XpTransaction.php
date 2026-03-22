<?php

namespace App\Models;

use Database\Factories\XpTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Records an individual XP award event for audit and display.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $task_id
 * @property int $points
 * @property string $reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Task|null $task
 */
#[Fillable(['user_id', 'task_id', 'points', 'reason'])]
class XpTransaction extends Model
{
    /** @use HasFactory<XpTransactionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    /**
     * Get the user that owns this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task associated with this transaction.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
