<template>
  <section class="hero-banner">
    <div class="container">
      <div class="hero-content">
        <h1 class="hero-title">Tu salud es nuestra mayor prioridad</h1>
        <p class="hero-subtitle">Encuentra los mejores productos farmacéuticos con calidad garantizada</p>
        <button class="cta-button" @click="$emit('show-all-products')">
          <i class="fas fa-shopping-bag"></i>
          Ver Productos
        </button>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'HeroBanner',
  emits: ['show-all-products']
}
</script>

<style scoped>
.hero-banner {
  background: linear-gradient(135deg, #1e88e5 0%, #0d47a1 100%);
  color: white;
  padding: 60px 0;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero-banner::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.1"><polygon points="0,0 1000,50 1000,100 0,100"/></svg>') no-repeat bottom;
  background-size: cover;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  position: relative;
  z-index: 2;
}

.hero-content {
  max-width: 600px;
  margin: 0 auto;
}

.hero-title {
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 16px;
  line-height: 1.3;
  text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.hero-subtitle {
  font-size: 1.1rem;
  margin-bottom: 32px;
  opacity: 0.9;
  line-height: 1.5;
}

/* BOTÓN AZUL CON DISEÑO PROFESIONAL */
.cta-button {
  background: linear-gradient(135deg, #2196F3, #1976D2);
  color: white;
  border: none;
  padding: 14px 32px;
  border-radius: 50px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(255, 255, 255, 0.4);
  display: inline-flex;
  align-items: center;
  gap: 10px;
  position: relative;
  overflow: hidden;
}

.cta-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.5s;
}

.cta-button:hover::before {
  left: 100%;
}

.cta-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(33, 150, 243, 0.6);
  background: linear-gradient(135deg, #1976D2, #1565C0);
}

.cta-button:active {
  transform: translateY(0);
}

.cta-button i {
  font-size: 1rem;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .hero-banner {
    padding: 50px 0;
  }
  
  .hero-title {
    font-size: 1.8rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
    margin-bottom: 28px;
  }
  
  .cta-button {
    padding: 12px 28px;
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .hero-title {
    font-size: 1.6rem;
  }
  
  .hero-subtitle {
    font-size: 0.95rem;
  }
  
  .cta-button {
    width: 100%;
    justify-content: center;
  }
}
</style>