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
use Inertia\Inertia;
use Inertia\Response;
use Inertia\ResponseFactory;

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
    public function index(): Response|ResponseFactory
    {
        return Inertia::render('Chat/Index', [
            'chats' => auth()->user()->chats,
            'activeChat' => '',
        ]);
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
        $chat = $this->chatService->store($request->validated());

        return to_route('chat.show', $chat->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Chat $chat): Response
    {
        return Inertia::render('Chat/Index', [
            'chats' => auth()->user()->chats,
            'activeChat' => $chat?->load('messages.user'),
        ]);
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
        return back();

    }

    /**
     * Remove the specified resource from storage.
     * @throws \Exception
     */
    public function destroy(Chat $chat): Redirector|RedirectResponse
    {
        $this->chatService->delete($chat);
        return to_route('chat.index');
    }
}
