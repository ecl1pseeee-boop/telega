<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public static function run(): void
    {
        Chat::factory(10)->create();
        Chat::factory(10)->groupChat()->create();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $chat = Chat::factory()->directChat($user1, $user2)->create();

        Message::factory()->userMessage($user1, $chat)->create();
        Message::factory()->userMessage($user2, $chat)->create();

    }
}
