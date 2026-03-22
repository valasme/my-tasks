<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimeBlockRequest;
use App\Http\Requests\UpdateTimeBlockRequest;
use App\Models\TimeBlock;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Handles CRUD operations for time blocks (time-blocking feature).
 */
class TimeBlockController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the time blocks calendar view.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TimeBlock::class);

        $date = $request->query('date', today()->format('Y-m-d'));

        $timeBlocks = $request->user()
            ->timeBlocks()
            ->with('task:id,title')
            ->where('date', $date)
            ->orderBy('start_time')
            ->get();

        return view('time-blocks.index', compact('timeBlocks', 'date'));
    }

    /**
     * Show the form for creating a new time block.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', TimeBlock::class);

        $tasks = $request->user()->tasks()
            ->where('status', '!=', 'completed')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('time-blocks.create', compact('tasks'));
    }

    /**
     * Store a newly created time block.
     */
    public function store(StoreTimeBlockRequest $request): RedirectResponse
    {
        try {
            $request->user()->timeBlocks()->create($request->validated());

            return redirect()
                ->route('time-blocks.index')
                ->with('success', 'Time block created successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to create time block.', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while creating the time block. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified time block.
     */
    public function edit(Request $request, TimeBlock $timeBlock): View
    {
        $this->authorize('update', $timeBlock);

        $tasks = $request->user()->tasks()
            ->where('status', '!=', 'completed')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('time-blocks.edit', compact('timeBlock', 'tasks'));
    }

    /**
     * Update the specified time block.
     */
    public function update(UpdateTimeBlockRequest $request, TimeBlock $timeBlock): RedirectResponse
    {
        try {
            $timeBlock->update($request->validated());

            return redirect()
                ->route('time-blocks.index')
                ->with('success', 'Time block updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update time block.', ['timeBlock' => $timeBlock->id, 'error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while updating the time block. Please try again.');
        }
    }

    /**
     * Remove the specified time block.
     */
    public function destroy(TimeBlock $timeBlock): RedirectResponse
    {
        $this->authorize('delete', $timeBlock);

        try {
            $timeBlock->delete();

            return redirect()
                ->route('time-blocks.index')
                ->with('success', 'Time block deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete time block.', ['timeBlock' => $timeBlock->id, 'error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong while deleting the time block. Please try again.');
        }
    }
}
