<!-- resources/views/posts/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Post Details</h5>
                <div>
                    <a href="{{ route('posts.edit', $post) }}" class="btn btn-warning">Edit</a>
                    <a href="{{ route('posts.revisions', $post) }}" class="btn btn-secondary">View All Revisions</a>
                </div>
            </div>
            <div class="card-body">
                <h3>{{ $post->title }}</h3>
                <p class="text-muted">By: {{ $post->user->name }}</p>
                <p>{{ $post->content }}</p>
                <p>
                    Status: 
                    @if($post->is_published)
                        <span class="badge bg-success">Published</span>
                    @else
                        <span class="badge bg-secondary">Draft</span>
                    @endif
                </p>
                <p>Created: {{ $post->created_at->format('Y-m-d H:i') }}</p>
                <p>Last Updated: {{ $post->updated_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Recent Revisions (Last 5)</h5>
            </div>
            <div class="card-body">
                @if($revisions->count() > 0)
                    <div class="list-group">
                        @foreach($revisions->take(5) as $revision)
                        <div class="list-group-item">
                            <small class="text-muted">{{ $revision->created_at->format('Y-m-d H:i') }}</small><br>
                            <strong>
                                @php
                                    $user = $revision->userResponsible();
                                @endphp
                                {{ $user ? $user->name : 'System' }}
                            </strong><br>
                            @if($revision->key == 'created_at' && !$revision->old_value)
                                Created this post
                            @else
                                Changed <strong>{{ $revision->fieldName() }}</strong> 
                                from <em>"{{ $revision->oldValue() }}"</em>
                                to <em>"{{ $revision->newValue() }}"</em>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No revision history yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection