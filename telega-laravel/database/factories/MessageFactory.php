<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_id' => Chat::factory(),
            'user_id' => User::factory(),
            'body' => fake()->sentence(4),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function userMessage(User $user, Chat $chat): static {
        return $this->state(fn ($attributes) => [
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => fake()->sentence(4),
        ]);
    }
}
