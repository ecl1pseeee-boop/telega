import React, {useState, useEffect } from 'react';
import {useForm, usePage} from '@inertiajs/react';


export default function Show({ chat, messages : initialMessages }) {
    const {data, setData, post, processing} = useForm({
        body: ''
    });

    const [messages, setMessages] = useState(initialMessages);
    const [input, setInput] = useState('');
    const { auth } = usePage().props;

    useEffect((e) => {

    }, []);
    const sendMessage = async (e) => {
        e.preventDefault();

        const response = await fetch(`/api/chat/${chat.id}/message`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                body: input
            })
        });

        if(response.ok) setInput('');
    }

    return (
        <div className="flex flex-col h-screen bg-[#E5DDD5]">
            {/* Header */}
            <div className="bg-[#075E54] text-white p-4 font-bold">{chat.title}</div>

            {/* Messages */}
            <div className="flex-1 overflow-y-auto p-4 space-y-2">
                {messages.map((msg) => (
                    <div key={msg.id} className={`flex ${msg.user_id === auth.user.id ? 'justify-end' : 'justify-start'}`}>
                        <div className={`p-2 rounded-lg ${msg.user_id === auth.user.id ? 'bg-[#DCF8C6]' : 'bg-white'}`}>
                            {msg.body}
                        </div>
                    </div>
                ))}
            </div>

            {/* Input */}
            <form onSubmit={sendMessage} className="p-4 bg-gray-100 flex">
                <input
                    className="flex-1 border rounded p-2"
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                />
                <button className="ml-2 bg-blue-500 text-white p-2 rounded">➤</button>
            </form>
        </div>
    );
}
