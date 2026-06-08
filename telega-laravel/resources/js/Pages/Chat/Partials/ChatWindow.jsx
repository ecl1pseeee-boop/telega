import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';

export default function ChatWindow({ chat }) {
    const { data, setData, post, reset } = useForm({ body: '' });

    // Состояние для редактирования
    const [editingId, setEditingId] = useState(null);
    const [editBody, setEditBody] = useState('');

    // Состояние для удаления (ID сообщения, которое хотим удалить)
    const [deleteId, setDeleteId] = useState(null);

    const sendMessage = (e) => {
        e.preventDefault();
        post(route('message.store', chat.id), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    const updateMessage = (id) => {
        router.put(route('message.update', { chat: chat.id, message: id }), { body: editBody }, {
            preserveScroll: true,
            onSuccess: () => setEditingId(null),
        });
    };

    const deleteMessage = () => {
        router.delete(route('message.destroy',{ chat: chat.id, message: deleteId }, {
            preserveScroll: true,
            onSuccess: () => setDeleteId(null),
        }));
    };

    return (
        <div className="flex flex-col h-full">
            <div className="p-4 border-b font-bold">{chat.title}</div>

            {/* Список сообщений */}
            <div className="flex-1 p-4 overflow-y-auto space-y-2">
                {chat.messages.map(msg => (
                    <div key={msg.id} className="bg-white p-3 rounded shadow-sm flex justify-between">
                        {editingId === msg.id ? (
                            <input
                                value={editBody}
                                onChange={e => setEditBody(e.target.value)}
                                className="border rounded px-2"
                            />
                        ) : <span>{msg.body}</span>}

                        <div className="flex gap-2">
                            {editingId === msg.id ? (
                                <button onClick={() => updateMessage(msg.id)} className="text-green-600">Сохранить</button>
                            ) : (
                                <button onClick={() => { setEditingId(msg.id); setEditBody(msg.body); }} className="text-blue-600">Ред.</button>
                            )}
                            <button onClick={() => setDeleteId(msg.id)} className="text-red-500">Удалить</button>
                        </div>
                    </div>
                ))}
            </div>

            {/* Модальное окно удаления */}
            {deleteId && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <div className="bg-white p-6 rounded shadow-lg">
                        <h2 className="mb-4">Вы уверены, что хотите удалить сообщение?</h2>
                        <div className="flex gap-4">
                            <button onClick={deleteMessage} className="bg-red-500 text-white px-4 py-2 rounded">Удалить</button>
                            <button onClick={() => setDeleteId(null)} className="bg-gray-300 px-4 py-2 rounded">Отмена</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Форма отправки */}
            <form onSubmit={sendMessage} className="p-4 border-t flex gap-2">
                <input
                    className="flex-1 border rounded p-2"
                    value={data.body}
                    onChange={e => setData('body', e.target.value)}
                />
                <button className="bg-blue-600 text-white px-4 py-2 rounded">Отправить</button>
            </form>
        </div>
    );
}
