<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Revision;
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
        if ($request->status != '') {

            $query->where(
                'is_published',
                $request->status
            );
        }

        // Author Filter
        if ($request->filled('user')) {

            $query->where(
                'user_id',
                $request->user
            );
        }

        $posts = $query
            ->oldest()
            ->paginate(5)
            ->withQueryString();

        $users = User::orderBy('name')->get();

        return view(
            'posts.index',
            compact(
                'posts',
                'users'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Post
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $users = User::orderBy('name')->get();

        return view(
            'posts.create',
            compact('users')
        );
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
            ->with(
                'success',
                'Post created successfully.'
            );
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
            ->oldest()
            ->get();

        // Statistics

        $revisionCount = $revisions->count();

        $firstRevision = $revisions->last();

        $lastRevision = $revisions->first();

        $lastEditor = null;

        if ($lastRevision) {

            $lastEditor = $lastRevision->userResponsible();
        }

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

        return view(
            'posts.edit',
            compact(
                'post',
                'users'
            )
        );
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
            ->with(
                'success',
                'Post updated successfully.'
            );
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
            ->with(
                'success',
                'Post deleted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Revision History
    |--------------------------------------------------------------------------
    */

    public function revisions(Post $post)
    {
        $revisions = $post
            ->revisionHistory()
            ->oldest()
            ->paginate(5);

        return view(
            'posts.revisions',
            compact(
                'post',
                'revisions'
            )
        );
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

        return new StreamedResponse(
            $callback,
            200,
            $headers
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete All Revisions
    |--------------------------------------------------------------------------
    */

    public function deleteAllRevisions(Post $post)
    {
        Revision::where(
            'revisionable_type',
            Post::class
        )
            ->where(
                'revisionable_id',
                $post->id
            )
            ->delete();

        return redirect()
            ->route(
                'posts.revisions',
                $post
            )
            ->with(
                'success',
                'All revisions deleted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Revision Analytics Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard()
    {
        /*
        |--------------------------------------------------------------------------
        | Basic Statistics
        |--------------------------------------------------------------------------
        */

        $totalPosts = Post::count();


        $totalRevisions = Revision::where(
            'revisionable_type',
            Post::class
        )->count();


        $todayRevisions = Revision::where(
            'revisionable_type',
            Post::class
        )
            ->whereDate(
                'created_at',
                today()
            )
            ->count();



        /*
        |--------------------------------------------------------------------------
        | Most Edited Post
        |--------------------------------------------------------------------------
        */

        $mostEditedPost = Revision::select(
            'revisionable_id',
            DB::raw('COUNT(*) as total')
        )
            ->where(
                'revisionable_type',
                Post::class
            )
            ->groupBy(
                'revisionable_id'
            )
            ->orderByDesc(
                'total'
            )
            ->first();



        /*
        |--------------------------------------------------------------------------
        | Top Editor
        |--------------------------------------------------------------------------
        */

        $topEditor = Revision::select(
            'user_id',
            DB::raw('COUNT(*) as total')
        )
            ->whereNotNull(
                'user_id'
            )
            ->groupBy(
                'user_id'
            )
            ->orderByDesc(
                'total'
            )
            ->first();



        /*
        |--------------------------------------------------------------------------
        | Recent Revision Activities
        |--------------------------------------------------------------------------
        */

        $recentActivities = Revision::where(
            'revisionable_type',
            Post::class
        )
            ->oldest()
            ->take(5)
            ->get();



        /*
        |--------------------------------------------------------------------------
        | Revision Chart Data
        |--------------------------------------------------------------------------
        */

        $chartData = Revision::where(
            'revisionable_type',
            Post::class
        )
            ->selectRaw(
                "
                DATE(created_at) as day,
                COUNT(*) as total
                "
            )
            ->groupBy(
                'day'
            )
            ->orderBy(
                'day'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Top 5 Edited Posts
        |--------------------------------------------------------------------------
        */

        $topEditedPosts = Revision::select(
            'revisionable_id',
            DB::raw('COUNT(*) as total')
        )
            ->where(
                'revisionable_type',
                Post::class
            )
            ->groupBy(
                'revisionable_id'
            )
            ->orderByDesc(
                'total'
            )
            ->take(5)
            ->get();



        /*
        |--------------------------------------------------------------------------
        | Most Edited Field
        |--------------------------------------------------------------------------
        */

        $mostEditedField = Revision::where(
            'revisionable_type',
            Post::class
        )
            ->select(
                'key',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(
                'key'
            )
            ->orderByDesc(
                'total'
            )
            ->first();



        /*
        |--------------------------------------------------------------------------
        | Average Revision Per Post
        |--------------------------------------------------------------------------
        */

        $averageRevisions = $totalPosts > 0
            ? round(
                $totalRevisions / $totalPosts,
                2
            )
            : 0;



        /*
        |--------------------------------------------------------------------------
        | Most Active Revision Day
        |--------------------------------------------------------------------------
        */

        $mostActiveDay = Revision::where(
            'revisionable_type',
            Post::class
        )
            ->selectRaw(
                "
                DATE(created_at) as day,
                COUNT(*) as total
                "
            )
            ->groupBy(
                'day'
            )
            ->orderByDesc(
                'total'
            )
            ->first();



        /*
        |--------------------------------------------------------------------------
        | Dashboard View
        |--------------------------------------------------------------------------
        */

        return view(
            'posts.dashboard',
            [

                'totalPosts' => $totalPosts,


                'totalRevisions' => $totalRevisions,


                'todayRevisions' => $todayRevisions,


                'mostEditedPost' => $mostEditedPost
                    ? Post::find(
                        $mostEditedPost->revisionable_id
                    )
                    : null,


                'topEditor' => $topEditor
                    ? User::find(
                        $topEditor->user_id
                    )
                    : null,


                'recentActivities' => $recentActivities,


                'chartData' => $chartData,


                'topEditedPosts' => $topEditedPosts,


                'mostEditedField' => $mostEditedField,


                'averageRevisions' => $averageRevisions,


                'mostActiveDay' => $mostActiveDay,

            ]
        );
    }
}
