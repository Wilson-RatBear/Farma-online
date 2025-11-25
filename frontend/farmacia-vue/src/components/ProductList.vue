<template>
  <section class="products-section">
    <div class="container">
      <h2 class="section-title">Nuestros Productos</h2>
      <div class="products-grid">
        <div 
          class="product-card" 
          v-for="product in products" 
          :key="product.id"
        >
          <!-- CORAZÓN -->
          <button 
            class="favorite-btn" 
            :class="{ active: isFavorite(product.id) }"
            @click="toggleFavorite(product)"
            :title="isFavorite(product.id) ? 'Quitar de favoritos' : 'Agregar a favoritos'"
          >
            <i class="fas fa-heart"></i>
          </button>

          <div class="product-image-container">
            <img :src="product.image" :alt="product.name" class="product-image">
          </div>
          <div class="product-info">
            <h3 class="product-name">{{ product.name }}</h3>
            <p class="product-description">{{ product.description }}</p>
            <div class="product-price-section">
              <span class="product-price">${{ product.price }}</span>
              <div class="product-actions">
                <!-- BOTÓN AGREGAR AL CARRITO -->
                <button class="add-to-cart" @click="$emit('add-to-cart', product)">
                  Agregar al Carrito
                </button>
                <!-- NUEVO BOTÓN ESCRIBIR RESEÑA -->
                <button 
  class="write-review" 
  @click="$emit('write-review', product)"
  title="Escribir reseña"
  style="background: linear-gradient(135deg, #42a5f5, #1976d2) !important; color: white !important; padding: 8px 12px !important; border-radius: 6px !important; font-weight: 600 !important; border: none !important; box-shadow: 0 2px 6px rgba(66,165,245,0.3) !important; display: flex !important; align-items: center !important; justify-content: center !important; gap: 6px !important; font-size: 0.85rem !important;"
>
  <i class="fas fa-star" style="font-size: 0.8rem !important;"></i> Reseñar
</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'ProductList',
  props: {
    products: Array,
    favoriteProducts: Array // ← AGREGAR ESTE PROP
  },
  methods: {
    // Verificar si el usuario ya reseñó este producto
hasUserReviewed(productId) {
  return this.userReviews?.some(review => review.producto_id === productId) || false;
},
    toggleFavorite(product) {
      this.$emit('toggle-favorite', product);
    },
    
    // Método para verificar si es favorito
    isFavorite(productId) {
      return this.favoriteProducts?.includes(productId) || false;
    }
  }
}
</script>

<style scoped>
.products-section {
  padding: 40px 0;
  background: #f8f9fa;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.section-title {
  text-align: center;
  font-size: 2rem;
  color: #2c3e50;
  margin-bottom: 30px;
  font-weight: 700;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 25px;
  padding: 20px 0;
}

.product-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  position: relative;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

/* CORAZÓN - ESQUINA DERECHA */
.favorite-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.favorite-btn:hover {
  background: white;
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.favorite-btn i {
  color: #ccc;
  font-size: 1.1rem;
  transition: all 0.3s ease;
}

/* CORAZÓN ACTIVO (ROJO) */
.favorite-btn.active i {
  color: #ff4081;
}

.favorite-btn:hover i {
  color: #ff4081;
}

.favorite-btn.active:hover i {
  color: #e91e63;
}

/* BADGE MÁS CORTO - ESQUINA IZQUIERDA */
.product-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: #4caf50;
  color: white;
  padding: 3px 8px; /* Más compacto */
  border-radius: 12px; /* Más redondeado */
  font-size: 0.7rem; /* Texto más pequeño */
  font-weight: 600;
  text-transform: uppercase;
  z-index: 5;
  white-space: nowrap; /* Evita que se rompa en varias líneas */
}

.product-image-container {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .product-image {
  transform: scale(1.05);
}

.product-info {
  padding: 20px;
}

.product-name {
  font-size: 1.2rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 8px;
  line-height: 1.3;
}

/* DESCRIPCIÓN SIN WARNING */
.product-description {
  color: #6c757d;
  font-size: 0.9rem;
  line-height: 1.5;
  margin-bottom: 15px;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  line-clamp: 2; /* Elimina el warning */
}

.product-price-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.product-price {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1e88e5;
}

/* BOTÓN AZUL */
.add-to-cart {
  background: linear-gradient(135deg, #1e88e5, #1565c0);
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(30,136,229,0.3);
}

.add-to-cart:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(30,136,229,0.4);
}

/* Responsive */
@media (max-width: 768px) {
  .products-grid {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
  }
  
  .section-title {
    font-size: 1.6rem;
  }
  
  .product-price-section {
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
  }
  
  .add-to-cart {
    width: 100%;
  }
  /* NUEVOS ESTILOS PARA LOS BOTONES */
.product-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 100%;
}

.write-review {
  background: linear-gradient(135deg, #4caf50, #45a049);
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(76,175,80,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 0.95rem;
  position: relative;
  overflow: hidden;
  border: 1px solid #43a047;
}

.write-review:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(76,175,80,0.4);
  background: linear-gradient(135deg, #45a049, #388e3c);
}

.write-review:active {
  transform: translateY(0);
}

/* Efecto médico - línea pulsante */
.write-review::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, #ffffff, #e8f5e8, #ffffff);
  animation: pulseLine 2s infinite;
}

@keyframes pulseLine {
  0%, 100% { opacity: 0.7; }
  50% { opacity: 1; }
}

.write-review i {
  font-size: 1rem;
  color: #e8f5e8;
}

/* Para que los botones queden igual de anchos */
.product-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  width: 100%;
}

.add-to-cart, .write-review {
  width: 100%;
}
}

</style>