<?php
// database/seeders/PostSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Create 3 test users if they don't exist
        $users = User::factory()->count(3)->create();
        
        // Create posts for each user
        foreach ($users as $user) {
            Post::factory()->count(5)->create([
                'user_id' => $user->id,
            ]);
        }
    }
}