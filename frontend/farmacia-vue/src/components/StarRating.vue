<template>
  <div class="star-rating">
    <span 
      v-for="star in 5" 
      :key="star"
      class="star"
      :class="{ active: star <= currentRating, interactive: interactive }"
      @click="interactive ? setRating(star) : null"
    >
      ★
    </span>
    <span v-if="showScore" class="rating-score">({{ currentRating }}/5)</span>
  </div>
</template>

<script>
export default {
  name: 'StarRating',
  props: {
    rating: { type: Number, default: 0 },
    interactive: { type: Boolean, default: false },
    showScore: { type: Boolean, default: true }
  },
  data() {
    return {
      currentRating: this.rating
    };
  },
  methods: {
    setRating(rating) {
      if (this.interactive) {
        this.currentRating = rating;
        this.$emit('rating-changed', rating);
      }
    }
  },
  watch: {
    rating(newRating) {
      this.currentRating = newRating;
    }
  }
}
</script>

<style scoped>
.star-rating {
  display: flex;
  align-items: center;
  gap: 2px;
}

.star {
  font-size: 1.2rem;
  color: #ddd;
  cursor: default;
  transition: color 0.2s ease;
}

.star.active {
  color: #ffc107;
}

.star.interactive {
  cursor: pointer;
}

.star.interactive:hover {
  color: #ffc107;
}

.rating-score {
  font-size: 0.9rem;
  color: #666;
  margin-left: 8px;
}
</style>