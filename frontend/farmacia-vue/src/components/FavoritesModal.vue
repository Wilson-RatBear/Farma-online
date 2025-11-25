<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="favorites-modal" @click.stop>
      <!-- Header -->
      <div class="modal-header">
        <button class="btn-back" @click="$emit('close')">
          <i class="fas fa-arrow-left"></i>
          Volver
        </button>
        <h2 class="modal-title">
          <i class="fas fa-heart"></i>
          Mis Favoritos
          <span class="items-count">({{ favorites.length }})</span>
        </h2>
        <button class="btn-close" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Contenido -->
      <div class="modal-content">
        <!-- Loading -->
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Cargando favoritos...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="favorites.length === 0" class="empty-state">
          <i class="fas fa-heart-broken"></i>
          <h3>No tienes favoritos aún</h3>
          <p>Agrega productos a tus favoritos para verlos aquí</p>
          <button class="btn-primary" @click="$emit('close')">
            Seguir comprando
          </button>
        </div>

        <!-- Lista de Favoritos -->
        <div v-else class="favorites-list">
          <div v-for="favorite in favorites" :key="favorite.id" class="favorite-item">
            <div class="product-image">
              <img :src="favorite.producto.imagen || '/placeholder-product.jpg'" 
                   :alt="favorite.producto.nombre"
                   @error="handleImageError">
            </div>
            
            <div class="product-info">
              <h4 class="product-name">{{ favorite.producto.nombre }}</h4>
              <p class="product-category">{{ favorite.producto.categoria?.nombre }}</p>
              <p class="product-price">${{ favorite.producto.precio }}</p>
            </div>

            <div class="product-actions">
              <button class="btn-add-cart" @click="addToCart(favorite.producto)">
                <i class="fas fa-cart-plus"></i>
              </button>
              <button class="btn-remove-favorite" @click="removeFavorite(favorite.producto.id)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { favoriteService } from '../services/favoriteService';

export default {
  name: 'FavoritesModal',
  data() {
    return {
      favorites: [],
      loading: false,
      error: null
    };
  },
  mounted() {
    this.loadFavorites();
  },
  methods: {
    async loadFavorites() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await favoriteService.getFavorites();
        if (response.data.success) {
          this.favorites = response.data.favoritos;
        }
      } catch (error) {
        console.error('Error loading favorites:', error);
        this.error = 'Error al cargar favoritos';
      } finally {
        this.loading = false;
      }
    },

    async removeFavorite(productoId) {
      try {
        const response = await favoriteService.removeFromFavorites(productoId);
        if (response.data.success) {
          // Remover localmente
          this.favorites = this.favorites.filter(fav => fav.producto_id !== productoId);
        }
      } catch (error) {
        console.error('Error removing favorite:', error);
        alert('Error al eliminar de favoritos');
      }
    },

    addToCart(product) {
      this.$emit('add-to-cart', product);
    },

    handleImageError(event) {
      event.target.src = '/placeholder-product.jpg';
    }
  }
};
</script>

<style scoped>
/* ESTILOS CRÍTICOS - FUERA DEL MEDIA QUERY */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

.favorites-modal {
  background: white;
  border-radius: 16px;
  width: 85%;
  max-width: 500px; /* Más compacto */
  max-height: 70vh; /* Menos alto */
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  z-index: 1001;
  animation: slideUp 0.3s ease;
  overflow: hidden; /* Para bordes redondeados */
}

/* Animaciones */
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

/* HEADER MEJORADO */
.modal-header {
  background: linear-gradient(135deg, #1e88e5, #1565c0);
  color: white;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-radius: 16px 16px 0 0;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn-back, .btn-close {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 5px;
}

.btn-back:hover, .btn-close:hover {
  background: rgba(255,255,255,0.3);
  transform: translateY(-1px);
}

.modal-title {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.modal-title i {
  color: #ff4081;
}

.items-count {
  font-size: 0.8rem;
  opacity: 0.9;
  background: rgba(255,255,255,0.2);
  padding: 2px 8px;
  border-radius: 12px;
}

/* CONTENIDO MEJORADO */
.modal-content {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
  background: #f8f9fa;
}

/* ITEMS MEJORADOS */
.favorite-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 16px;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  margin-bottom: 12px;
  transition: all 0.3s ease;
  background: white;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.favorite-item:hover {
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  transform: translateY(-2px);
  border-color: #1e88e5;
}

.product-image {
  width: 70px;
  height: 70px;
  border-radius: 10px;
  overflow: hidden;
  flex-shrink: 0;
  border: 2px solid #f1f3f4;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.favorite-item:hover .product-image img {
  transform: scale(1.05);
}

.product-info {
  flex: 1;
  min-width: 0; /* Para truncar texto largo */
}

.product-name {
  margin: 0 0 6px 0;
  font-weight: 600;
  color: #2c3e50;
  font-size: 1rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-category {
  margin: 0 0 6px 0;
  font-size: 0.85rem;
  color: #6c757d;
  font-weight: 500;
}

.product-price {
  margin: 0;
  font-weight: bold;
  color: #1e88e5;
  font-size: 1.1rem;
}

/* ACCIONES MEJORADAS */
.product-actions {
  display: flex;
  gap: 8px;
}

.btn-add-cart, .btn-remove-favorite {
  padding: 10px 12px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
}

.btn-add-cart {
  background: #4caf50;
  color: white;
  box-shadow: 0 2px 5px rgba(76,175,80,0.3);
}

.btn-remove-favorite {
  background: #f44336;
  color: white;
  box-shadow: 0 2px 5px rgba(244,67,54,0.3);
}

.btn-add-cart:hover {
  background: #45a049;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(76,175,80,0.4);
}

.btn-remove-favorite:hover {
  background: #d32f2f;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(244,67,54,0.4);
}

/* ESTADOS MEJORADOS */
.loading-state, .empty-state {
  text-align: center;
  padding: 50px 20px;
  color: #6c757d;
}

.loading-state i {
  font-size: 2rem;
  color: #1e88e5;
  margin-bottom: 15px;
}

.empty-state i {
  font-size: 3rem;
  color: #dee2e6;
  margin-bottom: 15px;
}

.empty-state h3 {
  margin: 0 0 10px 0;
  color: #495057;
  font-weight: 600;
}

.empty-state p {
  margin: 0 0 25px 0;
  color: #6c757d;
  line-height: 1.5;
}

.btn-primary {
  background: linear-gradient(135deg, #1e88e5, #1565c0);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(30,136,229,0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(30,136,229,0.4);
}

/* RESPONSIVE MEJORADO */
@media (max-width: 768px) {
  .favorites-modal {
    width: 95%;
    margin: 10px;
    max-height: 80vh;
  }
  
  .modal-header {
    padding: 14px 16px;
  }
  
  .modal-title {
    font-size: 1.2rem;
  }
  
  .favorite-item {
    flex-direction: column;
    text-align: center;
    padding: 20px;
  }
  
  .product-info {
    text-align: center;
  }
  
  .product-actions {
    justify-content: center;
    width: 100%;
  }
  
  .btn-add-cart, .btn-remove-favorite {
    flex: 1;
    max-width: 120px;
  }
}

/* Scrollbar personalizado */
.modal-content::-webkit-scrollbar {
  width: 6px;
}

.modal-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.modal-content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>