<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private readonly MessageService $messageService
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Chat $chat)
    {
        $messages = Message::where('chat_id', $chat->id)->latest()->paginate(15);
        return response()->json($messages);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    public function store(StoreMessageRequest $request, Chat $chat)
    {
        $validated = $request->validated();

        try {
            $message = $this->messageService->store(
                validated: $validated,
                chat: $chat
            );
            return to_route('chat.show', $chat->id);
        } catch (\Throwable $e) {
            return to_route('chat.show', $chat->id);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        return response()->json($message);
    }

    /**
     * Update the specified resource in storage.
     * @throws \Exception
     */
    public function update(UpdateMessageRequest $request, Chat $chat, Message $message)
    {
        $validated = $request->validated();

        try {
            $message = $this->messageService->update(
                validated: $validated,
                message: $message,
            );

            return to_route('chat.show', $chat->id);
        } catch (\Throwable $e) {
            return to_route('chat.show', $chat->id);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @throws \Exception
     */
    public function destroy(Chat $chat, Message $message)
    {
        try {
            $this->messageService->destroy($message);
            return to_route('chat.show', $chat->id);
        } catch (\Throwable $e) {
            return to_route('chat.show', $chat->id);
        }
    }
}
