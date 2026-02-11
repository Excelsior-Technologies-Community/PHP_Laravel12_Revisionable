<?php
// database/factories/PostFactory.php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'is_published' => $this->faker->boolean(70), // 70% chance of being published
            'user_id' => User::factory(), // This will create a user if not specified
        ];
    }
}