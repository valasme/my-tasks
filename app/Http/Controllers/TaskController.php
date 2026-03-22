<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Handles CRUD operations for authenticated users' tasks.
 *
 * Every action is guarded by {@see TaskPolicy} to ensure
 * users can only interact with their own tasks.
 */
class TaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * The allowed sort options for task listing.
     */
    private const array ALLOWED_SORTS = ['title_asc', 'title_desc'];

    /**
     * Display a paginated listing of the authenticated user's tasks.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $sort = $request->query('sort');
        $sort = in_array($sort, self::ALLOWED_SORTS, true) ? $sort : null;

        $query = $request->user()
            ->tasks()
            ->with('workspace:id,name')
            ->select(['id', 'workspace_id', 'title', 'status', 'priority', 'due_date', 'is_recurring_daily', 'recurring_times', 'completed_at']);

        $query = match ($sort) {
            'title_asc' => $query->orderBy('title'),
            'title_desc' => $query->orderByDesc('title'),
            default => $query->latest(),
        };

        $tasks = $query->paginate(15)->withQueryString();

        return view('tasks.index', compact('tasks', 'sort'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Task::class);

        $workspaces = $request->user()->workspaces()->orderBy('name')->get(['id', 'name']);

        return view('tasks.create', compact('workspaces'));
    }

    /**
     * Store a newly created task in storage.
     *
     * @param  StoreTaskRequest  $request  The validated request containing the new task data.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        try {
            $request->user()->tasks()->create($request->validated());

            // If converting from someday/maybe, delete the original item
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
            Log::error('Failed to create task.', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while creating the task. Please try again.');
        }
    }

    /**
     * Display the specified task.
     *
     * @param  Task  $task  The task instance resolved via route-model binding.
     */
    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->loadMissing('workspace:id,name');

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     *
     * @param  Task  $task  The task instance resolved via route-model binding.
     */
    public function edit(Request $request, Task $task): View
    {
        $this->authorize('update', $task);

        $workspaces = $request->user()->workspaces()->orderBy('name')->get(['id', 'name']);

        return view('tasks.edit', compact('task', 'workspaces'));
    }

    /**
     * Update the specified task in storage.
     *
     * @param  UpdateTaskRequest  $request  The validated request containing the updated task data.
     * @param  Task  $task  The task instance resolved via route-model binding.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        try {
            $task->update($request->validated());

            return redirect()
                ->route('tasks.show', $task)
                ->with('success', 'Task updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update task.', ['task' => $task->id, 'error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while updating the task. Please try again.');
        }
    }

    /**
     * Remove the specified task from storage.
     *
     * @param  Task  $task  The task instance resolved via route-model binding.
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
            Log::error('Failed to delete task.', ['task' => $task->id, 'error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong while deleting the task. Please try again.');
        }
    }
}
