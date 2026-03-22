<?php

namespace App\Models;

use Database\Factories\TimeBlockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A scheduled block of time on a user's daily calendar.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $task_id
 * @property string $title
 * @property Carbon $date
 * @property string $start_time
 * @property string $end_time
 * @property int|null $estimated_minutes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Task|null $task
 */
#[Fillable(['user_id', 'task_id', 'title', 'date', 'start_time', 'end_time', 'estimated_minutes'])]
class TimeBlock extends Model
{
    /** @use HasFactory<TimeBlockFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estimated_minutes' => 'integer',
        ];
    }

    /**
     * Get/set the date attribute, ensuring Y-m-d storage format.
     */
    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value) : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /**
     * Get the user that owns this time block.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task linked to this time block.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the formatted start time.
     */
    public function formattedStartTime(): string
    {
        return Carbon::parse($this->start_time)->format('g:i A');
    }

    /**
     * Get the formatted end time.
     */
    public function formattedEndTime(): string
    {
        return Carbon::parse($this->end_time)->format('g:i A');
    }
}
