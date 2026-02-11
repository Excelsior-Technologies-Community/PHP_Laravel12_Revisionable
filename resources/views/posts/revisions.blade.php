<!-- resources/views/posts/revisions.blade.php -->
@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Revision History for: {{ $post->title }}</h5>
        <a href="{{ route('posts.show', $post) }}" class="btn btn-primary">Back to Post</a>
    </div>
    <div class="card-body">
        @if($revisions->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Revision #</th>
                            <th>Date/Time</th>
                            <th>User</th>
                            <th>Field</th>
                            <th>Old Value</th>
                            <th>New Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = $revisions->count(); @endphp
                        @foreach($revisions as $revision)
                        <tr>
                            <td>{{ $counter-- }}</td>
                            <td>{{ $revision->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                @php
                                    $user = $revision->userResponsible();
                                @endphp
                                {{ $user ? $user->name : 'System' }}
                            </td>
                            <td>
                                @if($revision->key == 'created_at' && !$revision->old_value)
                                    <span class="badge bg-success">CREATED</span>
                                @elseif($revision->key == 'deleted_at')
                                    <span class="badge bg-danger">DELETED</span>
                                @else
                                    {{ $revision->fieldName() }}
                                @endif
                            </td>
                            <td>
                                @if($revision->key == 'created_at' && !$revision->old_value)
                                    -
                                @else
                                    {{ $revision->oldValue() }}
                                @endif
                            </td>
                            <td>{{ $revision->newValue() }}</td>
                            <td>
                                @if($revision->key == 'created_at' && !$revision->old_value)
                                    Post was created
                                @elseif($revision->key == 'deleted_at')
                                    Post was deleted
                                @else
                                    Field was updated
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-muted">No revision history found for this post.</p>
        @endif
    </div>
</div>
@endsection