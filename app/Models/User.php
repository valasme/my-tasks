<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * The application's user model with two-factor authentication support.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Task>      $tasks
 * @property-read Collection<int, Workspace>  $workspaces
 * @property-read Collection<int, HabitStreak> $habitStreaks
 * @property-read Collection<int, ProductivityLog> $productivityLogs
 * @property-read Collection<int, PomodoroSession> $pomodoroSessions
 * @property-read Collection<int, TimeBlock> $timeBlocks
 * @property-read Collection<int, InboxItem> $inboxItems
 * @property-read UserXp|null $xp
 * @property-read Collection<int, XpTransaction> $xpTransactions
 * @property-read Collection<int, DailyGoal> $dailyGoals
 * @property-read Collection<int, WeeklyReview> $weeklyReviews
 * @property-read Collection<int, MoodLog> $moodLogs
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the tasks owned by the user.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the workspaces owned by the user.
     */
    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class);
    }

    /**
     * Get the habit streaks for the user.
     */
    public function habitStreaks(): HasMany
    {
        return $this->hasMany(HabitStreak::class);
    }

    /**
     * Get the productivity logs for the user.
     */
    public function productivityLogs(): HasMany
    {
        return $this->hasMany(ProductivityLog::class);
    }

    /**
     * Get the pomodoro sessions for the user.
     */
    public function pomodoroSessions(): HasMany
    {
        return $this->hasMany(PomodoroSession::class);
    }

    /**
     * Get the time blocks for the user.
     */
    public function timeBlocks(): HasMany
    {
        return $this->hasMany(TimeBlock::class);
    }

    /**
     * Get the inbox items for the user.
     */
    public function inboxItems(): HasMany
    {
        return $this->hasMany(InboxItem::class);
    }

    /**
     * Get the user's XP record.
     */
    public function xp(): HasOne
    {
        return $this->hasOne(UserXp::class);
    }

    /**
     * Get the XP transactions for the user.
     */
    public function xpTransactions(): HasMany
    {
        return $this->hasMany(XpTransaction::class);
    }

    /**
     * Get the daily goals for the user.
     */
    public function dailyGoals(): HasMany
    {
        return $this->hasMany(DailyGoal::class);
    }

    /**
     * Get the weekly reviews for the user.
     */
    public function weeklyReviews(): HasMany
    {
        return $this->hasMany(WeeklyReview::class);
    }

    /**
     * Get the mood logs for the user.
     */
    public function moodLogs(): HasMany
    {
        return $this->hasMany(MoodLog::class);
    }
}
