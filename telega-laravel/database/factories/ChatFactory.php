<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chat>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['direct', 'group']),
            'title' => fake()->boolean(70) ? fake()->sentence(3) : null,
            'created_by' => User::factory(),
        ];
    }

    public function directChat(User $user1, User $user2): static {
        return $this->state([
            'type' => 'direct',
            'title' => fake()->sentence(3),
            'created_by' => $user1->id
        ])->afterCreating(function (Chat $chat) use ($user1, $user2) {
            $chat->members()->attach($user1->id, ['role' => 'owner']);
            $chat->members()->attach($user2->id, ['role' => 'member']);
        });
    }

    public function groupChat(): static {
        return $this->state(fn ($attributes) => [
            'type' => 'group',
            'title' => fake()->sentence(3)
        ]);
    }


}
