import React, { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import ChatWindow from './Partials/ChatWindow';

export default function Index({ chats, activeChat }) {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const { data, setData, post, reset } = useForm({ title: '', type: 'group', user_ids: [] });

    const createChat = (e) => {
        e.preventDefault();
        post(route('chat.store'), {
            onSuccess: () => {
                setIsModalOpen(false);
                reset();
            }
        });
    };

    return (
        <div className="flex h-screen overflow-hidden">
            {/* Левая панель */}
            <div className="w-1/3 border-r bg-gray-50 overflow-y-auto">
                <div className="p-4 border-b flex justify-between items-center">
                    <span className="font-bold">Мои чаты</span>
                    <button
                        onClick={() => setIsModalOpen(true)}
                        className="bg-blue-600 text-white px-3 py-1 rounded text-sm"
                    >
                        + Создать
                    </button>
                </div>
                {chats.map(chat => (
                    <Link
                        key={chat.id}
                        href={route('chat.show', chat.id)}
                        className={`block p-4 border-b hover:bg-gray-100 ${activeChat?.id === chat.id ? 'bg-blue-100' : ''}`}
                    >
                        {chat.title}
                    </Link>
                ))}
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
