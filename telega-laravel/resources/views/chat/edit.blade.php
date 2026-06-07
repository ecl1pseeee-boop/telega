<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight underline decoration-indigo-500">Настройки чата: {{ $chat->title }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 shadow-sm sm:rounded-lg">
                <form action="{{ route('chat.update', $chat) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Изменить название</label>
                        <input type="text" name="title" value="{{ $chat->title }}" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                            Сохранить изменения
                        </button>
                        <a href="{{ route('chat.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Вернуться к списку</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
