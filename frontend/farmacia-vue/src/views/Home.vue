<template>
  <div id="homeview">
    <!-- Banner de bienvenida mejorado -->
    <div class="welcome-banner">
      <div class="container">
        <h1>🩺 Bienvenidos a Farmacia Salud</h1>
        <p class="welcome-subtitle">Tu salud es nuestra mayor prioridad</p>
      </div>
    </div>
    
    <HeaderComponent 
      :carrito="carrito"
      :todos-productos="productos"
      @actualizar-cantidad="actualizarCantidad"
      @eliminar-del-carrito="eliminarDelCarrito"
      @vaciar-carrito="vaciarCarrito"
    />
    
    <main class="main-content">
      <!-- Hero Section -->
      <section class="hero-section">
        <div class="hero-overlay">
          <div class="hero-content">
          </div>
        </div>
      </section>

      <!-- Sección de productos -->
      <section class="products-section">
        <div class="container">
          <ProductList 
            :productos="productos"
            @agregar-al-carrito="agregarAlCarrito"
          />
        </div>
      </section>
    </main>
    
    <FooterComponent />
  </div>
</template>

<script>
// ✅ RUTAS CORRECTAS (desde views hacia components):
import HeaderComponent from '../components/Header.vue'
import ProductList from '../components/ProductList.vue'
import FooterComponent from '../components/Footer.vue'

export default {
  name: 'App',
  components: {
    HeaderComponent,
    ProductList,
    FooterComponent
  },
  data() {
    return {
      carrito: [],
      productos: [
  {
    id: 1,
    nombre: 'Paracetamol 500mg',
    descripcion: 'Alivia el dolor y la fiebre',
    precio: 8.50,
    emoji: '💊',
    categoria: 'Analgésicos'
  },
  {
    id: 2,
    nombre: 'Ibuprofeno 400mg',
    descripcion: 'Antiinflamatorio y analgésico',
    precio: 10.20,
    emoji: '💊',
    categoria: 'Antiinflamatorios'
  },
  {
    id: 3,
    nombre: 'Jarabe para la Tos',
    descripcion: 'Alivia la tos seca',
    precio: 12.00,
    emoji: '🍯',
    categoria: 'Antigripales'
  },
  {
    id: 4,
    nombre: 'Vitamina C 1000mg',
    descripcion: 'Refuerza el sistema inmunológico',
    precio: 15.75,
    emoji: '🍊',
    categoria: 'Vitaminas'
  },
  {
    id: 5,
    nombre: 'Curitas Antisépticas',
    descripcion: 'Protección para heridas pequeñas',
    precio: 5.25,
    emoji: '🩹',
    categoria: 'Primeros Auxilios'
  },
  {
    id: 6,
    nombre: 'Termómetro Digital',
    descripcion: 'Medición precisa de temperatura',
    precio: 22.00,
    emoji: '🌡️',
    categoria: 'Equipamiento'
  },
  {
    id: 7,
    nombre: 'Alcohol en Gel 500ml',
    descripcion: 'Desinfectante de manos',
    precio: 9.80,
    emoji: '🧴',
    categoria: 'Higiene'
  },
  {
    id: 8,
    nombre: 'Protector Solar FPS 50',
    descripcion: 'Protección contra rayos UV',
    precio: 18.50,
    emoji: '☀️',
    categoria: 'Cuidado Personal'
  },
  {
    id: 9,
    nombre: 'Shampoo Anticaspa',
    descripcion: 'Controla la caspa y picazón',
    precio: 12.30,
    emoji: '🧴',
    categoria: 'Cuidado Capilar'
  },
  {
    id: 10,
    nombre: 'Pañales Talla M',
    descripcion: 'Pañales ultra absorbentes',
    precio: 25.00,
    emoji: '👶',
    categoria: 'Infantil'
  },
  {
    id: 11,
    nombre: 'Omeprazol 20mg',
    descripcion: 'Alivia la acidez estomacal',
    precio: 14.80,
    emoji: '💊',
    categoria: 'Digestivos'
  },
  {
    id: 12,
    nombre: 'Aspirina 500mg',
    descripcion: 'Analgésico y antiinflamatorio',
    precio: 7.90,
    emoji: '💊',
    categoria: 'Analgésicos'
  },
  {
    id: 13,
    nombre: 'Jeringas Estériles',
    descripcion: 'Jeringas desechables 10 unidades',
    precio: 6.50,
    emoji: '💉',
    categoria: 'Equipamiento'
  },
  {
    id: 14,
    nombre: 'Crema Hidratante',
    descripcion: 'Hidratación profunda para la piel',
    precio: 16.20,
    emoji: '🧴',
    categoria: 'Cuidado Personal'
  },
  {
    id: 15,
    nombre: 'Multivitamínicos',
    descripcion: 'Complejo vitamínico completo',
    precio: 28.50,
    emoji: '💊',
    categoria: 'Vitaminas'
  },
  {
    id: 16,
    nombre: 'Tensiómetro Digital',
    descripcion: 'Monitor de presión arterial',
    precio: 45.00,
    emoji: '🩺',
    categoria: 'Equipamiento'
  },
  {
    id: 17,
    nombre: 'Leche Infantil 900g',
    descripcion: 'Fórmula para bebés',
    precio: 32.00,
    emoji: '🍼',
    categoria: 'Infantil'
  },
  {
    id: 18,
    nombre: 'Pastillas para la Garganta',
    descripcion: 'Alivia el dolor de garganta',
    precio: 8.75,
    emoji: '🍬',
    categoria: 'Antigripales'
  },
  {
    id: 19,
    nombre: 'Toallas Húmedas',
    descripcion: 'Toallas desinfectantes 80 unidades',
    precio: 11.40,
    emoji: '🧻',
    categoria: 'Higiene'
  },
  {
    id: 20,
    nombre: 'Nebulizador',
    descripcion: 'Nebulizador portátil',
    precio: 65.00,
    emoji: '💨',
    categoria: 'Equipamiento'
  }
],
      productosDescuento: [
        {
          id: 101,
          nombre: 'Paracetamol 500mg',
          descripcion: 'Alivia el dolor y la fiebre',
          precio: 8.50,
          precioOriginal: 12.00,
          descuento: 30,
          emoji: '💊',
          categoria: 'Analgésicos',
          tiempoRestante: '02:15:30'
        },
        {
          id: 102,
          nombre: 'Vitamina C 1000mg',
          descripcion: 'Refuerza el sistema inmunológico',
          precio: 11.00,
          precioOriginal: 15.75,
          descuento: 25,
          emoji: '🍊',
          categoria: 'Vitaminas',
          tiempoRestante: '01:45:20'
        },
        {
          id: 103,
          nombre: 'Jarabe para la Tos',
          descripcion: 'Alivia la tos seca',
          precio: 8.40,
          precioOriginal: 12.00,
          descuento: 30,
          emoji: '🍯',
          categoria: 'Antigripales',
          tiempoRestante: '03:20:15'
        },
        {
          id: 104,
          nombre: 'Crema Hidratante',
          descripcion: 'Hidratación profunda para la piel',
          precio: 11.34,
          precioOriginal: 16.20,
          descuento: 30,
          emoji: '🧴',
          categoria: 'Cuidado Personal',
          tiempoRestante: '04:10:45'
        }
      ]
    }
  },
  methods: {
    agregarAlCarrito(producto) {
      const itemExistente = this.carrito.find(item => item.id === producto.id)
      
      if (itemExistente) {
        itemExistente.cantidad++
      } else {
        this.carrito.push({
          ...producto,
          cantidad: 1
        })
      }
      
      alert(`✅ ${producto.nombre} agregado al carrito!`)
    },
    actualizarCantidad({ productId, type }) {
      const item = this.carrito.find(item => item.id === productId)
      if (item) {
        if (type === 'increment') {
          item.cantidad++
        } else if (type === 'decrement' && item.cantidad > 1) {
          item.cantidad--
        }
      }
    },
    eliminarDelCarrito(productId) {
      this.carrito = this.carrito.filter(item => item.id !== productId)
    },
    vaciarCarrito() {
      this.carrito = []
    }
  }
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.6;
  color: #333;
  
  /* IMAGEN DE FONDO - Opción 1 recomendada */
  background-image: linear-gradient(
      rgba(255, 255, 255, 0.30),
      rgba(255, 255, 255, 0.30)
    ),
    url(https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.freepik.es%2Ffotos-vectores-gratis%2Fmedicina&psig=AOvVaw1xJF-q-ZwHmX-ps_DQ95zq&ust=1761874833185000&source=images&cd=vfe&opi=89978449&ved=0CBUQjRxqFwoTCLCi9svlypADFQAAAAAdAAAAABAE);
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-attachment: fixed;
}

#app {
  min-height: 100vh;
}

.welcome-banner {
  background: linear-gradient(135deg, #1a6dba 0%, #0d4d8c 100%);
  color: white;
  text-align: center;
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  width: 100%;
}

.welcome-banner h1 {
  font-size: 2.2rem;
  font-weight: 600;
  margin: 0;
}

.welcome-subtitle {
  font-size: 1.1rem;
  opacity: 0.9;
  margin-top: 0.5rem;
}

.hero {
  text-align: center;
  padding: 3rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  margin-bottom: 2rem;
  width: 100%;
}

.hero h2 {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.hero p {
  font-size: 1.1rem;
  opacity: 0.9;
}

.main-content {
  min-height: 400px;
  padding-top: 80px;
}
</style>