<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only dashboard showing schedule statuses for non-daily tasks.
 *
 * Displays incomplete schedules (pending and missed) at the top,
 * and completed schedules (on time and late) at the bottom.
 */
class DueTaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * The allowed sort options for due task listing.
     */
    private const array ALLOWED_SORTS = ['title_asc', 'title_desc'];

    /**
     * Display the due tasks overview.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $sort = $request->query('sort');
        $sort = in_array($sort, self::ALLOWED_SORTS, true) ? $sort : null;

        $baseQuery = $request->user()
            ->tasks()
            ->where('is_recurring_daily', false)
            ->select(['id', 'title', 'status', 'priority', 'due_date', 'completed_at']);

        $incompleteQuery = (clone $baseQuery)
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date');

        $incompleteSchedules = match ($sort) {
            'title_asc' => $incompleteQuery->orderBy('title'),
            'title_desc' => $incompleteQuery->orderByDesc('title'),
            default => $incompleteQuery->orderBy('due_date'),
        };

        $incompleteSchedules = $incompleteSchedules->get();

        $completedSchedules = (clone $baseQuery)
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->paginate(15)
            ->withQueryString();

        return view('due-tasks.index', compact('incompleteSchedules', 'completedSchedules', 'sort'));
    }
}
