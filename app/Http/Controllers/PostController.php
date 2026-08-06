<?php
// app/Http/Controllers/PostController.php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Revision;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    // Display all posts
    public function index()
    {
        $posts = Post::with('user')->latest()->get();
        return view('posts.index', compact('posts'));
    }

    // Show create form
    public function create()
    {
        $users = User::all();
        return view('posts.create', compact('users'));
    }

    // Store new post
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'is_published' => $request->has('is_published'),
            'user_id' => $request->user_id,
        ]);

        return redirect()->route('posts.index')
            ->with('success', 'Post created successfully!');
    }

    // Display single post with revisions
    public function show(Post $post)
    {
        $revisions = $post->revisionHistory;
        return view('posts.show', compact('post', 'revisions'));
    }

    // Show edit form
    public function edit(Post $post)
    {
        $users = User::all();
        return view('posts.edit', compact('post', 'users'));
    }

    // Update post
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $post->update([
            'title' => $request->title,
            'content' => $request->content,
            'is_published' => $request->has('is_published'),
            'user_id' => $request->user_id,
        ]);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated successfully!');
    }

    // Delete post
    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }

    // Show revision history
    public function revisions(Post $post)
    {
        $revisions = $post->revisionHistory()->orderBy('created_at', 'desc')->get();
        return view('posts.revisions', compact('post', 'revisions'));
    }

    public function dashboard()
    {
        $totalPosts = Post::count();

        $totalRevisions = Revision::where(
            'revisionable_type',
            Post::class
        )->count();

        $todayRevisions = Revision::where(
            'revisionable_type',
            Post::class
        )
            ->whereDate('created_at', today())
            ->count();

        $mostEditedPost = Revision::select(
            'revisionable_id',
            DB::raw('COUNT(*) as total')
        )
            ->where('revisionable_type', Post::class)
            ->groupBy('revisionable_id')
            ->orderByDesc('total')
            ->first();

        $topEditor = Revision::select(
            'user_id',
            DB::raw('COUNT(*) as total')
        )
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->first();

        $recentActivities = Revision::where(
            'revisionable_type',
            Post::class
        )
            ->latest()
            ->take(10)
            ->get();

        $chartData = Revision::where(
            'revisionable_type',
            Post::class
        )
            ->selectRaw("
    DATE(created_at) as day,
    COUNT(*) as total
")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // NEW

        $topEditedPosts = Revision::select(
            'revisionable_id',
            DB::raw('COUNT(*) as total')
        )
            ->where('revisionable_type', Post::class)
            ->groupBy('revisionable_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $mostEditedField = Revision::where(
            'revisionable_type',
            Post::class
        )
            ->select(
                'key',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('key')
            ->orderByDesc('total')
            ->first();

        $averageRevisions = $totalPosts > 0
            ? round($totalRevisions / $totalPosts, 2)
            : 0;

        $mostActiveDay = Revision::where(
            'revisionable_type',
            Post::class
        )
            ->selectRaw("
    DATE(created_at) as day,
    COUNT(*) as total
")
            ->groupBy('day')
            ->orderByDesc('total')
            ->first();

        return view('posts.dashboard', [

            'totalPosts' => $totalPosts,

            'totalRevisions' => $totalRevisions,

            'todayRevisions' => $todayRevisions,

            'mostEditedPost' => $mostEditedPost
                ? Post::find($mostEditedPost->revisionable_id)
                : null,

            'topEditor' => $topEditor
                ? User::find($topEditor->user_id)
                : null,

            'recentActivities' => $recentActivities,

            'chartData' => $chartData,

            // NEW

            'topEditedPosts' => $topEditedPosts,

            'mostEditedField' => $mostEditedField,

            'averageRevisions' => $averageRevisions,

            'mostActiveDay' => $mostActiveDay,

        ]);
    }
}
