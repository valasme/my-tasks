<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DueTaskController;
use App\Http\Controllers\GamificationController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\MoodLogController;
use App\Http\Controllers\PomodoroController;
use App\Http\Controllers\SomedayController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimeBlockController;
use App\Http\Controllers\WeeklyReviewController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::resource('tasks', TaskController::class);
    Route::resource('workspaces', WorkspaceController::class);
    Route::get('due-tasks', [DueTaskController::class, 'index'])->name('due-tasks.index');

    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Pomodoro Timer
    Route::get('pomodoro', [PomodoroController::class, 'index'])->name('pomodoro.index');
    Route::post('pomodoro/start', [PomodoroController::class, 'start'])->name('pomodoro.start');
    Route::post('pomodoro/{pomodoro}/stop', [PomodoroController::class, 'stop'])->name('pomodoro.stop');
    Route::post('pomodoro/{pomodoro}/cancel', [PomodoroController::class, 'cancel'])->name('pomodoro.cancel');

    // Time Blocks
    Route::resource('time-blocks', TimeBlockController::class)->except(['show']);

    // GTD Inbox
    Route::get('inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::post('inbox', [InboxController::class, 'store'])->name('inbox.store');
    Route::post('inbox/{inboxItem}/convert', [InboxController::class, 'convertToTask'])->name('inbox.convert');
    Route::delete('inbox/{inboxItem}', [InboxController::class, 'destroy'])->name('inbox.destroy');

    // Someday/Maybe
    Route::get('someday', [SomedayController::class, 'index'])->name('someday.index');
    Route::get('someday/create', [SomedayController::class, 'create'])->name('someday.create');
    Route::post('someday', [SomedayController::class, 'store'])->name('someday.store');
    Route::post('someday/{task}/activate', [SomedayController::class, 'activate'])->name('someday.activate');

    // Gamification (XP, levels, daily goals)
    Route::get('gamification', [GamificationController::class, 'index'])->name('gamification.index');
    Route::post('gamification/daily-goal', [GamificationController::class, 'setDailyGoal'])->name('gamification.daily-goal');

    // Weekly Reviews
    Route::get('weekly-reviews', [WeeklyReviewController::class, 'index'])->name('weekly-reviews.index');
    Route::get('weekly-reviews/create', [WeeklyReviewController::class, 'create'])->name('weekly-reviews.create');
    Route::post('weekly-reviews', [WeeklyReviewController::class, 'store'])->name('weekly-reviews.store');
    Route::get('weekly-reviews/{weeklyReview}', [WeeklyReviewController::class, 'show'])->name('weekly-reviews.show');
    Route::get('weekly-reviews/{weeklyReview}/edit', [WeeklyReviewController::class, 'edit'])->name('weekly-reviews.edit');
    Route::put('weekly-reviews/{weeklyReview}', [WeeklyReviewController::class, 'update'])->name('weekly-reviews.update');
    Route::delete('weekly-reviews/{weeklyReview}', [WeeklyReviewController::class, 'destroy'])->name('weekly-reviews.destroy');

    // Mood Logs
    Route::get('mood-logs', [MoodLogController::class, 'index'])->name('mood-logs.index');
    Route::post('mood-logs', [MoodLogController::class, 'store'])->name('mood-logs.store');
    Route::delete('mood-logs/{moodLog}', [MoodLogController::class, 'destroy'])->name('mood-logs.destroy');
});

require __DIR__.'/settings.php';
