<template>
  <!-- ✅ ESTRUCTURA OBLIGATORIA PARA MODALES -->
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-container" @click.stop>
      
      <!-- HEADER CORREGIDO -->
      <div class="modal-header">
        <button class="btn-back" @click="$emit('close')">
          <i class="fas fa-arrow-left"></i>
        </button>
        <h2 class="modal-title">
          <i class="fas fa-star"></i>
          Agregar Reseña
        </h2>
        <button class="btn-close" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- CONTENIDO -->
      <div class="modal-content">
        <div class="review-form">
          <!-- Producto -->
          <div class="product-info" v-if="product">
            <img :src="product.image" :alt="product.name" class="product-image">
            <div class="product-details">
              <h3>{{ product.name }}</h3>
              <p class="product-category">{{ product.category }}</p>
            </div>
          </div>

          <!-- Rating -->
          <div class="rating-section">
            <label>Tu Calificación:</label>
            <StarRating 
              :interactive="true"
              :show-score="false"
              @rating-changed="setRating"
            />
            <span class="rating-text">{{ selectedRating }}/5 estrellas</span>
          </div>

          <!-- Comentario -->
          <div class="comment-section">
            <label for="review-comment">Tu Comentario (opcional):</label>
            <textarea 
              id="review-comment"
              v-model="comment"
              placeholder="Comparte tu experiencia con este producto..."
              rows="4"
              maxlength="500"
            ></textarea>
            <span class="char-count">{{ comment.length }}/500</span>
          </div>

          <!-- BOTÓN PARA VER RESEÑAS EXISTENTES -->
          <div class="view-reviews-section">
            <button class="btn-view-reviews" @click="openReviews">
              <i class="fas fa-eye"></i>
              Ver Reseñas Existentes
            </button>
          </div>

          <!-- Botones -->
          <div class="form-actions">
            <button class="btn-cancel" @click="$emit('close')">Cancelar</button>
            <button 
              class="btn-submit" 
              @click="submitReview"
              :disabled="!selectedRating || loading"
            >
              <i class="fas fa-spinner fa-spin" v-if="loading"></i>
              {{ loading ? 'Enviando...' : 'Publicar Reseña' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import StarRating from './StarRating.vue';
import { reviewService } from '../services/reviewService';

export default {
  name: 'AddReviewModal',
  components: {
    StarRating
  },
  props: {
    product: Object
  },
  data() {
    return {
      selectedRating: 0,
      comment: '',
      loading: false
    };
  },
  methods: {
    setRating(rating) {
      this.selectedRating = rating;
    },
    
    async submitReview() {
      if (!this.selectedRating) {
        alert('Por favor selecciona una calificación');
        return;
      }

      this.loading = true;
      
      try {
        await reviewService.createReview({
          producto_id: this.product.id,
          rating: this.selectedRating,
          comentario: this.comment
        });
        
        this.$emit('review-added');
        this.$emit('close');
        
      } catch (error) {
        console.error('Error al enviar reseña:', error);
        alert('Error al publicar la reseña: ' + (error.response?.data?.message || error.message));
      } finally {
        this.loading = false;
      }
    },

    openReviews() {
      this.$emit('view-reviews');
    }
  }
}
</script>

<style scoped>
/* ✅ ESTILOS CRÍTICOS - COPIAR Y PEGAR */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

.modal-container {
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  z-index: 1001;
  animation: slideUp 0.3s ease;
  overflow: hidden;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { 
    opacity: 0;
    transform: translateY(30px) scale(0.95);
  }
  to { 
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Header estándar */
.modal-header {
  background: linear-gradient(135deg, #1e88e5, #1565c0);
  color: white;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-radius: 16px 16px 0 0;
  min-height: 60px; /* Altura mínima garantizada */
}

.modal-title {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  white-space: nowrap;
  color: white !important; /* Forzar color blanco sólido */
  text-shadow: 0 1px 2px rgba(0,0,0,0.3); /* Añadir sombra para mejor contraste */
}

.modal-title i {
  color: #ffeb3b !important; /* Estrella amarilla brillante */
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}.modal-title {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  justify-content: center;
  text-align: center;
}

.btn-back, .btn-close {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.btn-back:hover, .btn-close:hover {
  background: rgba(255,255,255,0.3);
  transform: scale(1.1);
}

.modal-content {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
}

/* Estilos específicos del formulario */
.review-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.product-info {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.product-image {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  object-fit: cover;
}

.product-details h3 {
  margin: 0 0 5px 0;
  color: #2c3e50;
}

.product-category {
  margin: 0;
  color: #6c757d;
  font-size: 0.9rem;
}

.rating-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.rating-section label {
  font-weight: 600;
  color: #2c3e50;
}

.rating-text {
  color: #ffc107;
  font-weight: 600;
}

.comment-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.comment-section label {
  font-weight: 600;
  color: #2c3e50;
}

textarea {
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-family: inherit;
  resize: vertical;
  transition: border-color 0.3s ease;
}

textarea:focus {
  outline: none;
  border-color: #1e88e5;
}

.char-count {
  align-self: flex-end;
  font-size: 0.8rem;
  color: #6c757d;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 20px;
}

.btn-cancel, .btn-submit {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel {
  background: #6c757d;
  color: white;
}

.btn-submit {
  background: #4caf50;
  color: white;
}

.btn-cancel:hover {
  background: #5a6268;
}

.btn-submit:hover:not(:disabled) {
  background: #45a049;
}

.btn-submit:disabled {
  background: #ccc;
  cursor: not-allowed;
}
.view-reviews-section {
  text-align: center;
  margin: 15px 0;
}

.btn-view-reviews {
  background: transparent;
  color: #1e88e5;
  border: 2px solid #1e88e5;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
}

.btn-view-reviews:hover {
  background: #1e88e5;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(30,136,229,0.3);
}
</style>