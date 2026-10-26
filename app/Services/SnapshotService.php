<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Revision;

class SnapshotService
{
    /**
     * Reconstruct the exact attributes of a post at a specific revision ID.
     */
    public static function reconstructSnapshot(Post $post, int $targetRevisionId): array
    {
        // Start from current model attributes
        $attributes = [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'is_published' => (bool) $post->is_published,
            'user_id' => $post->user_id,
            'user_name' => $post->user ? $post->user->name : 'Unknown',
            'created_at' => $post->created_at ? $post->created_at->format('Y-m-d H:i:s') : '',
            'updated_at' => $post->updated_at ? $post->updated_at->format('Y-m-d H:i:s') : '',
        ];

        // Fetch all revisions that occurred AFTER targetRevisionId in descending order
        $revisionsAfter = Revision::where('revisionable_type', Post::class)
            ->where('revisionable_id', $post->id)
            ->where('id', '>', $targetRevisionId)
            ->orderBy('id', 'desc')
            ->get();

        // Undo each revision back to its old_value
        foreach ($revisionsAfter as $rev) {
            $key = $rev->key;
            if ($key === 'created_at' || $key === 'updated_at') {
                continue;
            }
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = self::castAttribute($key, $rev->old_value);
            }
        }

        // Get target revision timestamp
        $targetRev = Revision::find($targetRevisionId);
        if ($targetRev) {
            $attributes['snapshot_at'] = $targetRev->created_at->format('d M Y, H:i');
            $user = $targetRev->userResponsible();
            $attributes['editor_name'] = $user ? $user->name : 'System';
            $attributes['revision_id'] = $targetRev->id;
        }

        return $attributes;
    }

    /**
     * Build chronological snapshots of all revisions for the Time-Machine slider.
     */
    public static function getTimelineSnapshots(Post $post): array
    {
        $revisions = Revision::where('revisionable_type', Post::class)
            ->where('revisionable_id', $post->id)
            ->orderBy('id', 'asc')
            ->get();

        $timeline = [];
        $step = 1;

        foreach ($revisions as $rev) {
            $snapshot = self::reconstructSnapshot($post, $rev->id);
            $user = $rev->userResponsible();

            $timeline[] = [
                'step' => $step++,
                'revision_id' => $rev->id,
                'field' => $rev->fieldName(),
                'key' => $rev->key,
                'old_value' => $rev->oldValue(),
                'new_value' => $rev->newValue(),
                'user_name' => $user ? $user->name : 'System',
                'date' => $rev->created_at->format('d M Y, H:i'),
                'relative_time' => $rev->created_at->diffForHumans(),
                'snapshot' => $snapshot,
            ];
        }

        // Add current live state as final timeline step
        $timeline[] = [
            'step' => $step,
            'revision_id' => 0,
            'field' => 'Live Current State',
            'key' => 'current',
            'old_value' => '',
            'new_value' => '',
            'user_name' => $post->user ? $post->user->name : 'System',
            'date' => $post->updated_at->format('d M Y, H:i'),
            'relative_time' => $post->updated_at->diffForHumans(),
            'snapshot' => [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'is_published' => (bool) $post->is_published,
                'user_name' => $post->user ? $post->user->name : 'Unknown',
                'snapshot_at' => $post->updated_at->format('d M Y, H:i') . ' (Live)',
                'editor_name' => $post->user ? $post->user->name : 'System',
                'revision_id' => 'current',
            ],
        ];

        return $timeline;
    }

    /**
     * Cast raw database string value to proper type.
     */
    private static function castAttribute(string $key, $value)
    {
        if ($key === 'is_published') {
            return (bool) $value;
        }
        if ($key === 'user_id') {
            return (int) $value;
        }
        return $value;
    }
}
