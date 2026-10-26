<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Revision;
use App\Services\DiffService;
use App\Services\SnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Posts Listing (Search + Filter + Pagination)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Post::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($user) use ($search) {
                        $user->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status Filter
        if ($request->status !== null && $request->status !== '') {
            $query->where('is_published', $request->status);
        }

        // Author Filter
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        $posts = $query
            ->latest('updated_at')
            ->paginate(8)
            ->withQueryString();

        $users = User::orderBy('name')->get();

        return view('posts.index', compact('posts', 'users'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create Post
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('posts.create', compact('users'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store Post
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'user_id' => 'required|exists:users,id',
        ]);

        Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => $request->user_id,
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Show Post
    |--------------------------------------------------------------------------
    */
    public function show(Post $post)
    {
        $revisions = $post
            ->revisionHistory()
            ->latest('created_at')
            ->get();

        $revisionCount = $revisions->count();
        $firstRevision = $revisions->last();
        $lastRevision = $revisions->first();
        $lastEditor = $lastRevision ? $lastRevision->userResponsible() : null;

        return view(
            'posts.show',
            compact(
                'post',
                'revisions',
                'revisionCount',
                'firstRevision',
                'lastRevision',
                'lastEditor'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Post
    |--------------------------------------------------------------------------
    */
    public function edit(Post $post)
    {
        $users = User::orderBy('name')->get();
        return view('posts.edit', compact('post', 'users'));
    }

    /*
    |--------------------------------------------------------------------------
    | Update Post
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title'   => 'required|max:255',
            'content' => 'required',
            'user_id' => 'required|exists:users,id',
        ]);

        $post->update([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => $request->user_id,
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Post
    |--------------------------------------------------------------------------
    */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Revision History Listing
    |--------------------------------------------------------------------------
    */
    public function revisions(Post $post)
    {
        $revisions = $post
            ->revisionHistory()
            ->latest('id')
            ->paginate(10);

        $allRevisions = $post->revisionHistory()->latest('id')->get();

        return view('posts.revisions', compact('post', 'revisions', 'allRevisions'));
    }

    /*
    |--------------------------------------------------------------------------
    | FEATURE 1: 1-Click Granular Field Revert
    |--------------------------------------------------------------------------
    */
    public function revertField(Request $request, Post $post, Revision $revision)
    {
        if ($revision->revisionable_id != $post->id || $revision->revisionable_type !== Post::class) {
            abort(403, 'Unauthorized revision action.');
        }

        $key = $revision->key;
        $targetValue = $revision->old_value;

        if ($key === 'is_published') {
            $targetValue = (bool) $targetValue;
        }

        $post->{$key} = $targetValue;
        $post->save();

        $displayVal = $revision->oldValue() ?: 'blank';
        return redirect()
            ->route('posts.revisions', $post)
            ->with('success', "Field '{$revision->fieldName()}' successfully reverted to previous value: '{$displayVal}'.");
    }

    /*
    |--------------------------------------------------------------------------
    | FEATURE 1: Full Point-in-Time Snapshot Rollback
    |--------------------------------------------------------------------------
    */
    public function rollbackSnapshot(Request $request, Post $post, Revision $revision)
    {
        if ($revision->revisionable_id != $post->id || $revision->revisionable_type !== Post::class) {
            abort(403, 'Unauthorized revision action.');
        }

        $snapshot = SnapshotService::reconstructSnapshot($post, $revision->id);

        $post->update([
            'title' => $snapshot['title'],
            'content' => $snapshot['content'],
            'is_published' => $snapshot['is_published'],
            'user_id' => $snapshot['user_id'],
        ]);

        return redirect()
            ->route('posts.show', $post)
            ->with('success', "Post was successfully rolled back to Snapshot from {$snapshot['snapshot_at']} (Revision #{$revision->id}).");
    }

    /*
    |--------------------------------------------------------------------------
    | FEATURE 1: Revert Confirmation Details Preview (AJAX)
    |--------------------------------------------------------------------------
    */
    public function previewRevert(Post $post, Revision $revision)
    {
        if ($revision->revisionable_id != $post->id) {
            return response()->json(['error' => 'Invalid revision'], 403);
        }

        $user = $revision->userResponsible();

        return response()->json([
            'revision_id' => $revision->id,
            'field' => $revision->fieldName(),
            'key' => $revision->key,
            'current_value' => (string) $post->{$revision->key},
            'target_value' => (string) $revision->oldValue(),
            'date' => $revision->created_at->format('d M Y, H:i'),
            'author' => $user ? $user->name : 'System',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FEATURE 2: Visual Word-Level Diff (Inline & Split)
    |--------------------------------------------------------------------------
    */
    public function revisionDiff(Post $post, Revision $revision)
    {
        if ($revision->revisionable_id != $post->id) {
            return response()->json(['error' => 'Invalid revision'], 403);
        }

        $oldVal = (string) $revision->oldValue();
        $newVal = (string) $revision->newValue();

        $inlineHtml = DiffService::renderInlineDiff($oldVal, $newVal);
        $sideBySide = DiffService::renderSideBySide($oldVal, $newVal);
        $user = $revision->userResponsible();

        return response()->json([
            'revision_id' => $revision->id,
            'field' => $revision->fieldName(),
            'date' => $revision->created_at->format('d M Y, H:i'),
            'author' => $user ? $user->name : 'System',
            'old_value' => $oldVal,
            'new_value' => $newVal,
            'inline_html' => $inlineHtml,
            'side_by_side' => $sideBySide,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FEATURE 2: Compare Any Two Arbitrary Revisions
    |--------------------------------------------------------------------------
    */
    public function compareRevisions(Request $request, Post $post)
    {
        $allRevisions = $post->revisionHistory()->latest('id')->get();

        $revAId = $request->query('rev_a', optional($allRevisions->get(1))->id ?? optional($allRevisions->first())->id);
        $revBId = $request->query('rev_b', optional($allRevisions->first())->id);

        $snapshotA = $revAId ? SnapshotService::reconstructSnapshot($post, (int)$revAId) : null;
        $snapshotB = $revBId ? SnapshotService::reconstructSnapshot($post, (int)$revBId) : null;

        $diffs = [];
        if ($snapshotA && $snapshotB) {
            foreach (['title', 'content', 'is_published'] as $key) {
                $valA = (string) ($key === 'is_published' ? ($snapshotA[$key] ? 'Published' : 'Draft') : $snapshotA[$key]);
                $valB = (string) ($key === 'is_published' ? ($snapshotB[$key] ? 'Published' : 'Draft') : $snapshotB[$key]);

                $diffs[$key] = [
                    'label' => ucfirst(str_replace('_', ' ', $key)),
                    'old' => $valA,
                    'new' => $valB,
                    'inline_html' => DiffService::renderInlineDiff($valA, $valB),
                    'side_by_side' => DiffService::renderSideBySide($valA, $valB),
                ];
            }
        }

        return view('posts.compare', compact('post', 'allRevisions', 'revAId', 'revBId', 'snapshotA', 'snapshotB', 'diffs'));
    }

    /*
    |--------------------------------------------------------------------------
    | FEATURE 3: Interactive Time-Machine Snapshot Previewer
    |--------------------------------------------------------------------------
    */
    public function timeMachine(Post $post)
    {
        $timeline = SnapshotService::getTimelineSnapshots($post);

        return view('posts.time_machine', compact('post', 'timeline'));
    }

    /*
    |--------------------------------------------------------------------------
    | Export Revision History CSV
    |--------------------------------------------------------------------------
    */
    public function exportCsv(Post $post)
    {
        $fileName = 'post-' . $post->id . '-revisions.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($post) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Date',
                'User',
                'Field',
                'Old Value',
                'New Value',
            ]);

            foreach ($post->revisionHistory as $revision) {
                $user = $revision->userResponsible();
                fputcsv($file, [
                    $revision->created_at,
                    $user ? $user->name : 'System',
                    $revision->fieldName(),
                    strip_tags($revision->oldValue()),
                    strip_tags($revision->newValue()),
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete All Revisions
    |--------------------------------------------------------------------------
    */
    public function deleteAllRevisions(Post $post)
    {
        Revision::where('revisionable_type', Post::class)
            ->where('revisionable_id', $post->id)
            ->delete();

        return redirect()
            ->route('posts.revisions', $post)
            ->with('success', 'All revisions deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | FEATURE 6: Revision Analytics Dashboard (Enhanced Chart.js Studio)
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        $totalPosts = Post::count();
        $totalRevisions = Revision::where('revisionable_type', Post::class)->count();
        $todayRevisions = Revision::where('revisionable_type', Post::class)->whereDate('created_at', today())->count();

        // Most Edited Post
        $mostEditedPostRow = Revision::select('revisionable_id', DB::raw('COUNT(*) as total'))
            ->where('revisionable_type', Post::class)
            ->groupBy('revisionable_id')
            ->orderByDesc('total')
            ->first();

        // Top Editor
        $topEditorRow = Revision::select('user_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->first();

        // Recent Revision Activities
        $recentActivities = Revision::where('revisionable_type', Post::class)
            ->latest('id')
            ->take(8)
            ->get();

        // Chart 1: Revisions Volume Timeline (Daily for past 30 days)
        $chartData = Revision::where('revisionable_type', Post::class)
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->take(30)
            ->get();

        // Chart 2: Field Modification Breakdown (Donut Chart)
        $fieldDistribution = Revision::where('revisionable_type', Post::class)
            ->select('key', DB::raw('COUNT(*) as total'))
            ->groupBy('key')
            ->orderByDesc('total')
            ->get();

        // Chart 3: Top Contributors Leaderboard (Bar Chart)
        $topContributors = Revision::select('user_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->take(5)
            ->with('responsibleUser')
            ->get();

        // Top 5 Edited Posts
        $topEditedPosts = Revision::select('revisionable_id', DB::raw('COUNT(*) as total'))
            ->where('revisionable_type', Post::class)
            ->groupBy('revisionable_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Summary statistics
        $mostEditedField = Revision::where('revisionable_type', Post::class)
            ->select('key', DB::raw('COUNT(*) as total'))
            ->groupBy('key')
            ->orderByDesc('total')
            ->first();

        $averageRevisions = $totalPosts > 0 ? round($totalRevisions / $totalPosts, 2) : 0;

        $mostActiveDay = Revision::where('revisionable_type', Post::class)
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderByDesc('total')
            ->first();

        return view('posts.dashboard', [
            'totalPosts' => $totalPosts,
            'totalRevisions' => $totalRevisions,
            'todayRevisions' => $todayRevisions,
            'mostEditedPost' => $mostEditedPostRow ? Post::find($mostEditedPostRow->revisionable_id) : null,
            'topEditor' => $topEditorRow ? User::find($topEditorRow->user_id) : null,
            'recentActivities' => $recentActivities,
            'chartData' => $chartData,
            'fieldDistribution' => $fieldDistribution,
            'topContributors' => $topContributors,
            'topEditedPosts' => $topEditedPosts,
            'mostEditedField' => $mostEditedField,
            'averageRevisions' => $averageRevisions,
            'mostActiveDay' => $mostActiveDay,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FEATURE 6: Audit Report Studio & Printable Compliance Report
    |--------------------------------------------------------------------------
    */
    public function auditReport(Request $request)
    {
        $query = Revision::where('revisionable_type', Post::class)->with('responsibleUser');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('key')) {
            $query->where('key', $request->key);
        }

        $revisions = $query->latest('id')->paginate(50)->withQueryString();
        $users = User::orderBy('name')->get();
        $fields = Revision::where('revisionable_type', Post::class)->distinct()->pluck('key');

        return view('posts.audit_report', compact('revisions', 'users', 'fields'));
    }
}
