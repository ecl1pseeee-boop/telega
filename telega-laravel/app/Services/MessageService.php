<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class MessageService
{
    public function store(array $validated, Chat $chat): Message
    {
        try {
            $message = DB::transaction(function () use ($validated, $chat) {
                $message = Message::create([
                    'body' => $validated['body'],
                    'chat_id' => $chat->id,
                    'user_id' => auth()->id(),
                ]);

                return $message;
            });

            Log::info('Сообщение создано успешно:', [
                'chat_id' => $chat->id,
                'message_id' => $message->id,
                'user_id' => auth()->id(),
                'body' => $message->body,
            ]);

            $this->publishMessage(action: 'message.created', message: $message);

            return $message;
        } catch (\Exception $e) {
            Log::error('Ошибка создания сообщения в чате: ', [
                'chat_id' => $chat->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(array $validated, Message $message): Message
    {
        try {
            $message = DB::transaction(function () use ($validated, $message) {
                $message->update([
                    'body' => $validated['body'],
                ]);

                return $message;
            });

            Log::info('Сообщение обновлено успешно:', [
                'message_id' => $message->id,
                'chat_id' => $message->chat_id,
                'user_id' => auth()->id(),
                'title' => $message->title,
            ]);

            $this->publishMessage(action: 'message.updated', message: $message);

        } catch (\Exception $e) {
            Log::error('Ошибка обновления сообщения: ', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        return $message;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message): void
    {
        try {
            $old_message_id = $message->id;
            DB::transaction(function () use ($message) {
                $message->delete();
            });

            Log::info('Сообщение успешно удалено:', [
                'old_message_id' => $old_message_id,
                'user_id' => auth()->id(),
            ]);

            $this->publishMessage(action: 'message.deleted', message: $message);
        } catch (\Exception $e) {
            Log::error('Ошибка удаления сообщения: ', [
                'chat_id' => $message->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function publishMessage(string $action, Message $message): void {
        if($action === 'message.created' || $action === 'message.updated') {
            Redis::publish('chat_channel', json_encode([
                'action' => $action,
                'data' => [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'user_id' => $message->user_id,
                    'body' => $message->body,
                    'author' => auth()->user()->name,
                    'created_at' => $message->created_at->toDateTimeString(),
                    'updated_at' => $message->updated_at->toDateTimeString(),
                ]
            ]));
        } elseif ($action === 'message.deleted') {
            Redis::publish('chat_channel', json_encode([
                'action' => 'message.deleted',
                'data' => [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                ]
            ]));
        }
    }
}
