<?php

namespace App\Models;

use Database\Factories\UserXpFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Tracks the user's total experience points and current level.
 *
 * @property int $id
 * @property int $user_id
 * @property int $total_xp
 * @property int $level
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
#[Fillable(['user_id', 'total_xp', 'level'])]
class UserXp extends Model
{
    /** @use HasFactory<UserXpFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'user_xp';

    /**
     * The XP points awarded per task priority.
     */
    public const array XP_PER_PRIORITY = [
        'low' => 10,
        'medium' => 25,
        'high' => 50,
        'urgent' => 100,
    ];

    /**
     * The XP required per level.
     */
    public const int XP_PER_LEVEL = 100;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_xp' => 'integer',
            'level' => 'integer',
        ];
    }

    /**
     * Get the user that owns this XP record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add XP points and recalculate the level.
     */
    public function addXp(int $points): void
    {
        $this->total_xp += $points;
        $this->level = $this->calculateLevel();
        $this->save();
    }

    /**
     * Calculate the level based on total XP.
     */
    public function calculateLevel(): int
    {
        return (int) floor($this->total_xp / self::XP_PER_LEVEL) + 1;
    }

    /**
     * Get the XP needed to reach the next level.
     */
    public function xpForNextLevel(): int
    {
        return ($this->level * self::XP_PER_LEVEL) - $this->total_xp;
    }

    /**
     * Get the percentage progress toward the next level.
     */
    public function progressPercent(): int
    {
        $xpInCurrentLevel = $this->total_xp % self::XP_PER_LEVEL;

        return (int) round(($xpInCurrentLevel / self::XP_PER_LEVEL) * 100);
    }

    /**
     * Get the level title based on current level.
     */
    public function levelTitle(): string
    {
        return match (true) {
            $this->level >= 50 => 'Legendary',
            $this->level >= 30 => 'Master',
            $this->level >= 20 => 'Expert',
            $this->level >= 10 => 'Veteran',
            $this->level >= 5 => 'Skilled',
            default => 'Beginner',
        };
    }
}
