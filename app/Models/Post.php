<?php
// app/Models/Post.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Post extends Model
{
    use HasFactory, RevisionableTrait; // Add HasFactory here
    
    protected $revisionEnabled = true;
    protected $revisionCleanup = true;
    protected $historyLimit = 500;
    protected $revisionCreationsEnabled = true;
    protected $revisionNullString = 'nothing';
    protected $revisionUnknownString = 'unknown';
    
    protected $dontKeepRevisionOf = ['created_at', 'updated_at'];
    
    protected $fillable = [
        'title',
        'content',
        'is_published',
        'user_id'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
public function revisionHistory()
{
    return $this->hasMany(\App\Models\Revision::class, 'revisionable_id')
        ->where('revisionable_type', self::class)
        ->orderBy('created_at', 'desc');
}
}