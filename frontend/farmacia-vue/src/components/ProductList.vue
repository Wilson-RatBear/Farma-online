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
          <div class="product-image-container">
            <img :src="product.image" :alt="product.name" class="product-image">
            <div class="product-badge" v-if="product.badge">{{ product.badge }}</div>
          </div>
          <div class="product-info">
            <h3 class="product-name">{{ product.name }}</h3>
            <p class="product-description">{{ product.description }}</p>
            <div class="product-price-section">
              <span class="product-price">${{ product.price }}</span>
              <button class="add-to-cart" @click="$emit('add-to-cart', product)">
                Agregar al Carrito
              </button>
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
    products: Array
  }
}
</script>