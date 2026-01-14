<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="chat-support" @click.stop>
      <!-- Header -->
      <div class="chat-header">
        <button class="back-btn" @click="$emit('close')">
          <i class="fas fa-arrow-left"></i>
        </button>
        <h2><i class="fas fa-comments"></i> Soporte al Cliente</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Contenido principal -->
      <div class="chat-content">
        <!-- Lista de conversaciones -->
        <div class="conversations-list" v-if="!selectedConversation">
          <div class="section-header">
            <h3>Mis Conversaciones</h3>
            <button class="new-chat-btn" @click="showNewChat = true">
              <i class="fas fa-plus"></i> Nueva Conversación
            </button>
          </div>

          <div v-if="loading" class="loading">Cargando conversaciones...</div>
          
          <div v-else-if="conversations.length === 0" class="empty-state">
            <i class="fas fa-comments"></i>
            <p>No tienes conversaciones activas</p>
            <button class="btn-primary" @click="showNewChat = true">
              Iniciar Chat de Soporte
            </button>
          </div>

          <div v-else class="conversations">
            <div 
              v-for="conversation in conversations" 
              :key="conversation.id"
              class="conversation-item"
              :class="{ unread: hasUnreadMessages(conversation) }"
              @click="selectConversation(conversation)"
            >
              <div class="conversation-info">
                <h4>{{ conversation.subject }}</h4>
                <p class="last-message">
                  {{ getLastMessagePreview(conversation) }}
                </p>
                <span class="date">
                  {{ formatDate(conversation.last_message_at) }}
                </span>
              </div>
              <div class="conversation-status">
                <span v-if="conversation.is_closed" class="status closed">Cerrado</span>
                <span v-else class="status open">Abierto</span>
                <i class="fas fa-chevron-right"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Vista de conversación individual -->
        <div class="conversation-view" v-else>
          <div class="conversation-header">
            <button class="back-btn" @click="selectedConversation = null">
              <i class="fas fa-arrow-left"></i>
            </button>
            <h3>{{ selectedConversation.subject }}</h3>
            <span class="status" :class="selectedConversation.is_closed ? 'closed' : 'open'">
              {{ selectedConversation.is_closed ? 'Cerrado' : 'Abierto' }}
            </span>
          </div>

          <div class="messages-container" ref="messagesContainer">
            <div v-if="messagesLoading" class="loading">Cargando mensajes...</div>
            
            <div v-else class="messages">
              <div 
                v-for="message in messages" 
                :key="message.id"
                class="message"
                :class="{ 'own-message': message.user_id === currentUser.id }"
              >
                <div class="message-bubble">
                  <p>{{ message.message }}</p>
                  <span class="message-time">
                    {{ formatTime(message.created_at) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="message-input" v-if="!selectedConversation.is_closed">
            <input 
              v-model="newMessage" 
              @keypress.enter="sendMessage"
              placeholder="Escribe tu mensaje..."
              :disabled="sendingMessage"
            />
            <button 
              @click="sendMessage" 
              :disabled="!newMessage.trim() || sendingMessage"
              class="send-btn"
            >
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </div>

        <!-- Modal para nueva conversación -->
        <div v-if="showNewChat" class="modal-overlay inner-overlay" @click="showNewChat = false">
          <div class="new-chat-modal" @click.stop>
            <div class="modal-header">
              <h3>Nueva Conversación</h3>
              <button @click="showNewChat = false" class="close-btn">
                <i class="fas fa-times"></i>
              </button>
            </div>
            
            <div class="modal-content">
              <input 
                v-model="newConversation.subject"
                placeholder="Asunto de la conversación"
                class="subject-input"
              />
              <textarea 
                v-model="newConversation.message"
                placeholder="Describe tu consulta o problema..."
                rows="4"
                class="message-textarea"
              ></textarea>
              
              <div class="modal-actions">
                <button @click="showNewChat = false" class="btn-secondary">
                  Cancelar
                </button>
                <button 
                  @click="createConversation" 
                  :disabled="!canCreateConversation"
                  class="btn-primary"
                >
                  Iniciar Conversación
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { chatService } from '../services/chatService';

export default {
  name: 'ChatSupport',
  props: {
    currentUser: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      conversations: [],
      selectedConversation: null,
      messages: [],
      loading: false,
      messagesLoading: false,
      sendingMessage: false,
      showNewChat: false,
      newMessage: '',
      newConversation: {
        subject: '',
        message: ''
      }
    }
  },
  computed: {
    canCreateConversation() {
      return this.newConversation.subject.trim() && this.newConversation.message.trim();
    }
  },
  methods: {
    async loadConversations() {
      this.loading = true;
      try {
        const response = await chatService.getConversations();
        this.conversations = response.data.conversations;
      } catch (error) {
        console.error('Error loading conversations:', error);
      } finally {
        this.loading = false;
      }
    },

    async selectConversation(conversation) {
      this.selectedConversation = conversation;
      this.messagesLoading = true;
      
      try {
        const response = await chatService.getMessages(conversation.id);
        this.messages = response.data.messages;
        
        // Scroll to bottom
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      } catch (error) {
        console.error('Error loading messages:', error);
      } finally {
        this.messagesLoading = false;
      }
    },

    async sendMessage() {
      if (!this.newMessage.trim() || this.sendingMessage) return;

      this.sendingMessage = true;
      try {
        const response = await chatService.sendMessage({
          conversation_id: this.selectedConversation.id,
          message: this.newMessage
        });

        this.messages.push(response.data.message);
        this.newMessage = '';
        
        // Update conversations list
        await this.loadConversations();
        
        // Scroll to bottom
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      } catch (error) {
        console.error('Error sending message:', error);
      } finally {
        this.sendingMessage = false;
      }
    },

    async createConversation() {
      try {
        const response = await chatService.createConversation(this.newConversation);
        this.conversations.unshift(response.data.conversation);
        this.showNewChat = false;
        this.newConversation = { subject: '', message: '' };
        
        // Auto-select the new conversation
        this.selectConversation(response.data.conversation);
      } catch (error) {
        console.error('Error creating conversation:', error);
      }
    },

    hasUnreadMessages(conversation) {
      return conversation.latest_message && 
             !conversation.latest_message.is_read && 
             conversation.latest_message.user_id !== this.currentUser.id;
    },

    getLastMessagePreview(conversation) {
      if (!conversation.latest_message) return 'No hay mensajes';
      const message = conversation.latest_message.message;
      return message.length > 50 ? message.substring(0, 50) + '...' : message;
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
.chat-support {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 90%;
  max-width: 500px;
  height: 70vh;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
  z-index: 1000;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-header {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  padding: 15px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-radius: 12px 12px 0 0;
}

.chat-header h2 {
  margin: 0;
  font-size: 1.3rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

.back-btn, .close-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.back-btn:hover, .close-btn:hover {
  background: rgba(255,255,255,0.3);
}

.chat-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.conversations-list {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h3 {
  margin: 0;
  color: #333;
}

.new-chat-btn {
  background: #1e88e5;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  transition: background 0.3s ease;
}

.new-chat-btn:hover {
  background: #0d47a1;
}

.loading {
  text-align: center;
  padding: 20px;
  color: #666;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 15px;
  color: #ccc;
}

.btn-primary {
  background: #1e88e5;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn-primary:hover {
  background: #0d47a1;
}

.conversations {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.conversation-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: white;
}

.conversation-item:hover {
  background: #f5f5f5;
  transform: translateX(5px);
}

.conversation-item.unread {
  border-left: 4px solid #1e88e5;
  background: #f8fdff;
}

.conversation-info h4 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 1rem;
}

.last-message {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

.date {
  font-size: 0.8rem;
  color: #999;
}

.conversation-status {
  display: flex;
  align-items: center;
  gap: 10px;
}

.status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: bold;
}

.status.open {
  background: #e8f5e8;
  color: #2e7d32;
}

.status.closed {
  background: #ffebee;
  color: #c62828;
}

/* Vista de conversación */
.conversation-view {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.conversation-header {
  padding: 15px 20px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  align-items: center;
  gap: 15px;
  background: #f8f9fa;
}

.conversation-header h3 {
  margin: 0;
  flex: 1;
  color: #333;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: #f8f9fa;
}

.messages {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.message {
  display: flex;
  max-width: 80%;
}

.own-message {
  align-self: flex-end;
}

.message-bubble {
  background: #e3f2fd;
  padding: 12px 16px;
  border-radius: 18px;
  border-bottom-right-radius: 4px;
  line-height: 1.4;
}

.own-message .message-bubble {
  background: #1e88e5;
  color: white;
  border-bottom-right-radius: 18px;
  border-bottom-left-radius: 4px;
}

.message-time {
  font-size: 0.8rem;
  color: #999;
  margin-top: 5px;
  display: block;
}

.own-message .message-time {
  color: rgba(255,255,255,0.8);
}

.message-input {
  padding: 15px;
  border-top: 1px solid #e0e0e0;
  background: white;
  display: flex;
  gap: 10px;
}

.message-input input {
  flex: 1;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 25px;
  outline: none;
}

.message-input input:focus {
  border-color: #1e88e5;
}

.send-btn {
  background: #1e88e5;
  color: white;
  border: none;
  width: 45px;
  height: 45px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s ease;
}

.send-btn:hover:not(:disabled) {
  background: #0d47a1;
}

.send-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

/* Modal interno */
.inner-overlay {
  background: rgba(0,0,0,0.5);
}

.new-chat-modal {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  z-index: 1001;
}

.modal-header {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  padding: 15px 20px;
  border-radius: 12px 12px 0 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
}

.modal-content {
  padding: 20px;
}

.subject-input, .message-textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  margin-bottom: 15px;
  font-family: inherit;
  resize: vertical;
}

.subject-input:focus, .message-textarea:focus {
  border-color: #1e88e5;
  outline: none;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.btn-secondary {
  background: #6c757d;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
}

.btn-secondary:hover {
  background: #545b62;
}

/* Responsive */
@media (max-width: 768px) {
  .chat-support {
    width: 95%;
    height: 85vh;
  }
  
  .section-header {
    flex-direction: column;
    gap: 10px;
    align-items: stretch;
  }
  
  .message {
    max-width: 90%;
  }
}
</style>