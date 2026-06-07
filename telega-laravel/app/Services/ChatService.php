<?php

namespace App\Services;

use App\Models\Chat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function store(array $validated): Chat {
        try {
            $chat = DB::transaction(function () use ($validated) {
                $chat = Chat::create([
                    'title' => $validated['title'],
                    'type' => $validated['type'],
                    'created_by' => auth()->id()
                ]);

                $chat->members()->attach(auth()->id());

                return $chat;
            });

            Log::info('Чат создан успешно:', [
                'chat_id' => $chat->id,
                'user_id' => auth()->id(),
                'title' => $chat->title,
            ]);
            return $chat;
        }
        catch (\Exception $e) {
            Log::error('Ошибка создания чата: ', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function update(array $validated, Chat $chat): Chat {
        try {
            $chat = DB::transaction(function () use ($validated, $chat) {
                $chat->update([
                    'title' => $validated['title'] ?? $chat->title,
                ]);

                return $chat;
            });

            Log::info('Чат успешно обновлен:', [
                'chat_id' => $chat->id,
                'user_id' => auth()->id(),
                'title' => $chat->title,
            ]);

            return $chat->fresh();
        }
        catch (\Exception $e) {
            Log::error('Ошибка обновления чата: ', [
                'chat_id' => $chat->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function delete(Chat $chat): void {
        try {
            $old_chat_id = $chat->id;
            DB::transaction(function () use ($chat) {
                $chat->delete();
                $chat->members()->detach();
                $chat->messages()->delete();
            });

            Log::info('Чат успешно удален:', [
                'old_chat_id' => $old_chat_id,
                'user_id' => auth()->id(),
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка удаления чата: ', [
                'chat_id' => $chat->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
