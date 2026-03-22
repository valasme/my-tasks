<?php

namespace App\Http\Controllers;

use App\Models\WeeklyReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeeklyReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = $request->user()
            ->weeklyReviews()
            ->latest('week_start')
            ->paginate(10);

        return view('weekly-reviews.index', compact('reviews'));
    }

    public function create(): View
    {
        return view('weekly-reviews.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'week_start' => ['required', 'date'],
            'week_end' => ['required', 'date', 'after_or_equal:week_start'],
            'tasks_completed' => ['required', 'integer', 'min:0'],
            'tasks_created' => ['required', 'integer', 'min:0'],
            'tasks_missed' => ['required', 'integer', 'min:0'],
            'summary' => ['nullable', 'string', 'max:5000'],
        ]);

        $request->user()->weeklyReviews()->create($validated);

        return redirect()->route('weekly-reviews.index')
            ->with('success', __('Weekly review created.'));
    }

    public function show(WeeklyReview $weeklyReview): View
    {
        if ($weeklyReview->user_id !== auth()->id()) {
            abort(403);
        }

        return view('weekly-reviews.show', compact('weeklyReview'));
    }

    public function edit(WeeklyReview $weeklyReview): View
    {
        if ($weeklyReview->user_id !== auth()->id()) {
            abort(403);
        }

        return view('weekly-reviews.edit', compact('weeklyReview'));
    }

    public function update(Request $request, WeeklyReview $weeklyReview): RedirectResponse
    {
        if ($weeklyReview->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'week_start' => ['required', 'date'],
            'week_end' => ['required', 'date', 'after_or_equal:week_start'],
            'tasks_completed' => ['required', 'integer', 'min:0'],
            'tasks_created' => ['required', 'integer', 'min:0'],
            'tasks_missed' => ['required', 'integer', 'min:0'],
            'summary' => ['nullable', 'string', 'max:5000'],
        ]);

        $weeklyReview->update($validated);

        return redirect()->route('weekly-reviews.show', $weeklyReview)
            ->with('success', __('Weekly review updated.'));
    }

    public function destroy(WeeklyReview $weeklyReview): RedirectResponse
    {
        if ($weeklyReview->user_id !== auth()->id()) {
            abort(403);
        }

        $weeklyReview->delete();

        return redirect()->route('weekly-reviews.index')
            ->with('success', __('Weekly review deleted.'));
    }
}
