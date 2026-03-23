<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Handles CRUD operations for authenticated users' tasks.
 *
 * Supports filtering by status, priority, workspace, and free-text search,
 * as well as multiple sort options. Every action is guarded by {@see TaskPolicy}
 * to ensure users can only interact with their own tasks.
 */
class TaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a paginated, filterable listing of the authenticated user's tasks.
     *
     * Accepted query parameters:
     *  - `search`    (string)  Free-text search across title and description
     *  - `status`    (string)  Filter by status (pending, in_progress, completed)
     *  - `priority`  (string)  Filter by priority (low, medium, high, urgent)
     *  - `workspace` (int)     Filter by workspace ID
     *  - `sort`      (string)  Sort key — see {@see Task::ALLOWED_SORTS}
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $workspaces = $request->user()
            ->workspaces()
            ->orderBy('name')
            ->get(['id', 'name']);

        $filters = $this->extractFilters($request, $workspaces->pluck('id')->all());

        $query = $request->user()
            ->tasks()
            ->with([
                'workspace' => fn (Builder|BelongsTo $workspaceQuery) => $workspaceQuery
                    ->where('user_id', $request->user()->id)
                    ->select(['id', 'name']),
            ])
            ->select([
                'id', 'workspace_id', 'title', 'status', 'priority',
                'due_date', 'is_recurring_daily', 'recurring_times',
                'completed_at', 'estimated_minutes', 'created_at',
            ]);

        $this->applyFilters($query, $filters, $request->user()->id);

        $query->applySort($filters['sort']);

        $tasks = $query->paginate(15)->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'filters' => $filters,
            'workspaces' => $workspaces,
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Task::class);

        $workspaces = $request->user()
            ->workspaces()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('tasks.create', compact('workspaces'));
    }

    /**
     * Store a newly created task in storage.
     *
     * If the `from_someday` query parameter is present and references a
     * valid "someday_maybe" task, that task is deleted after the new one
     * is created (converting a someday item into a real task).
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        try {
            $request->user()->tasks()->create($request->validated());

            if ($fromSomeday = $request->query('from_someday')) {
                $request->user()->tasks()
                    ->where('id', $fromSomeday)
                    ->where('category', 'someday_maybe')
                    ->delete();
            }

            return redirect()
                ->route('tasks.index')
                ->with('success', 'Task created successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to create task.', [
                'error' => $e->getMessage(),
                'user' => $request->user()->id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while creating the task. Please try again.');
        }
    }

    /**
     * Display the specified task with its workspace loaded.
     */
    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->loadMissing('workspace:id,name');

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Request $request, Task $task): View
    {
        $this->authorize('update', $task);

        $workspaces = $request->user()
            ->workspaces()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('tasks.edit', compact('task', 'workspaces'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        try {
            $task->update($request->validated());

            return redirect()
                ->route('tasks.show', $task)
                ->with('success', 'Task updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update task.', [
                'task' => $task->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while updating the task. Please try again.');
        }
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        try {
            $task->delete();

            return redirect()
                ->route('tasks.index')
                ->with('success', 'Task deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete task.', [
                'task' => $task->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong while deleting the task. Please try again.');
        }
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
            'sort' => is_string($sort) && array_key_exists($sort, Task::ALLOWED_SORTS) ? $sort : null,
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
