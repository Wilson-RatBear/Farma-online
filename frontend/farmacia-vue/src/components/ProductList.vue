<template>
  <section class="products-section">
    <div class="container">
      <!-- ENCABEZADO CON CONTADOR DINÁMICO -->
      <div class="section-header">
        <h2 class="section-title">
          {{ showAllProducts ? 'Todos Nuestros Productos' : 'Productos Destacados' }}
          <span class="product-count">({{ displayedCount }})</span>
        </h2>
        
        <!-- BOTÓN VER MENOS (solo aparece cuando se muestran todos) -->
        <button 
          v-if="showAllProducts" 
          class="view-less-btn"
          @click="$emit('show-limited-products')"
        >
          <i class="fas fa-times"></i>
          Ver Menos Productos
        </button>
      </div>

      <!-- GRILLA DE PRODUCTOS -->
      <div class="products-grid">
        <div 
          class="product-card" 
          v-for="product in products" 
          :key="product.id"
        >
          <!-- CORAZÓN DE FAVORITOS -->
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
                  <i class="fas fa-cart-plus"></i>
                  Agregar
                </button>
                
                <!-- BOTÓN ESCRIBIR RESEÑA -->
                <button 
                  class="write-review" 
                  @click="$emit('write-review', product)"
                  title="Escribir reseña"
                >
                  <i class="fas fa-star"></i> Reseñar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MENSAJE CUANDO NO HAY PRODUCTOS -->
      <div v-if="products.length === 0" class="no-products">
        <p>No se encontraron productos.</p>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'ProductList',
  props: {
    products: Array,
    favoriteProducts: Array,
    showAllProducts: Boolean,
    totalProducts: Number,
    displayedCount: Number // ✅ NUEVO PROP PARA EL CONTADOR
  },
  methods: {
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

/* ENCABEZADO CON CONTADOR */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 15px;
}

.section-title {
  font-size: 2rem;
  color: #2c3e50;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
}

.product-count {
  font-size: 1rem;
  color: #666;
  font-weight: normal;
  background: #e3f2fd;
  padding: 4px 12px;
  border-radius: 20px;
  border: 1px solid #bbdefb;
}

/* BOTÓN VER MENOS */
.view-less-btn {
  background: linear-gradient(135deg, #666, #444);
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.view-less-btn:hover {
  background: linear-gradient(135deg, #555, #333);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

/* GRILLA DE PRODUCTOS */
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

/* DESCRIPCIÓN CORREGIDA - SIN WARNINGS */
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
  line-clamp: 2;
  max-height: 2.8em;
}

.product-price-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.product-price {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1e88e5;
}

/* ACCIONES DE PRODUCTO */
.product-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 100%;
}

/* BOTÓN AGREGAR AL CARRITO */
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
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
}

.add-to-cart:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(30,136,229,0.4);
}

/* BOTÓN ESCRIBIR RESEÑA */
.write-review {
  background: linear-gradient(135deg, #42a5f5, #1976d2);
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(66,165,245,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  position: relative;
  overflow: hidden;
}

.write-review:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(66,165,245,0.4);
  background: linear-gradient(135deg, #1976d2, #1565c0);
}

.write-review:active {
  transform: translateY(0);
}

/* Efecto de línea pulsante */
.write-review::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, #ffffff, #e3f2fd, #ffffff);
  animation: pulseLine 2s infinite;
}

@keyframes pulseLine {
  0%, 100% { opacity: 0.7; }
  50% { opacity: 1; }
}

.write-review i {
  font-size: 1rem;
  color: #e3f2fd;
}

/* MENSAJE SIN PRODUCTOS */
.no-products {
  text-align: center;
  padding: 40px;
  color: #666;
  font-size: 1.1rem;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .section-header {
    flex-direction: column;
    align-items: stretch;
    text-align: center;
  }
  
  .products-grid {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
  }
  
  .section-title {
    font-size: 1.6rem;
  }
  
  .view-less-btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .product-card {
    padding: 15px;
  }
}
</style>