<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInboxItemRequest;
use App\Models\InboxItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * GTD inbox: capture thoughts quickly, process later.
 *
 * Supports filtering by status (all/unprocessed/processed), search,
 * and workspace, as well as multiple sort options. Every action
 * is guarded by {@see InboxItemPolicy} to ensure users can only
 * interact with their own inbox items.
 */
class InboxController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a paginated, filterable listing of the authenticated user's inbox items.
     *
     * Accepted query parameters:
     *  - `status`    (string)  Filter by status (all, unprocessed, processed)
     *  - `search`   (string)  Free-text search across body content
     *  - `workspace` (int)    Filter by workspace ID
     *  - `sort`     (string)  Sort key — see {@see InboxItem::ALLOWED_SORTS}
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InboxItem::class);

        $workspaces = $request->user()
            ->workspaces()
            ->orderBy('name')
            ->get(['id', 'name']);

        $filters = $this->extractFilters($request, $workspaces->pluck('id')->all());

        $query = $request->user()
            ->inboxItems()
            ->with([
                'workspace' => fn (Builder|BelongsTo $workspaceQuery) => $workspaceQuery
                    ->where('user_id', $request->user()->id)
                    ->select(['id', 'name']),
                'task',
            ])
            ->select(['id', 'workspace_id', 'body', 'is_processed', 'task_id', 'created_at']);

        $this->applyFilters($query, $filters);

        $query->applySort($filters['sort']);

        $items = $query->paginate(15)->withQueryString();

        $counts = $this->getCounts($request->user()->id);

        return view('inbox.index', [
            'items' => $items,
            'filters' => $filters,
            'workspaces' => $workspaces,
            'counts' => $counts,
        ]);
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
            Log::error('Failed to create inbox item.', [
                'error' => $e->getMessage(),
                'user' => $request->user()->id,
            ]);

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

        try {
            $inboxItem->update(['is_processed' => true]);

            return redirect()
                ->route('tasks.create', ['title' => $inboxItem->body, 'from_inbox' => $inboxItem->id]);
        } catch (\Throwable $e) {
            Log::error('Failed to convert inbox item to task.', [
                'inbox_item' => $inboxItem->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('inbox.index')
                ->with('error', 'Something went wrong. Please try again.');
        }
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
            Log::error('Failed to delete inbox item.', [
                'inbox_item' => $inboxItem->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Extract and sanitize filter values from the request.
     *
     * Only whitelisted values are kept — anything invalid is discarded.
     *
     * @param  array<int, int>  $allowedWorkspaceIds
     * @return array{search: ?string, status: ?string, workspace: ?int, sort: ?string}
     */
    private function extractFilters(Request $request, array $allowedWorkspaceIds): array
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $workspace = $request->query('workspace');
        $sort = $request->query('sort');

        return [
            'search' => is_string($search) && trim($search) !== '' ? trim($search) : null,
            'status' => is_string($status) && in_array($status, InboxItem::FILTER_STATUSES, true) ? $status : 'unprocessed',
            'workspace' => $this->resolveWorkspaceFilter($workspace, $allowedWorkspaceIds),
            'sort' => is_string($sort) && array_key_exists($sort, InboxItem::ALLOWED_SORTS) ? $sort : null,
        ];
    }

    /**
     * Resolve the workspace filter to a user-owned workspace ID.
     *
     * @param  array<int, int>  $allowedWorkspaceIds
     */
    private function resolveWorkspaceFilter(mixed $workspace, array $allowedWorkspaceIds): ?int
    {
        if (! is_string($workspace) && ! is_int($workspace)) {
            return null;
        }

        $workspace = (string) $workspace;

        if (! ctype_digit($workspace)) {
            return null;
        }

        $workspaceId = (int) $workspace;

        return in_array($workspaceId, $allowedWorkspaceIds, true) ? $workspaceId : null;
    }

    /**
     * Apply active filters to the inbox item query.
     *
     * @param  array{search: ?string, status: ?string, workspace: ?int, sort: ?string}  $filters
     */
    private function applyFilters(Builder|HasMany $query, array $filters): void
    {
        $query
            ->when($filters['search'] !== null, fn (Builder $q): Builder => $q->search($filters['search']))
            ->when($filters['status'] === 'unprocessed', fn (Builder $q): Builder => $q->unprocessed())
            ->when($filters['status'] === 'processed', fn (Builder $q): Builder => $q->processed())
            ->when(
                $filters['workspace'] !== null,
                fn (Builder $q): Builder => $q->where('workspace_id', $filters['workspace'])
            );
    }

    /**
     * Get counts for unprocessed and processed items.
     *
     * @return array{unprocessed: int, processed: int}
     */
    private function getCounts(int $userId): array
    {
        return [
            'unprocessed' => InboxItem::where('user_id', $userId)->unprocessed()->count(),
            'processed' => InboxItem::where('user_id', $userId)->processed()->count(),
        ];
    }
}
