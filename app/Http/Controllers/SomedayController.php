<?php

namespace App\Http\Controllers;

use App\Http\Requests\SomedayStoreRequest;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Manages Someday/Maybe items (tasks with category=someday_maybe).
 */
class SomedayController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the someday/maybe list.
     *
     * Accepted query parameters:
     *  - `search`    (string)  Free-text search across title and description
     *  - `priority`  (string)  Filter by priority (low, medium, high, urgent)
     *  - `sort`      (string)  Sort key — see {@see Task::ALLOWED_SORTS}
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $filters = $this->extractFilters($request);

        $query = $request->user()
            ->tasks()
            ->where('category', 'someday_maybe')
            ->where('status', '!=', 'completed')
            ->select([
                'id', 'title', 'description', 'priority', 'created_at',
            ]);

        $this->applyFilters($query, $filters);

        $query->applySort($filters['sort']);

        $tasks = $query->paginate(15)->withQueryString();

        return view('someday.index', [
            'tasks' => $tasks,
            'filters' => $filters,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    /**
     * Show the form for creating a new someday item.
     */
    public function create(): View
    {
        $this->authorize('create', Task::class);

        return view('someday.create');
    }

    /**
     * Store a newly created someday item.
     */
    public function store(SomedayStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        try {
            $request->user()->tasks()->create([
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
                'status' => 'pending',
                'priority' => 'low',
                'category' => 'someday_maybe',
            ]);

            return redirect()
                ->route('someday.index')
                ->with('success', 'Someday item created.');
        } catch (\Throwable $e) {
            Log::error('Failed to create someday item.', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Activate a someday item by redirecting to the task creation form with pre-filled data.
     */
    public function activate(Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        return redirect()
            ->route('tasks.create', [
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'from_someday' => $task->id,
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
     * @return array{search: ?string, priority: ?string, sort: ?string}
     */
    private function extractFilters(Request $request): array
    {
        $search = $request->query('search');
        $priority = $request->query('priority');
        $sort = $request->query('sort');

        return [
            'search' => is_string($search) && trim($search) !== '' ? trim($search) : null,
            'priority' => is_string($priority) && in_array($priority, Task::PRIORITIES, true) ? $priority : null,
            'sort' => is_string($sort) && array_key_exists($sort, Task::ALLOWED_SORTS) ? $sort : null,
        ];
    }

    /**
     * Apply active filters to the task query.
     *
     * @param  array{search: ?string, priority: ?string, sort: ?string}  $filters
     */
    private function applyFilters(Builder|HasMany $query, array $filters): void
    {
        $query
            ->when($filters['search'] !== null, fn (Builder $taskQuery): Builder => $taskQuery->search($filters['search']))
            ->when($filters['priority'] !== null, fn (Builder $taskQuery): Builder => $taskQuery->filterByPriority($filters['priority']));
    }
}
