<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Создать новый чат</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 shadow-sm sm:rounded-lg">
                <form action="{{ route('chat.store') }}" method="POST">
                    @csrf

                    <!-- Название чата -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Название чата</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" required
                               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <!-- Тип чата (Новое поле) -->
                    <div class="mt-4">
                        <label for="type" class="block text-sm font-medium text-gray-700">Тип чата</label>
                        <select id="type" name="type" required
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="" disabled {{ old('type') ? '' : 'selected' }}>Выберите тип...</option>
                            @foreach(\App\Models\Chat::TYPES as $type)
                                <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex items-center justify-end">
                        <a href="{{ route('chat.index') }}" class="text-sm text-gray-600 hover:underline mr-4">Отмена</a>
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                            Создать
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
