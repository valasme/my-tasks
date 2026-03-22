<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyGoalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * XP, levels, and daily goals gamification controller.
 */
class GamificationController extends Controller
{
    /**
     * Display the gamification dashboard (XP, level, daily goals).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $userXp = $user->xp;
        $recentTransactions = $user->xpTransactions()
            ->latest()
            ->take(20)
            ->get();

        $todayGoal = $user->dailyGoals()
            ->whereDate('date', today())
            ->first();

        $weeklyGoals = $user->dailyGoals()
            ->where('date', '>=', now()->subDays(7)->format('Y-m-d'))
            ->orderBy('date')
            ->get();

        return view('gamification.index', compact('userXp', 'recentTransactions', 'todayGoal', 'weeklyGoals'));
    }

    /**
     * Set or update today's daily goal.
     */
    public function setDailyGoal(StoreDailyGoalRequest $request): RedirectResponse
    {
        try {
            $request->user()->dailyGoals()->updateOrCreate(
                ['date' => today()->startOfDay()],
                ['target_count' => $request->validated('target_count')],
            );

            return redirect()
                ->route('gamification.index')
                ->with('success', 'Daily goal updated.');
        } catch (\Throwable $e) {
            Log::error('Failed to set daily goal.', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }
}
