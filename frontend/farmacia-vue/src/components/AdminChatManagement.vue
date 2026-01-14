<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="admin-chat-management" @click.stop>
      <!-- Header Mejorado -->
      <div class="admin-chat-header">
        <div class="header-left">
          <button class="back-btn" @click="$emit('close')">
            <i class="fas fa-arrow-left"></i>
          </button>
          <h2><i class="fas fa-headset"></i> Centro de Soporte</h2>
        </div>
        <div class="header-stats">
          <span class="stat-item">
            <i class="fas fa-comments"></i>
            {{ conversations.length }} Conversaciones
          </span>
          <span class="stat-item unread" v-if="unreadCount > 0">
            <i class="fas fa-bell"></i>
            {{ unreadCount }} Sin leer
          </span>
        </div>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Contenido principal -->
      <div class="admin-chat-content">
        <!-- Barra lateral de conversaciones -->
        <div class="conversations-sidebar">
          <div class="sidebar-header">
            <h3>Conversaciones</h3>
            <div class="filters">
              <select v-model="filterStatus" @change="filterConversations" class="filter-select">
                <option value="all">Todas</option>
                <option value="open">Abiertas</option>
                <option value="closed">Cerradas</option>
                <option value="unread">Sin leer</option>
              </select>
              <div class="search-box">
                <input 
                  v-model="searchQuery" 
                  @input="filterConversations"
                  placeholder="Buscar..."
                />
                <i class="fas fa-search"></i>
              </div>
            </div>
          </div>

          <div class="conversations-list">
            <div v-if="loading" class="loading">
              <i class="fas fa-spinner fa-spin"></i> Cargando...
            </div>

            <div v-else-if="filteredConversations.length === 0" class="empty-state">
              <i class="fas fa-comments-slash"></i>
              <p>No hay conversaciones</p>
            </div>

            <div v-else class="conversations">
              <div 
                v-for="conversation in filteredConversations" 
                :key="conversation.id"
                class="conversation-item"
                :class="{ 
                  unread: hasUnreadMessages(conversation),
                  active: selectedConversation?.id === conversation.id 
                }"
                @click="selectConversation(conversation)"
              >
                <div class="conversation-avatar">
                  {{ getUserInitials(conversation.user) }}
                </div>
                <div class="conversation-info">
                  <div class="conversation-header">
                    <h4>{{ conversation.user.name }}</h4>
                    <span class="date">{{ formatDate(conversation.last_message_at) }}</span>
                  </div>
                  <p class="subject">{{ conversation.subject }}</p>
                  <p class="last-message">{{ getLastMessagePreview(conversation) }}</p>
                  <div class="conversation-footer">
                    <span class="status" :class="conversation.is_closed ? 'closed' : 'open'">
                      {{ conversation.is_closed ? 'Cerrado' : 'Abierto' }}
                    </span>
                    <span class="message-count">
                      <i class="fas fa-envelope"></i>
                      {{ conversation.messages_count || 0 }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel de conversación -->
        <div class="conversation-panel" v-if="selectedConversation">
          <div class="conversation-header">
            <div class="conversation-info">
              <h3>{{ selectedConversation.subject }}</h3>
              <p>Con {{ selectedConversation.user.name }} • {{ selectedConversation.user.email }}</p>
            </div>
            <div class="conversation-actions">
              <button 
                v-if="selectedConversation.is_closed"
                @click="reopenConversation(selectedConversation.id)"
                class="btn btn-success"
              >
                <i class="fas fa-lock-open"></i> Reabrir
              </button>
              <button 
                v-else
                @click="closeConversation(selectedConversation.id)"
                class="btn btn-warning"
              >
                <i class="fas fa-lock"></i> Cerrar
              </button>
            </div>
          </div>

          <div class="messages-container" ref="messagesContainer">
            <div v-if="messagesLoading" class="loading">Cargando mensajes...</div>
            
            <div v-else class="messages">
              <div 
                v-for="message in messages" 
                :key="message.id"
                class="message"
                :class="{ 
                  'user-message': message.user_id === selectedConversation.user_id,
                  'admin-message': message.user_id !== selectedConversation.user_id 
                }"
              >
                <div class="message-avatar">
                  {{ getUserInitials(message.user) }}
                </div>
                <div class="message-content">
                  <div class="message-header">
                    <strong>{{ message.user.name }}</strong>
                    <span class="message-time">{{ formatTime(message.created_at) }}</span>
                  </div>
                  <div class="message-bubble">
                    <p>{{ message.message }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="message-input" v-if="!selectedConversation.is_closed">
            <textarea 
              v-model="newMessage" 
              @keypress.ctrl.enter="sendMessageAsAdmin"
              placeholder="Escribe tu respuesta... (Ctrl+Enter para enviar)"
              rows="3"
              :disabled="sendingMessage"
            ></textarea>
            <button 
              @click="sendMessageAsAdmin" 
              :disabled="!newMessage.trim() || sendingMessage"
              class="send-btn btn-primary"
            >
              <i class="fas fa-paper-plane"></i> Enviar
            </button>
          </div>
        </div>

        <!-- Estado vacío -->
        <div v-else class="empty-conversation">
          <div class="empty-content">
            <i class="fas fa-comments"></i>
            <h3>Centro de Soporte</h3>
            <p>Selecciona una conversación para comenzar a chatear</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { chatService } from '../services/chatService';

export default {
  name: 'AdminChatManagement',
  data() {
    return {
      conversations: [],
      filteredConversations: [],
      selectedConversation: null,
      messages: [],
      loading: false,
      messagesLoading: false,
      sendingMessage: false,
      filterStatus: 'all',
      searchQuery: '',
      newMessage: '',
      unreadCount: 0
    }
  },
  computed: {
    getEmptyStateMessage() {
      switch (this.filterStatus) {
        case 'open': return 'abiertas';
        case 'closed': return 'cerradas';
        case 'unread': return 'sin leer';
        default: return '';
      }
    }
  },
  methods: {
    async loadConversations() {
      this.loading = true;
      try {
        const [conversationsResponse, unreadResponse] = await Promise.all([
          chatService.getAdminConversations(),
          chatService.getUnreadConversations()
        ]);
        
        this.conversations = conversationsResponse.data.conversations;
        this.filteredConversations = this.conversations;
        this.unreadCount = unreadResponse.data.unread_count;
      } catch (error) {
        console.error('Error loading conversations:', error);
      } finally {
        this.loading = false;
      }
    },

    filterConversations() {
      let filtered = this.conversations;

      // Filtrar por estado
      if (this.filterStatus === 'open') {
        filtered = filtered.filter(c => !c.is_closed);
      } else if (this.filterStatus === 'closed') {
        filtered = filtered.filter(c => c.is_closed);
      } else if (this.filterStatus === 'unread') {
        filtered = filtered.filter(this.hasUnreadMessages);
      }

      // Filtrar por búsqueda
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(c => 
          c.user.name.toLowerCase().includes(query) ||
          c.user.email.toLowerCase().includes(query) ||
          c.subject.toLowerCase().includes(query)
        );
      }

      this.filteredConversations = filtered;
    },

    async selectConversation(conversation) {
      this.selectedConversation = conversation;
      this.messagesLoading = true;
      
      try {
        const response = await chatService.getMessages(conversation.id);
        this.messages = response.data.messages;
        
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      } catch (error) {
        console.error('Error loading messages:', error);
      } finally {
        this.messagesLoading = false;
      }
    },

    async sendMessageAsAdmin() {
      if (!this.newMessage.trim() || this.sendingMessage) return;

      this.sendingMessage = true;
      try {
        const response = await chatService.sendMessage({
          conversation_id: this.selectedConversation.id,
          message: this.newMessage
        });

        this.messages.push(response.data.message);
        this.newMessage = '';
        
        // Recargar lista para actualizar contadores
        await this.loadConversations();
        
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      } catch (error) {
        console.error('Error sending message:', error);
      } finally {
        this.sendingMessage = false;
      }
    },

    async closeConversation(id) {
      try {
        await chatService.closeConversation(id);
        await this.loadConversations();
        
        if (this.selectedConversation?.id === id) {
          this.selectedConversation.is_closed = true;
        }
      } catch (error) {
        console.error('Error closing conversation:', error);
      }
    },

    async reopenConversation(id) {
      try {
        await chatService.reopenConversation(id);
        await this.loadConversations();
        
        if (this.selectedConversation?.id === id) {
          this.selectedConversation.is_closed = false;
        }
      } catch (error) {
        console.error('Error reopening conversation:', error);
      }
    },

    hasUnreadMessages(conversation) {
      return conversation.latest_message && 
             !conversation.latest_message.is_read && 
             conversation.latest_message.user_id === conversation.user_id;
    },

    getLastMessagePreview(conversation) {
      if (!conversation.latest_message) return 'No hay mensajes';
      const message = conversation.latest_message.message;
      return message.length > 80 ? message.substring(0, 80) + '...' : message;
    },

    getUserInitials(user) {
      return user.name.split(' ').map(n => n[0]).join('').toUpperCase();
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('es-ES');
    },

    formatTime(dateString) {
      return new Date(dateString).toLocaleTimeString('es-ES', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
    },

    scrollToBottom() {
      const container = this.$refs.messagesContainer;
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    }
  },
  async mounted() {
    await this.loadConversations();
  }
}
</script>

<style scoped>
.admin-chat-management {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 95%;
  max-width: 1400px;
  height: 85vh;
  background: white;
  border-radius: 16px;
  box-shadow: 0 25px 80px rgba(0,0,0,0.3);
  z-index: 1000;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid #e1e5e9;
}

/* Header mejorado */
.admin-chat-header {
  background: linear-gradient(135deg, #1e88e5 0%, #0d47a1 100%);
  color: white;
  padding: 20px 25px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-radius: 16px 16px 0 0;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 15px;
}

.admin-chat-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 12px;
}

.back-btn, .close-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  font-size: 1.1rem;
}

.back-btn:hover, .close-btn:hover {
  background: rgba(255,255,255,0.3);
  transform: scale(1.05);
}

.header-stats {
  display: flex;
  gap: 20px;
}

.stat-item {
  background: rgba(255,255,255,0.2);
  padding: 8px 15px;
  border-radius: 8px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 8px;
  backdrop-filter: blur(10px);
}

.stat-item.unread {
  background: rgba(255, 71, 87, 0.3);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

/* Contenido principal */
.admin-chat-content {
  flex: 1;
  display: flex;
  overflow: hidden;
  background: #f8fafc;
}

/* Barra lateral de conversaciones */
.conversations-sidebar {
  width: 400px;
  background: white;
  border-right: 1px solid #e1e5e9;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid #e1e5e9;
  background: #f8fafc;
}

.sidebar-header h3 {
  margin: 0 0 15px 0;
  color: #1e293b;
  font-size: 1.2rem;
}

.filters {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: white;
  font-size: 0.9rem;
}

.search-box {
  position: relative;
}

.search-box input {
  width: 100%;
  padding: 10px 35px 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
}

.search-box i {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
}

.conversations-list {
  flex: 1;
  overflow-y: auto;
  padding: 10px;
}

.loading {
  text-align: center;
  padding: 30px;
  color: #6b7280;
  font-size: 0.9rem;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #9ca3af;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 15px;
  opacity: 0.5;
}

.conversations {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.conversation-item {
  display: flex;
  gap: 12px;
  padding: 15px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: white;
  border: 1px solid #f1f5f9;
}

.conversation-item:hover {
  background: #f8fafc;
  border-color: #e2e8f0;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.conversation-item.unread {
  border-left: 4px solid #1e88e5;
  background: #f0f7ff;
}

.conversation-item.active {
  background: #1e88e5;
  color: white;
  border-color: #1e88e5;
}

.conversation-item.active .subject,
.conversation-item.active .last-message,
.conversation-item.active .date {
  color: white;
}

.conversation-avatar {
  width: 45px;
  height: 45px;
  background: #1e88e5;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.conversation-item.active .conversation-avatar {
  background: white;
  color: #1e88e5;
}

.conversation-info {
  flex: 1;
  min-width: 0;
}

.conversation-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 5px;
}

.conversation-header h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

.date {
  font-size: 0.75rem;
  color: #6b7280;
  white-space: nowrap;
}

.subject {
  margin: 0 0 5px 0;
  font-weight: 500;
  color: #1e293b;
  font-size: 0.9rem;
}

.last-message {
  margin: 0 0 8px 0;
  color: #6b7280;
  font-size: 0.85rem;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
-webkit-line-clamp: 2;
-webkit-box-orient: vertical;
line-clamp: 2;
}

.conversation-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status {
  padding: 3px 8px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: bold;
  text-transform: uppercase;
}

.status.open {
  background: #dcfce7;
  color: #166534;
}

.status.closed {
  background: #fecaca;
  color: #991b1b;
}

.conversation-item.active .status.open {
  background: rgba(255,255,255,0.3);
  color: white;
}

.message-count {
  font-size: 0.75rem;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Panel de conversación */
.conversation-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
}

.conversation-header {
  padding: 20px 25px;
  border-bottom: 1px solid #e1e5e9;
  background: #f8fafc;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.conversation-info h3 {
  margin: 0 0 5px 0;
  color: #1e293b;
  font-size: 1.3rem;
}

.conversation-info p {
  margin: 0;
  color: #6b7280;
  font-size: 0.9rem;
}

.conversation-actions {
  display: flex;
  gap: 10px;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.3s ease;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover {
  background: #d97706;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: #f8fafc;
}

.messages {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.message {
  display: flex;
  gap: 12px;
  max-width: 70%;
}

.message.user-message {
  align-self: flex-start;
}

.message.admin-message {
  align-self: flex-end;
  flex-direction: row-reverse;
}

.message-avatar {
  width: 40px;
  height: 40px;
  background: #1e88e5;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.8rem;
  flex-shrink: 0;
}

.message.admin-message .message-avatar {
  background: #10b981;
}

.message-content {
  flex: 1;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.message-header strong {
  font-size: 0.9rem;
  color: #374151;
}

.message-time {
  font-size: 0.75rem;
  color: #9ca3af;
}

.message-bubble {
  background: white;
  padding: 12px 16px;
  border-radius: 18px;
  border-bottom-left-radius: 4px;
  line-height: 1.4;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  border: 1px solid #f1f5f9;
}

.message.admin-message .message-bubble {
  background: #1e88e5;
  color: white;
  border-bottom-left-radius: 18px;
  border-bottom-right-radius: 4px;
  border: none;
}

.message-input {
  padding: 20px;
  border-top: 1px solid #e1e5e9;
  background: white;
}

.message-input textarea {
  width: 100%;
  padding: 15px;
  border: 1px solid #d1d5db;
  border-radius: 12px;
  resize: vertical;
  margin-bottom: 15px;
  font-family: inherit;
  font-size: 0.95rem;
  transition: border-color 0.3s ease;
}

.message-input textarea:focus {
  border-color: #1e88e5;
  outline: none;
  box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
}

.send-btn {
  width: 100%;
  padding: 12px;
  background: #1e88e5;
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.send-btn:hover:not(:disabled) {
  background: #0d47a1;
  transform: translateY(-1px);
}

.send-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
  transform: none;
}

/* Estado vacío */
.empty-conversation {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
}

.empty-content {
  text-align: center;
  color: #6b7280;
}

.empty-content i {
  font-size: 4rem;
  margin-bottom: 20px;
  opacity: 0.3;
}

.empty-content h3 {
  margin: 0 0 10px 0;
  color: #374151;
  font-size: 1.5rem;
}

.empty-content p {
  margin: 0;
  font-size: 1rem;
}

/* Responsive */
@media (max-width: 1200px) {
  .admin-chat-management {
    width: 98%;
    height: 90vh;
  }
  
  .conversations-sidebar {
    width: 350px;
  }
}

@media (max-width: 768px) {
  .admin-chat-management {
    width: 100%;
    height: 100vh;
    border-radius: 0;
  }
  
  .admin-chat-header {
    border-radius: 0;
  }
  
  .conversations-sidebar {
    width: 100%;
  }
  
  .conversation-panel {
    display: none;
  }
  
  .conversation-item.active + .conversation-panel {
    display: flex;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1001;
  }
}
</style>