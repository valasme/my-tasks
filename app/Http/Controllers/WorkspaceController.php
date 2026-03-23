<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceRequest;
use App\Http\Requests\UpdateWorkspaceRequest;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Handles CRUD operations for authenticated users' workspaces.
 *
 * Supports filtering by search term, task presence, and multiple sort
 * options. Every action is guarded by {@see WorkspacePolicy} to ensure
 * users can only interact with their own workspaces.
 */
class WorkspaceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a paginated, filterable listing of the authenticated user's workspaces.
     *
     * Accepted query parameters:
     *  - `search`    (string) Free-text search across workspace name
     *  - `has_tasks` (string) Filter by task presence (with_tasks, without_tasks)
     *  - `sort`      (string) Sort key — see {@see Workspace::ALLOWED_SORTS}
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Workspace::class);

        $filters = $this->extractFilters($request);

        $query = $request->user()
            ->workspaces()
            ->withCount('tasks');

        $this->applyFilters($query, $filters);

        $query->applySort($filters['sort']);

        $workspaces = $query->paginate(15)->withQueryString();

        return view('workspaces.index', [
            'workspaces' => $workspaces,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new workspace.
     */
    public function create(): View
    {
        $this->authorize('create', Workspace::class);

        return view('workspaces.create');
    }

    /**
     * Store a newly created workspace in storage.
     */
    public function store(StoreWorkspaceRequest $request): RedirectResponse
    {
        try {
            $request->user()->workspaces()->create($request->validated());

            return redirect()
                ->route('workspaces.index')
                ->with('success', 'Workspace created successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to create workspace.', [
                'error' => $e->getMessage(),
                'user' => $request->user()->id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while creating the workspace. Please try again.');
        }
    }

    /**
     * Display the specified workspace with its tasks.
     */
    public function show(Workspace $workspace): View
    {
        $this->authorize('view', $workspace);

        $tasks = $workspace->tasks()
            ->select(['id', 'title', 'status', 'priority', 'due_date', 'is_recurring_daily', 'completed_at'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('workspaces.show', compact('workspace', 'tasks'));
    }

    /**
     * Show the form for editing the specified workspace.
     */
    public function edit(Workspace $workspace): View
    {
        $this->authorize('update', $workspace);

        return view('workspaces.edit', compact('workspace'));
    }

    /**
     * Update the specified workspace in storage.
     */
    public function update(UpdateWorkspaceRequest $request, Workspace $workspace): RedirectResponse
    {
        try {
            $workspace->update($request->validated());

            return redirect()
                ->route('workspaces.show', $workspace)
                ->with('success', 'Workspace updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update workspace.', [
                'workspace' => $workspace->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while updating the workspace. Please try again.');
        }
    }

    /**
     * Remove the specified workspace from storage.
     */
    public function destroy(Workspace $workspace): RedirectResponse
    {
        $this->authorize('delete', $workspace);

        try {
            $workspace->delete();

            return redirect()
                ->route('workspaces.index')
                ->with('success', 'Workspace deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete workspace.', [
                'workspace' => $workspace->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong while deleting the workspace. Please try again.');
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
     * @return array{search: ?string, has_tasks: ?string, sort: ?string}
     */
    private function extractFilters(Request $request): array
    {
        $search = $request->query('search');
        $hasTasks = $request->query('has_tasks');
        $sort = $request->query('sort');

        return [
            'search' => is_string($search) && trim($search) !== '' ? trim($search) : null,
            'has_tasks' => is_string($hasTasks) && in_array($hasTasks, Workspace::HAS_TASKS_OPTIONS, true) ? $hasTasks : null,
            'sort' => is_string($sort) && array_key_exists($sort, Workspace::ALLOWED_SORTS) ? $sort : null,
        ];
    }

    /**
     * Apply active filters to the workspace query.
     *
     * @param  array{search: ?string, has_tasks: ?string, sort: ?string}  $filters
     */
    private function applyFilters(Builder|HasMany $query, array $filters): void
    {
        $query
            ->when($filters['search'] !== null, fn (Builder $q): Builder => $q->search($filters['search']))
            ->when($filters['has_tasks'] !== null, fn (Builder $q): Builder => $q->filterByTaskPresence($filters['has_tasks']));
    }
}
