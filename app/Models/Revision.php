<?php
// app/Models/Revision.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\Revision as RevisionableRevision;

class Revision extends RevisionableRevision
{
    // Add a relationship to get the user who made the change
    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // Helper method to get user name safely
    public function getUserNameAttribute()
    {
        $user = $this->userResponsible();
        return $user ? $user->name : 'System';
    }
}