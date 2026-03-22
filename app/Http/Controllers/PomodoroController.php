<?php

namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Manages Pomodoro focus-timer sessions.
 */
class PomodoroController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the Pomodoro timer page with session history.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PomodoroSession::class);

        $sessions = $request->user()
            ->pomodoroSessions()
            ->where('status', 'completed')
            ->latest('ended_at')
            ->paginate(15);

        $activeSession = $request->user()
            ->pomodoroSessions()
            ->where('status', 'active')
            ->latest()
            ->first();

        $todayCount = $request->user()
            ->pomodoroSessions()
            ->where('status', 'completed')
            ->where('type', 'work')
            ->whereDate('ended_at', today())
            ->count();

        return view('pomodoro.index', compact('sessions', 'activeSession', 'todayCount'));
    }

    /**
     * Start a new Pomodoro session.
     */
    public function start(Request $request): RedirectResponse
    {
        $this->authorize('create', PomodoroSession::class);

        $validated = $request->validate([
            'task_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'type' => ['required', 'string', 'in:work,break'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:120'],
        ]);

        try {
            $request->user()->pomodoroSessions()->create([
                'task_id' => $validated['task_id'] ?? null,
                'type' => $validated['type'],
                'duration_minutes' => $validated['duration_minutes'],
                'started_at' => now(),
                'status' => 'active',
            ]);

            return redirect()
                ->route('pomodoro.index')
                ->with('success', 'Pomodoro session started.');
        } catch (\Throwable $e) {
            Log::error('Failed to start Pomodoro session.', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Stop the active Pomodoro session.
     */
    public function stop(Request $request, PomodoroSession $pomodoro): RedirectResponse
    {
        $this->authorize('update', $pomodoro);

        try {
            $pomodoro->update([
                'ended_at' => now(),
                'status' => 'completed',
            ]);

            return redirect()
                ->route('pomodoro.index')
                ->with('success', 'Pomodoro session completed.');
        } catch (\Throwable $e) {
            Log::error('Failed to stop Pomodoro session.', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Cancel the active Pomodoro session.
     */
    public function cancel(Request $request, PomodoroSession $pomodoro): RedirectResponse
    {
        $this->authorize('update', $pomodoro);

        try {
            $pomodoro->update([
                'ended_at' => now(),
                'status' => 'cancelled',
            ]);

            return redirect()
                ->route('pomodoro.index')
                ->with('success', 'Pomodoro session cancelled.');
        } catch (\Throwable $e) {
            Log::error('Failed to cancel Pomodoro session.', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }
}
