<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="chaos-modal" @click.stop>
      <!-- Header -->
      <div class="chaos-header">
        <div class="chaos-avatar">
          <i class="fas fa-robot"></i>
        </div>
        <div class="chaos-info">
          <h3>Chaos AI</h3>
          <span class="chaos-status">Asistente Farmacéutico</span>
        </div>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Chat Container -->
      <div class="chat-container">
        <div class="chat-messages">
          <div v-for="(message, index) in messages" :key="index" 
               :class="['message', message.type]">
            <div class="message-content">
              {{ message.text }}
            </div>
            <div class="message-time">
              {{ message.time }}
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Questions -->
      <div class="quick-questions">
        <div class="questions-title">Preguntas rápidas:</div>
        <div class="questions-grid">
          <button @click="askQuestion('¿Qué dosis de paracetamol para adultos?')" 
                  class="question-btn">Dosis Paracetamol</button>
          <button @click="askQuestion('Interacciones ibuprofeno y alcohol')" 
                  class="question-btn">Interacciones</button>
          <button @click="askQuestion('¿Para qué sirve la loratadina?')" 
                  class="question-btn">Uso Loratadina</button>
          <button @click="askQuestion('Efectos secundarios omeprazol')" 
                  class="question-btn">Efectos Omeprazol</button>
        </div>
      </div>

      <!-- Input -->
      <div class="chat-input-container">
        <input 
          v-model="userInput"
          @keyup.enter="sendMessage"
          placeholder="Escribe tu pregunta sobre medicamentos..."
          class="chat-input"
        />
        <button @click="sendMessage" class="send-btn">
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChaosAIModal',
  data() {
    return {
      userInput: '',
      messages: [
        {
          type: 'bot',
          text: '¡Hola! Soy Chaos AI, tu asistente farmacéutico. ¿En qué puedo ayudarte hoy? Puedo responder sobre medicamentos, dosis, interacciones y más.',
          time: this.getCurrentTime()
        }
      ]
    }
  },
  methods: {
    sendMessage() {
      if (!this.userInput.trim()) return;

      // Agregar mensaje del usuario
      this.messages.push({
        type: 'user',
        text: this.userInput,
        time: this.getCurrentTime()
      });

      // Generar respuesta
      const response = this.generateResponse(this.userInput.toLowerCase());
      
      // Simular typing delay
      setTimeout(() => {
        this.messages.push({
          type: 'bot',
          text: response,
          time: this.getCurrentTime()
        });
        
        // Scroll to bottom
        this.$nextTick(() => {
          const container = this.$el.querySelector('.chat-messages');
          container.scrollTop = container.scrollHeight;
        });
      }, 1000);

      this.userInput = '';
    },

    askQuestion(question) {
      this.userInput = question;
      this.sendMessage();
    },

    generateResponse(query) {
      const knowledgeBase = {
        // Medicamentos
        'paracetamol': '💊 **Paracetamol 500mg**\n\n• **Dosis adultos:** 1-2 tabletas cada 4-6 horas\n• **Dosis máxima:** 4g (8 tabletas) en 24 horas\n• **Para:** Dolor leve a moderado, fiebre\n• **Precauciones:** Evitar con alcohol, no exceder dosis\n• **Presentaciones:** Tabletas, suspensión, supositorios',
        
        'ibuprofeno': '💊 **Ibuprofeno 400mg**\n\n• **Dosis:** 1 tableta cada 6-8 horas con alimentos\n• **Para:** Dolor, inflamación, fiebre\n• **Interacciones:** Evitar con anticoagulantes, otros AINEs\n• **Contraindicaciones:** Úlceras gástricas, problemas renales\n• **Efectos comunes:** Molestias estomacales',
        
        'amoxicilina': '💊 **Amoxicilina 500mg**\n\n• **Dosis:** 1 tableta cada 8 horas (generalmente 7-10 días)\n• **Para:** Infecciones bacterianas\n• **Importante:** Completar todo el tratamiento\n• **Efectos:** Puede causar diarrea, tomar con probióticos\n• **Alergias:** Informar si es alérgico a penicilina',
        
        'omeprazol': '💊 **Omeprazol 20mg**\n\n• **Dosis:** 1 tableta al día en ayunas\n• **Para:** Acidez, reflujo, protección gástrica\n• **Duración:** Máximo 14 días sin supervisión médica\n• **Interacciones:** Puede afectar absorción de otros medicamentos\n• **Precauciones:** No usar prolongadamente sin receta',
        
        'loratadina': '💊 **Loratadina 10mg**\n\n• **Dosis:** 1 tableta al día\n• **Para:** Alergias, rinitis, urticaria\n• **Ventaja:** No produce sueño en la mayoría de personas\n• **Duración:** Efecto por 24 horas\n• **Precauciones:** Consultar en embarazo y lactancia',

        // Síntomas
        'dolor cabeza': '🩺 **Para dolor de cabeza:**\n\n• **Medicamentos:** Paracetamol 500mg o Ibuprofeno 400mg\n• **Dosis:** 1-2 tabletas, repetir cada 6 horas si es necesario\n• **Complementos:** Descansar en lugar oscuro, hidratación\n• **Alerta:** Si dolor es muy intenso o persistente, consultar médico',
        
        'fiebre': '🩺 **Para fiebre:**\n\n• **Medicamentos:** Paracetamol o Ibuprofeno\n• **Dosis adultos:** Paracetamol 500-1000mg cada 6 horas\n• **Medidas:** Hidratación, reposo, paños húmedos\n• **Urgencia:** Si fiebre >39°C por más de 48 horas',
        
        'tos': '🩺 **Para tos:**\n\n• **Tos seca:** Antitusivos como Dextrometorfano\n• **Tos productiva:** Expectorantes como Guaifenesina\n• **Natural:** Miel con limón, hidratación\n• **Consulta:** Si persiste más de 1 semana o con fiebre',

        // General
        'dosis': '📋 **Sobre dosis medicamentos:**\n\n• Siempre seguir indicaciones del médico o prospecto\n• Respetar intervalos entre dosis\n• No duplicar dosis si olvida una toma\n• Considerar edad, peso y condiciones del paciente\n• En duda, consultar siempre al farmacéutico',
        
        'interacción': '🔍 **Interacciones medicamentosas:**\n\n• Informar TODOS los medicamentos en uso al médico\n• Algunos medicamentos afectan la eficacia de otros\n• Alcohol puede potenciar efectos secundarios\n• Suplementos y hierbas también interactúan\n• Siempre verificar antes de mezclar tratamientos',
        
        'embarazo': '🤰 **Medicamentos en embarazo:**\n\n• Siempre consultar al médico antes de tomar cualquier medicamento\n• Algunos medicamentos pueden afectar al bebé\n• Paracetamol generalmente seguro en dosis normales\n• Evitar automedicación durante embarazo y lactancia\n• Informar al farmacéutico si está embarazada'
      };

      // Buscar respuesta
      for (const [key, response] of Object.entries(knowledgeBase)) {
        if (query.includes(key)) {
          return response;
        }
      }

      // Respuesta por defecto
      return `🤖 **Chaos AI**\n\nEntiendo que preguntas sobre: "${query}"\n\nPuedo ayudarte con información sobre:\n• 💊 Dosis de medicamentos\n• 🩺 Uso para síntomas específicos\n• 🔍 Interacciones entre medicamentos\n• ⚠️ Efectos secundarios\n• 📋 Recomendaciones generales\n\n¿Puedes ser más específico? Por ejemplo: "dosis de paracetamol" o "para qué sirve el ibuprofeno"`;
    },

    getCurrentTime() {
      return new Date().toLocaleTimeString('es-ES', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
    }
  },
  mounted() {
    // Focus en el input al abrir
    this.$nextTick(() => {
      const input = this.$el.querySelector('.chat-input');
      if (input) input.focus();
    });
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000;
}

.chaos-modal {
  width: 90%;
  max-width: 500px;
  height: 80vh;
  background: white;
  border-radius: 15px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.chaos-header {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  padding: 1.5rem;
  border-radius: 15px 15px 0 0;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.chaos-avatar {
  width: 50px;
  height: 50px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.chaos-info h3 {
  margin: 0;
  font-size: 1.2rem;
}

.chaos-status {
  font-size: 0.8rem;
  opacity: 0.8;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  margin-left: auto;
}

.chat-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.chat-messages {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.message {
  max-width: 80%;
  padding: 0.75rem 1rem;
  border-radius: 15px;
  position: relative;
}

.message.user {
  align-self: flex-end;
  background: #1e88e5;
  color: white;
  border-bottom-right-radius: 5px;
}

.message.bot {
  align-self: flex-start;
  background: #f5f5f5;
  color: #333;
  border-bottom-left-radius: 5px;
}

.message-content {
  white-space: pre-line;
  line-height: 1.4;
}

.message-time {
  font-size: 0.7rem;
  opacity: 0.7;
  margin-top: 0.25rem;
}

.quick-questions {
  padding: 1rem;
  border-top: 1px solid #eee;
}

.questions-title {
  font-size: 0.9rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.questions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
}

.question-btn {
  background: #e3f2fd;
  border: 1px solid #1e88e5;
  color: #1e88e5;
  padding: 0.5rem;
  border-radius: 8px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.question-btn:hover {
  background: #1e88e5;
  color: white;
}

.chat-input-container {
  padding: 1rem;
  border-top: 1px solid #eee;
  display: flex;
  gap: 0.5rem;
}

.chat-input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 25px;
  outline: none;
}

.chat-input:focus {
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
}

.send-btn:hover {
  background: #0d47a1;
}
</style>