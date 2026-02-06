<template>
  <!-- Componente condicional basado en si hay promociones -->
  <div v-if="hasPromotions" class="promotions-carousel" :class="{ hidden: hidden }">
    <!-- ... resto del template igual ... -->
  </div>
</template>

<script>
export default {
  name: 'PromotionsCarousel',
  props: {
    promotions: Array,
    currentPromotion: Number,
    hidden: Boolean
  },
  computed: {
    hasPromotions() {
      // Verifica que promotions no sea null/undefined y tenga al menos un elemento
      return this.promotions && this.promotions.length > 0
    }
  }
}
</script>