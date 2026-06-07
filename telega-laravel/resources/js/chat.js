// resources/js/chat.js

class ChatManager {
    constructor(chatId) {
        this.chatId = chatId;
        this.currentPage = 1;
        this.lastPage = 1;
        this.isLoading = false;
        this.init();
    }

    init() {
        this.cacheDom();
        this.bindEvents();
        this.loadMessages();
    }

    cacheDom() {
        this.messagesContainer = document.getElementById('messages-container');
        this.messageForm = document.getElementById('message-form');
        this.messageInput = document.getElementById('message-input');
        this.loadMoreBtn = document.getElementById('load-more');
        this.sendBtn = document.getElementById('send-btn');
    }

    bindEvents() {
        this.messageForm.addEventListener('submit', (e) => this.handleSubmit(e));
        if (this.loadMoreBtn) {
            this.loadMoreBtn.addEventListener('click', () => this.loadMoreMessages());
        }
    }

    async loadMessages(reset = false) {
        if (this.isLoading) return;

        this.isLoading = true;
        this.showLoading();

        try {
            // Адаптируем URL под вашу структуру: /chat/{chat}/message?page=1
            const response = await fetch(`/api/chat/${this.chatId}/message?page=${this.currentPage}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load messages');
            }

            const data = await response.json();

            this.lastPage = data.last_page || 1;

            if (reset) {
                this.messagesContainer.innerHTML = '';
                this.currentPage = 1;
                // Добавляем заголовок "Начало чата" после очистки
                this.addChatHeader();
            }

            this.renderMessages(data.data);
            this.updateLoadMoreButton();

            // Скроллим вниз только при загрузке новых сообщений (не при подгрузке старых)
            if (this.currentPage === this.lastPage || reset) {
                this.scrollToBottom();
            } else {
                this.preserveScrollPosition();
            }

        } catch (error) {
            console.error('Error loading messages:', error);
            this.showError('Не удалось загрузить сообщения');
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }

    async loadMoreMessages() {
        if (this.currentPage >= this.lastPage) return;

        this.currentPage++;
        await this.loadMessages();
    }

    renderMessages(messages) {
        // Сохраняем старую высоту для сохранения позиции скролла
        const oldHeight = this.messagesContainer.scrollHeight;

        messages.forEach(message => {
            // Проверяем, не добавлено ли уже это сообщение
            if (!document.querySelector(`.message-item[data-id="${message.id}"]`)) {
                const messageElement = this.createMessageElement(message);
                // Добавляем в начало контейнера для старых сообщений
                if (this.currentPage > 1) {
                    this.messagesContainer.insertBefore(messageElement, this.messagesContainer.firstChild);
                } else {
                    this.messagesContainer.appendChild(messageElement);
                }
            }
        });

        // Сохраняем позицию скролла при загрузке старых сообщений
        if (this.currentPage > 1) {
            const newHeight = this.messagesContainer.scrollHeight;
            this.messagesContainer.scrollTop = newHeight - oldHeight;
        }
    }

    createMessageElement(message) {
        const isOwnMessage = message.user_id === this.getCurrentUserId();
        const div = document.createElement('div');
        div.className = `flex ${isOwnMessage ? 'justify-end' : 'justify-start'} mb-4 message-item`;
        div.dataset.id = message.id;

        div.innerHTML = `
            <div class="${isOwnMessage ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800'} rounded-lg px-4 py-2 max-w-[70%] relative group">
                <div class="text-sm break-words">${this.escapeHtml(message.body)}</div>
                <div class="text-xs ${isOwnMessage ? 'text-indigo-200' : 'text-gray-500'} mt-1">
                    ${this.formatDate(message.created_at)}
                </div>
                ${isOwnMessage ? `
                    <div class="absolute -top-2 -right-2 hidden group-hover:flex space-x-1">
                        <button onclick="chatManager.editMessage(${message.id})" class="bg-white rounded-full p-1 shadow-md hover:bg-gray-100">
                            <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </button>
                        <button onclick="chatManager.deleteMessage(${message.id})" class="bg-white rounded-full p-1 shadow-md hover:bg-gray-100">
                            <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                ` : ''}
            </div>
        `;

        return div;
    }

    async handleSubmit(e) {
        e.preventDefault();

        const content = this.messageInput.value.trim();
        if (!content) return;

        this.messageInput.disabled = true;
        this.sendBtn.disabled = true;
        this.sendBtn.textContent = 'Отправка...';

        try {
            // POST запрос на /chat/{chat}/message
            const response = await fetch(`/api/chat/${this.chatId}/message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ body: content })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Failed to send message');
            }

            const data = await response.json();

            // Если ответ содержит объект с ключом 'message' (как у вас в контроллере)
            const newMessage = data.message || data;

            // Добавляем новое сообщение
            const messageElement = this.createMessageElement(newMessage);
            this.messagesContainer.appendChild(messageElement);

            // Очищаем поле ввода
            this.messageInput.value = '';

            // Скроллим вниз
            this.scrollToBottom();

            // Если мы не на последней странице, перезагружаем, чтобы обновить пагинацию
            if (this.currentPage !== this.lastPage) {
                this.currentPage = this.lastPage;
                await this.loadMessages(true);
            }

        } catch (error) {
            console.error('Error sending message:', error);
            this.showError(error.message || 'Не удалось отправить сообщение');
        } finally {
            this.messageInput.disabled = false;
            this.sendBtn.disabled = false;
            this.sendBtn.textContent = 'Отправить';
            this.messageInput.focus();
        }
    }

    async editMessage(messageId) {
        const messageElement = document.querySelector(`.message-item[data-id="${messageId}"]`);
        const contentDiv = messageElement.querySelector('.text-sm');
        const oldContent = contentDiv.textContent;

        const newContent = prompt('Редактировать сообщение:', oldContent);
        if (!newContent || newContent === oldContent) return;

        try {
            const response = await fetch(`/api/chat/${this.chatId}/message/${messageId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ body: newContent })
            });

            if (response.ok) {
                const data = await response.json();
                const updatedMessage = data.message || data;
                contentDiv.textContent = this.escapeHtml(updatedMessage.body);
                this.showSuccess('Сообщение обновлено');
            } else {
                throw new Error('Failed to update');
            }
        } catch (error) {
            console.error('Error editing message:', error);
            this.showError('Не удалось обновить сообщение');
        }
    }

    async deleteMessage(messageId) {
        if (!confirm('Удалить это сообщение?')) return;

        try {
            const response = await fetch(`/api/chat/${this.chatId}/message/${messageId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const messageElement = document.querySelector(`.message-item[data-id="${messageId}"]`);
                messageElement?.remove();
                this.showSuccess('Сообщение удалено');
            } else {
                throw new Error('Failed to delete');
            }
        } catch (error) {
            console.error('Error deleting message:', error);
            this.showError('Не удалось удалить сообщение');
        }
    }

    addChatHeader() {
        const header = document.createElement('div');
        header.className = 'text-center text-gray-400 text-sm mb-4';
        header.textContent = 'Начало истории чата';
        this.messagesContainer.appendChild(header);
    }

    getCurrentUserId() {
        return parseInt(document.querySelector('meta[name="user-id"]')?.content || '0');
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;

        // Если сегодня
        if (diff < 86400000 && date.getDate() === now.getDate()) {
            return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
        }

        // Если вчера
        const yesterday = new Date(now);
        yesterday.setDate(yesterday.getDate() - 1);
        if (date.getDate() === yesterday.getDate()) {
            return `Вчера, ${date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })}`;
        }

        // Иначе полная дата
        return date.toLocaleString('ru-RU', {
            day: '2-digit',
            month: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    scrollToBottom() {
        setTimeout(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }, 100);
    }

    preserveScrollPosition() {
        // Сохраняем позицию скролла при загрузке старых сообщений
        const oldScrollTop = this.messagesContainer.scrollTop;
        setTimeout(() => {
            this.messagesContainer.scrollTop = oldScrollTop;
        }, 50);
    }

    updateLoadMoreButton() {
        if (this.loadMoreBtn) {
            if (this.currentPage >= this.lastPage) {
                this.loadMoreBtn.style.display = 'none';
            } else {
                this.loadMoreBtn.style.display = 'block';
            }
        }
    }

    showLoading() {
        if (this.loadMoreBtn && this.currentPage > 1) {
            this.loadMoreBtn.textContent = 'Загрузка...';
            this.loadMoreBtn.disabled = true;
        }
    }

    hideLoading() {
        if (this.loadMoreBtn) {
            this.loadMoreBtn.textContent = 'Загрузить предыдущие сообщения';
            this.loadMoreBtn.disabled = false;
        }
    }

    showError(message) {
        this.showToast(message, 'error');
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 ${type === 'error' ? 'bg-red-500' : 'bg-green-500'} text-white px-4 py-2 rounded shadow-lg z-50 transition-opacity duration-300`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Глобальная переменная для доступа к методам из onclick
let chatManager;

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.getElementById('chat-container');
    if (chatContainer) {
        const chatId = chatContainer.dataset.chatId;
        chatManager = new ChatManager(chatId);

        // Делаем глобально доступным для onclick
        window.chatManager = chatManager;
    }
});
