<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Read-only analytics dashboards: productivity trends, completion ratios, habit streaks.
 */
class AnalyticsController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the analytics dashboard.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();

        // Completion ratio
        $totalTasks = $user->tasks()->count();
        $completedTasks = $user->tasks()->where('status', 'completed')->count();
        $completionRatio = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // Productivity by day of week
        $productivityByDay = $user->productivityLogs()
            ->select('day_of_week', DB::raw('count(*) as total'))
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->pluck('total', 'day_of_week')
            ->toArray();

        // Productivity by hour
        $productivityByHour = $user->productivityLogs()
            ->select('hour_of_day', DB::raw('count(*) as total'))
            ->groupBy('hour_of_day')
            ->orderBy('hour_of_day')
            ->pluck('total', 'hour_of_day')
            ->toArray();

        // Habit streaks
        $habitStreaks = $user->habitStreaks()
            ->with('task:id,title')
            ->orderByDesc('current_streak')
            ->get();

        // Tasks completed per day (last 14 days)
        $tasksPerDay = $user->tasks()
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(14))
            ->select(DB::raw('DATE(completed_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        return view('analytics.index', compact(
            'totalTasks',
            'completedTasks',
            'completionRatio',
            'productivityByDay',
            'productivityByHour',
            'habitStreaks',
            'tasksPerDay',
        ));
    }
}
