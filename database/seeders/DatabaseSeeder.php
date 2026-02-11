<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create test users
        $user1 = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $user2 = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);
        
        // Create some test posts
        Post::create([
            'title' => 'First Post',
            'content' => 'This is the content of the first post.',
            'is_published' => true,
            'user_id' => $user1->id,
        ]);
        
        Post::create([
            'title' => 'Second Post',
            'content' => 'Content for the second post goes here.',
            'is_published' => false,
            'user_id' => $user1->id,
        ]);
        
        Post::create([
            'title' => 'Third Post',
            'content' => 'Another post content example.',
            'is_published' => true,
            'user_id' => $user2->id,
        ]);
        
        Post::create([
            'title' => 'Fourth Post',
            'content' => 'More content for testing revisions.',
            'is_published' => true,
            'user_id' => $user2->id,
        ]);
    }
}