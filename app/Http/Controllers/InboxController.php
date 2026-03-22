<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInboxItemRequest;
use App\Models\InboxItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * GTD inbox: capture thoughts quickly, process later.
 */
class InboxController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the inbox items.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InboxItem::class);

        $items = $request->user()
            ->inboxItems()
            ->where('is_processed', false)
            ->latest()
            ->paginate(15);

        $processedCount = $request->user()
            ->inboxItems()
            ->where('is_processed', true)
            ->count();

        return view('inbox.index', compact('items', 'processedCount'));
    }

    /**
     * Store a new inbox item.
     */
    public function store(StoreInboxItemRequest $request): RedirectResponse
    {
        try {
            $request->user()->inboxItems()->create($request->validated());

            return redirect()
                ->route('inbox.index')
                ->with('success', 'Item captured.');
        } catch (\Throwable $e) {
            Log::error('Failed to create inbox item.', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Convert an inbox item into a task by redirecting to the task creation form.
     */
    public function convertToTask(Request $request, InboxItem $inboxItem): RedirectResponse
    {
        $this->authorize('update', $inboxItem);

        $inboxItem->update(['is_processed' => true]);

        return redirect()
            ->route('tasks.create', ['title' => $inboxItem->body, 'from_inbox' => $inboxItem->id]);
    }

    /**
     * Delete an inbox item.
     */
    public function destroy(InboxItem $inboxItem): RedirectResponse
    {
        $this->authorize('delete', $inboxItem);

        try {
            $inboxItem->delete();

            return redirect()
                ->route('inbox.index')
                ->with('success', 'Item deleted.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete inbox item.', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }
}
