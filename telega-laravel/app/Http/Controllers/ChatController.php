<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;
use App\Services\ChatService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    )
    {
        $this->authorizeResourceForModel(Chat::class, 'chat');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Factory|View
    {
        $userChats = auth()->user()->chats;
        return view('chat.index', compact('userChats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Factory|View
    {
        return view('chat.create');
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    public function store(StoreChatRequest $request): Redirector|RedirectResponse
    {
        $validated = $request->validated();

        $chat = $this->chatService->store(
            validated: $validated
        );

        return redirect(route('chat.show', $chat))->with('success', 'Чат успешно создан');
    }

    /**
     * Display the specified resource.
     */
    public function show(Chat $chat): Factory|View
    {
        return view('chat.show', compact('chat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chat $chat): Factory|View
    {
        return view('chat.edit', compact('chat'));
    }

    /**
     * Update the specified resource in storage.
     * @throws \Exception
     */
    public function update(UpdateChatRequest $request, Chat $chat): Redirector|RedirectResponse
    {
        $validated = $request->validated();
        $this->chatService->update(
            validated: $validated,
            chat: $chat
        );
        return redirect(route('chat.show', $chat))->with('success', 'Чат успешно обновлен');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chat $chat): Redirector|RedirectResponse
    {
        $this->chatService->delete($chat);
        return redirect(route('chat.index'))->with('success', 'Успешно удалено');
    }
}
