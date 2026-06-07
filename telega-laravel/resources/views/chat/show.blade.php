<x-app-layout>
    @vite(['resources/js/chat.js'])

    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('chat.index') }}" class="mr-4 text-gray-400 hover:text-gray-600">
                ← Назад
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $chat->title }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg h-[600px] flex flex-col"
                 id="chat-container"
                 data-chat-id="{{ $chat->id }}">

                <!-- Кнопка загрузки предыдущих сообщений -->
                <div class="p-2 text-center border-b" id="load-more-container">
                    <button id="load-more"
                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                        ↓ Загрузить предыдущие сообщения ↓
                    </button>
                </div>

                <!-- Окно сообщений -->
                <div id="messages-container" class="flex-1 p-6 overflow-y-auto bg-gray-50">
                    <!-- Сообщения будут загружены сюда через JS -->
                </div>

                <!-- Поле ввода -->
                <div class="p-4 border-t bg-white rounded-b-lg">
                    <form id="message-form" class="flex space-x-4">
                        <input type="text"
                               id="message-input"
                               placeholder="Введите сообщение..."
                               autocomplete="off"
                               class="flex-1 border-gray-300 focus:border-indigo-500 rounded-lg focus:ring focus:ring-indigo-200 px-4 py-2">
                        <button type="submit"
                                id="send-btn"
                                class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition duration-200">
                            Отправить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


