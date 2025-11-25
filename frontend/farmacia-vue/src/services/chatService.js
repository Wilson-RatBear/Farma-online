import api from './api';

export const chatService = {
    // Conversaciones
    getConversations() {
        return api.get('/chat/conversations');
    },

    createConversation(data) {
        return api.post('/chat/conversations', data);
    },

    getConversation(id) {
        return api.get(`/chat/conversations/${id}`);
    },

    // Mensajes
    sendMessage(data) {
        return api.post('/chat/messages', data);
    },

    getMessages(conversationId) {
        return api.get(`/chat/conversations/${conversationId}/messages`);
    },

    // Admin
    getAdminConversations() {
        return api.get('/admin/chat/conversations');
    },

    getUnreadConversations() {
        return api.get('/admin/chat/conversations/unread');
    },

    closeConversation(id) {
        return api.put(`/admin/chat/conversations/${id}/close`);
    },

    reopenConversation(id) {
        return api.put(`/admin/chat/conversations/${id}/reopen`);
    }
};