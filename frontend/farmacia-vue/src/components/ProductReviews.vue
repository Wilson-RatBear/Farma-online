<template>
  <!-- ✅ ESTRUCTURA OBLIGATORIA PARA MODALES -->
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-container" @click.stop>
      
      <!-- HEADER ESTÁNDAR -->
      <div class="modal-header">
        <button class="btn-back" @click="$emit('close')">
          <i class="fas fa-arrow-left"></i> Volver
        </button>
        <h2 class="modal-title">
          <i class="fas fa-comments"></i>
          Reseñas del Producto
          <span class="items-count">({{ reviews.length }})</span>
        </h2>
        <button class="btn-close" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- CONTENIDO -->
      <div class="modal-content">
        <!-- Producto Info -->
        <div class="product-header" v-if="product">
          <div class="product-summary">
            <img :src="product.image" :alt="product.name" class="product-image">
            <div class="product-details">
              <h3>{{ product.name }}</h3>
              <div class="rating-overview">
                <StarRating :rating="stats.promedio_rating" :show-score="true" />
                <span class="total-reviews">{{ stats.total_resenas }} reseñas</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Distribución de Ratings -->
        <div class="rating-distribution" v-if="stats.distribucion_ratings">
          <h4>Distribución de Calificaciones</h4>
          <div class="distribution-bars">
            <div v-for="i in 5" :key="i" class="distribution-item">
              <span class="star-count">{{ i }}★</span>
              <div class="bar-container">
                <div 
                  class="bar-fill" 
                  :style="{ width: getPercentage(i) + '%' }"
                ></div>
              </div>
              <span class="count">{{ stats.distribucion_ratings[i] || 0 }}</span>
            </div>
          </div>
        </div>

        <!-- Lista de Reseñas -->
        <div class="reviews-list">
          <div v-if="loading" class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando reseñas...</p>
          </div>

          <div v-else-if="reviews.length === 0" class="empty-state">
            <i class="fas fa-comment-slash"></i>
            <h3>No hay reseñas aún</h3>
            <p>Sé el primero en opinar sobre este producto</p>
          </div>

          <div v-else class="reviews-container">
            <div v-for="review in reviews" :key="review.id" class="review-item">
              <div class="review-header">
                <div class="user-info">
                  <div class="user-avatar">
                    {{ review.usuario?.name?.charAt(0)?.toUpperCase() || 'U' }}
                  </div>
                  <div class="user-details">
                    <span class="user-name">{{ review.usuario?.name || 'Usuario' }}</span>
                    <span class="review-date">{{ formatDate(review.created_at) }}</span>
                  </div>
                </div>
                <StarRating :rating="review.rating" :show-score="false" />
              </div>
              
              <div class="review-content">
                <p class="review-comment" v-if="review.comentario">{{ review.comentario }}</p>
                <p class="no-comment" v-else>Este usuario no dejó un comentario.</p>
              </div>
            </div>
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
  name: 'ProductReviews',
  components: {
    StarRating
  },
  props: {
    product: Object
  },
  data() {
    return {
      reviews: [],
      stats: {
        promedio_rating: 0,
        total_resenas: 0,
        distribucion_ratings: {}
      },
      loading: false
    };
  },
  mounted() {
    this.loadReviews();
  },
  methods: {
    async loadReviews() {
      if (!this.product) return;
      
      this.loading = true;
      try {
        const response = await reviewService.getProductReviews(this.product.id);
        if (response.data.success) {
          this.reviews = response.data.resenas;
          this.stats = response.data.estadisticas;
        }
      } catch (error) {
        console.error('Error cargando reseñas:', error);
      } finally {
        this.loading = false;
      }
    },

    getPercentage(rating) {
      const total = this.stats.total_resenas;
      if (total === 0) return 0;
      const count = this.stats.distribucion_ratings[rating] || 0;
      return (count / total) * 100;
    },

    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    }
  },
  watch: {
    product() {
      this.loadReviews();
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
  max-width: 600px;
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
}

.modal-title {
  margin: 0;
  font-size: 1.3rem;
  display: flex;
  align-items: center;
  gap: 8px;
}

.items-count {
  font-size: 0.9rem;
  opacity: 0.9;
  background: rgba(255,255,255,0.2);
  padding: 2px 8px;
  border-radius: 12px;
}

.btn-back, .btn-close {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 5px;
}

.btn-back:hover, .btn-close:hover {
  background: rgba(255,255,255,0.3);
  transform: translateY(-1px);
}

.modal-content {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
}

/* Estilos específicos de reseñas */
.product-header {
  padding: 0 0 20px 0;
  border-bottom: 1px solid #e9ecef;
  margin-bottom: 20px;
}

.product-summary {
  display: flex;
  align-items: center;
  gap: 15px;
}

.product-image {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  object-fit: cover;
}

.product-details h3 {
  margin: 0 0 8px 0;
  color: #2c3e50;
}

.rating-overview {
  display: flex;
  align-items: center;
  gap: 10px;
}

.total-reviews {
  color: #6c757d;
  font-size: 0.9rem;
}

.rating-distribution {
  margin-bottom: 25px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.rating-distribution h4 {
  margin: 0 0 15px 0;
  color: #2c3e50;
}

.distribution-item {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.star-count {
  width: 30px;
  font-weight: 600;
  color: #ffc107;
}

.bar-container {
  flex: 1;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  background: #ffc107;
  border-radius: 4px;
  transition: width 0.5s ease;
}

.count {
  width: 20px;
  text-align: right;
  font-size: 0.8rem;
  color: #6c757d;
}

.review-item {
  padding: 20px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  margin-bottom: 15px;
  background: white;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1e88e5, #1565c0);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1rem;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  color: #2c3e50;
}

.review-date {
  font-size: 0.8rem;
  color: #6c757d;
}

.review-content {
  color: #495057;
  line-height: 1.5;
}

.review-comment {
  margin: 0;
}

.no-comment {
  margin: 0;
  font-style: italic;
  color: #6c757d;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #6c757d;
}

.empty-state i {
  font-size: 3rem;
  color: #dee2e6;
  margin-bottom: 15px;
}

.empty-state h3 {
  margin: 0 0 10px 0;
  color: #495057;
}
</style>