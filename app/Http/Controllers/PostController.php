<?php
// app/Http/Controllers/PostController.php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

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
}