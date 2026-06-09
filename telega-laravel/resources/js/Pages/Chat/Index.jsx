import React, {useEffect, useState} from 'react';
import { Link, useForm, router} from '@inertiajs/react';
import ChatWindow from './Partials/ChatWindow';
import axios from "axios";

export default function Index({ chats, activeChat }) {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const { data, setData, post, reset } = useForm({ title: '', type: 'group', user_ids: [] });

    const [friends, setFriends] = useState([]);
    const [activeTab, setActiveTab] = useState('chats');
    const { post: createDirectChat } = useForm();

    useEffect(() => {
        axios.get(route('friends.possible')).then(res => setFriends(res.data.data));
    }, []);

    const createChat = (e) => {
        e.preventDefault();
        post(route('chat.store'), {
            onSuccess: () => {
                setIsModalOpen(false);
                reset();
            }
        });
    };

    const startDirectChat = (friendId) => {
        router.post(route('chat.store'), {
            title: 'Личный чат',
            type: 'direct',
            user_ids: [friendId]
        }, {
            onSuccess: () => setActiveTab('chats')
        });
    };

    return (
        <div className="flex h-screen overflow-hidden">
            {/* Левая панель */}
            <div className="w-1/3 border-r bg-gray-50 flex flex-col h-screen">
                {/* Заголовок */}
                <div className="p-4 border-b flex justify-between items-center">
                    <span className="font-bold">Мой мессенджер</span>
                    <button
                        onClick={() => setIsModalOpen(true)}
                        className="bg-blue-600 text-white px-3 py-1 rounded text-sm"
                    >
                        + Создать
                    </button>
                </div>

                {/* Переключатель вкладок */}
                <div className="flex border-b bg-white">
                    <button
                        onClick={() => setActiveTab('chats')}
                        className={`flex-1 py-2 ${activeTab === 'chats' ? 'border-b-2 border-blue-600 font-bold' : ''}`}
                    >
                        Чаты
                    </button>
                    <button
                        onClick={() => setActiveTab('friends')}
                        className={`flex-1 py-2 ${activeTab === 'friends' ? 'border-b-2 border-blue-600 font-bold' : ''}`}
                    >
                        Друзья
                    </button>
                </div>

                {/* Контент вкладки */}
                <div className="flex-1 overflow-y-auto">
                    {activeTab === 'chats' ? (
                        // Вкладка "Чаты"
                        chats.map(chat => (
                            <Link
                                key={chat.id}
                                href={route('chat.show', chat.id)}
                                className={`block p-4 border-b hover:bg-gray-100 ${activeChat?.id === chat.id ? 'bg-blue-100' : ''}`}
                            >
                                {chat.title}
                            </Link>
                        ))
                    ) : (
                        // Вкладка "Друзья"
                        friends.map(friend => (
                            <div key={friend.id} className="p-4 border-b flex justify-between items-center hover:bg-gray-100">
                                <span>{friend.name}</span>
                                <button
                                    onClick={() => startDirectChat(friend.id)}
                                    className="text-xs bg-green-500 text-white px-2 py-1 rounded"
                                >
                                    Написать
                                </button>
                            </div>
                        ))
                    )}
                </div>
            </div>

            {/* Модальное окно */}
            {isModalOpen && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                    <form onSubmit={createChat} className="bg-white p-6 rounded-lg w-96 shadow-xl">
                        <h2 className="text-xl mb-4">Новый чат</h2>
                        <input
                            placeholder="Название чата"
                            className="w-full border p-2 mb-4 rounded"
                            onChange={e => setData('title', e.target.value)}
                        />
                        <select className="w-full border p-2 mb-4 rounded" onChange={e => setData('type', e.target.value)}>
                            <option value="group">Группа</option>
                            <option value="direct">Личный</option>
                        </select>

                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Участники:</label>
                            <div className="max-h-40 overflow-y-auto border rounded p-2">
                                {Array.isArray(friends) && friends.length > 0 ? (
                                    friends.map(friend => (
                                    <label key={friend.id} className="flex items-center gap-2 py-1 cursor-pointer hover:bg-gray-50">
                                        <input
                                            type="checkbox"
                                            value={friend.id}
                                            className="rounded text-blue-600"
                                            onChange={(e) => {
                                                const userId = parseInt(e.target.value);
                                                const currentIds = data.user_ids;

                                                const newIds = e.target.checked
                                                    ? [...currentIds, userId] // Добавляем
                                                    : currentIds.filter(id => id !== userId); // Удаляем

                                                setData('user_ids', newIds);
                                            }}
                                        />
                                        <span className="text-sm">{friend.name}</span>
                                    </label>
                                ))) : (
                                    <p className="text-gray-500 text-sm p-2">ВозможныеДрузья не найдены</p>
                                )}
                            </div>
                        </div>

                        <div className="flex justify-end gap-2">
                            <button type="button" onClick={() => setIsModalOpen(false)} className="px-4 py-2 text-gray-600">Отмена</button>
                            <button type="submit" className="bg-blue-600 text-white px-4 py-2 rounded">Создать</button>
                        </div>
                    </form>
                </div>
            )}

            {/* Правая панель с ChatWindow */}
            <div className="w-2/3 flex flex-col">
                {activeChat ? <ChatWindow chat={activeChat} /> : <div className="flex-1 flex items-center justify-center">Выберите чат</div>}
            </div>
        </div>
    );
}
