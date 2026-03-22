<?php

namespace App\Http\Controllers;

use App\Models\Task;
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
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $request->user()
            ->tasks()
            ->where('category', 'someday_maybe')
            ->where('status', '!=', 'completed')
            ->select(['id', 'title', 'description', 'priority', 'created_at'])
            ->latest()
            ->paginate(15);

        return view('someday.index', compact('tasks'));
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
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $request->user()->tasks()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => 'pending',
                'priority' => 'low',
                'category' => 'someday_maybe',
            ]);

            return redirect()
                ->route('someday.index')
                ->with('success', 'Someday item created.');
        } catch (\Throwable $e) {
            Log::error('Failed to create someday item.', ['error' => $e->getMessage()]);

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
}
