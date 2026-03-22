<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMoodLogRequest;
use App\Models\MoodLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Mood/energy tracking for tasks.
 */
class MoodLogController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display mood log history and trends.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', MoodLog::class);

        $logs = $request->user()
            ->moodLogs()
            ->with('task:id,title')
            ->latest('logged_at')
            ->paginate(15);

        $moodDistribution = $request->user()
            ->moodLogs()
            ->select('mood', DB::raw('count(*) as total'))
            ->groupBy('mood')
            ->pluck('total', 'mood')
            ->toArray();

        return view('mood-logs.index', compact('logs', 'moodDistribution'));
    }

    /**
     * Store a new mood log entry.
     */
    public function store(StoreMoodLogRequest $request): RedirectResponse
    {
        try {
            $request->user()->moodLogs()->create(array_merge(
                $request->validated(),
                ['logged_at' => now()],
            ));

            return redirect()
                ->route('mood-logs.index')
                ->with('success', 'Mood logged.');
        } catch (\Throwable $e) {
            Log::error('Failed to create mood log.', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Delete a mood log entry.
     */
    public function destroy(MoodLog $moodLog): RedirectResponse
    {
        $this->authorize('delete', $moodLog);

        try {
            $moodLog->delete();

            return redirect()
                ->route('mood-logs.index')
                ->with('success', 'Mood log deleted.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete mood log.', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }
}
