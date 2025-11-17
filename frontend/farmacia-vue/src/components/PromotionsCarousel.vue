<template>
  <div class="promotions-carousel" :class="{ hidden: hidden }">
    <button class="close-promotions" @click="$emit('toggle')">
      <i class="fas fa-times"></i>
    </button>
    
    <div class="carousel-container">
      <div class="carousel-track" :style="{ transform: `translateX(-${currentPromotion * 100}%)` }">
        <div class="carousel-slide" v-for="(promotion, index) in promotions" :key="promotion.id">
          <div class="promotion-badge">{{ promotion.discount }}</div>
          <img :src="promotion.image" :alt="promotion.name" class="promotion-image">
          <div class="promotion-info">
            <h4>{{ promotion.name }}</h4>
            <p class="promotion-price">
              <span class="old-price">${{ promotion.oldPrice }}</span>
              <span class="new-price">${{ promotion.newPrice }}</span>
            </p>
            <button class="promotion-btn" @click="$emit('add-to-cart', promotion)">
              Agregar
            </button>
          </div>
        </div>
      </div>
      
      <!-- Indicadores -->
      <div class="carousel-indicators">
        <button 
          v-for="(promotion, index) in promotions" 
          :key="index"
          :class="{ active: currentPromotion === index }"
          @click="$emit('change-promotion', index)"
        ></button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PromotionsCarousel',
  props: {
    promotions: Array,
    currentPromotion: Number,
    hidden: Boolean
  }
}
</script>