<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only dashboard showing schedule statuses for non-daily tasks.
 *
 * Displays incomplete schedules (pending and missed) at the top,
 * and completed schedules (on time and late) at the bottom.
 *
 * Supports filtering by search, status, priority, and workspace,
 * as well as sort options. Every action is guarded by {@see TaskPolicy}
 * to ensure users can only interact with their own tasks.
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

        $workspaces = $request->user()
            ->workspaces()
            ->orderBy('name')
            ->get(['id', 'name']);

        $filters = $this->extractFilters($request, $workspaces->pluck('id')->all());

        $baseQuery = $request->user()
            ->tasks()
            ->where('is_recurring_daily', false)
            ->with([
                'workspace' => fn (Builder|BelongsTo $workspaceQuery) => $workspaceQuery
                    ->where('user_id', $request->user()->id)
                    ->select(['id', 'name']),
            ])
            ->select([
                'id', 'workspace_id', 'title', 'status', 'priority',
                'due_date', 'completed_at',
            ]);

        $this->applyFilters($baseQuery, $filters, $request->user()->id);

        // Separate incomplete and completed tasks
        $incompleteQuery = clone $baseQuery;
        $incompleteQuery->where('status', '!=', 'completed')
            ->whereNotNull('due_date');

        $completedQuery = clone $baseQuery;
        $completedQuery->where('status', 'completed');

        // Apply sorting to incomplete tasks
        $incompleteQuery = match ($filters['sort']) {
            'title_asc' => $incompleteQuery->orderBy('title'),
            'title_desc' => $incompleteQuery->orderByDesc('title'),
            default => $incompleteQuery->orderBy('due_date'),
        };

        $incompleteSchedules = $incompleteQuery->get();

        // Apply sorting and pagination to completed tasks
        $completedSchedules = $completedQuery
            ->orderByDesc('completed_at')
            ->paginate(15)
            ->withQueryString();

        return view('due-tasks.index', [
            'incompleteSchedules' => $incompleteSchedules,
            'completedSchedules' => $completedSchedules,
            'filters' => $filters,
            'workspaces' => $workspaces,
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Extract and sanitize filter values from the request.
     *
     * Only whitelisted values are kept — anything invalid is discarded.
     *
     * @param  array<int, int>  $allowedWorkspaceIds
     * @return array{search: ?string, status: ?string, priority: ?string, workspace: ?int, sort: ?string}
     */
    private function extractFilters(Request $request, array $allowedWorkspaceIds): array
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $priority = $request->query('priority');
        $workspace = $request->query('workspace');
        $sort = $request->query('sort');

        return [
            'search' => is_string($search) && trim($search) !== '' ? trim($search) : null,
            'status' => is_string($status) && in_array($status, Task::STATUSES, true) ? $status : null,
            'priority' => is_string($priority) && in_array($priority, Task::PRIORITIES, true) ? $priority : null,
            'workspace' => $this->resolveWorkspaceFilter($workspace, $allowedWorkspaceIds),
            'sort' => is_string($sort) && in_array($sort, self::ALLOWED_SORTS, true) ? $sort : null,
        ];
    }

    /**
     * Resolve the workspace filter to a user-owned workspace ID.
     *
     * @param  array<int, int>  $allowedWorkspaceIds
     */
    private function resolveWorkspaceFilter(mixed $workspace, array $allowedWorkspaceIds): ?int
    {
        if (! is_string($workspace) && ! is_int($workspace)) {
            return null;
        }

        $workspace = (string) $workspace;

        if (! ctype_digit($workspace)) {
            return null;
        }

        $workspaceId = (int) $workspace;

        return in_array($workspaceId, $allowedWorkspaceIds, true) ? $workspaceId : null;
    }

    /**
     * Apply active filters to the task query.
     *
     * @param  array{search: ?string, status: ?string, priority: ?string, workspace: ?int, sort: ?string}  $filters
     */
    private function applyFilters(Builder|HasMany $query, array $filters, int $userId): void
    {
        $query
            ->when($filters['search'] !== null, fn (Builder $taskQuery): Builder => $taskQuery->search($filters['search']))
            ->when($filters['status'] !== null, fn (Builder $taskQuery): Builder => $taskQuery->filterByStatus($filters['status']))
            ->when($filters['priority'] !== null, fn (Builder $taskQuery): Builder => $taskQuery->filterByPriority($filters['priority']))
            ->when(
                $filters['workspace'] !== null,
                fn (Builder $taskQuery): Builder => $taskQuery
                    ->where('workspace_id', $filters['workspace'])
                    ->whereHas(
                        'workspace',
                        fn (Builder $workspaceQuery): Builder => $workspaceQuery->where('user_id', $userId)
                    )
            );
    }
}
