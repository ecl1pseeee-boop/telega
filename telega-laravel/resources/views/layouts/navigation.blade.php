<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('chat.index') }}" class="text-xl font-bold text-gray-800 hover:text-blue-600 transition">
                        Чат-приложение
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="{{ route('chat.index') }}"
                       class="{{ request()->routeIs('chat.index') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}
                       inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Мои чаты
                    </a>

                    <a href="{{ route('chat.create') }}"
                       class="{{ request()->routeIs('chat.create') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}
                       inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Создать чат
                    </a>
                </div>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-3">
                <span class="text-sm text-gray-600">
                    {{ auth()->user()->name ?? 'Гость' }}
                </span>

                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                            Выйти
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</nav>
