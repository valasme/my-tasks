<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceRequest;
use App\Http\Requests\UpdateWorkspaceRequest;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Handles CRUD operations for authenticated users' workspaces.
 *
 * Every action is guarded by {@see WorkspacePolicy} to ensure
 * users can only interact with their own workspaces.
 */
class WorkspaceController extends Controller
{
    use AuthorizesRequests;

    /**
     * The allowed sort options for workspace listing.
     */
    private const array ALLOWED_SORTS = ['name_asc', 'name_desc'];

    /**
     * Display a paginated listing of the authenticated user's workspaces.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Workspace::class);

        $sort = $request->query('sort');
        $sort = in_array($sort, self::ALLOWED_SORTS, true) ? $sort : null;

        $query = $request->user()
            ->workspaces()
            ->withCount('tasks');

        $query = match ($sort) {
            'name_desc' => $query->orderByDesc('name'),
            default => $query->orderBy('name'),
        };

        $workspaces = $query->paginate(15)->withQueryString();

        return view('workspaces.index', compact('workspaces', 'sort'));
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
            Log::error('Failed to create workspace.', ['error' => $e->getMessage()]);

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
            Log::error('Failed to update workspace.', ['workspace' => $workspace->id, 'error' => $e->getMessage()]);

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
            Log::error('Failed to delete workspace.', ['workspace' => $workspace->id, 'error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong while deleting the workspace. Please try again.');
        }
    }
}
