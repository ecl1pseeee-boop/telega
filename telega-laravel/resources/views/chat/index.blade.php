<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Мои чаты</h2>
            <a href="{{ route('chat.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm transition">
                + Создать чат
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 text-gray-900">
                    @forelse ($userChats as $chat)
                        <div class="flex items-center justify-between p-4 hover:bg-gray-50 border-b last:border-0 transition">
                            <div>
                                <a href="{{ route('chat.show', $chat) }}" class="text-lg font-medium text-indigo-600 hover:underline">
                                    {{ $chat->title ?? 'Без названия' }}
                                </a>
                                <p class="text-sm text-gray-500">Создан: {{ $chat->created_at->format('d.m.Y H:i') }}</p>
                            </div>
                            <div class="flex space-x-3">
                                <a href="{{ route('chat.edit', $chat) }}" class="text-gray-600 hover:text-indigo-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('chat.destroy', $chat) }}" method="POST" onsubmit="return confirm('Удалить этот чат?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-10">У вас пока нет активных чатов.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
