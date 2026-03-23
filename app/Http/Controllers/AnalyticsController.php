<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Read-only analytics dashboards: completion ratios and daily trends.
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
            'tasksPerDay',
        ));
    }
}
