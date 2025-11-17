<template>
  <div id="app">
    <!-- Header -->
    <Header 
      :cart-count="cartItems.length"
      :current-user="currentUser"
      @toggle-menu="toggleMenu"
      @toggle-cart="toggleCart"
      @show-login="showLoginModal = true"
      @logout="logout"
      @search="handleSearch"
      @filter-category="handleCategoryFilter"
    />

    <!-- Menú Lateral -->
    <Sidebar 
      :active="menuActive"
      @close="toggleMenu"
    />

    <!-- Carousel de Promociones -->
    <PromotionsCarousel 
      :promotions="promotions"
      :current-promotion="currentPromotion"
      :hidden="promotionsHidden"
      @toggle="togglePromotions"
      @change-promotion="currentPromotion = $event"
      @add-to-cart="addToCartFromPromotion"
    />

    <!-- Contenido Principal -->
    <main class="main-content">
      <!-- Banner Hero -->
      <HeroBanner />

      <!-- Sección de Productos -->
      <ProductList 
        :products="filteredProducts"
        @add-to-cart="addToCart"
      />
    </main>

    <!-- Footer -->
    <Footer />

    <!-- Modales -->
    <LoginModal 
      v-if="showLoginModal"
      @close="showLoginModal = false"
      @show-register="switchToRegister"
      @login="login"
    />

    <RegisterModal 
      v-if="showRegisterModal"
      @close="showRegisterModal = false"
      @show-login="switchToLogin"
      @register="register"
    />

    <CartModal 
      v-if="cartVisible"
      :items="cartItems"
      :total="cartTotal"
      @close="closeCart"
      @remove-from-cart="removeFromCart"
      @increase-quantity="increaseQuantity"
      @decrease-quantity="decreaseQuantity"
      @start-checkout="startCheckout"
    />

    <PaymentModal 
      v-if="showPaymentModal"
      :show="showPaymentModal"
      :step="paymentStep"
      :items="cartItems"
      :total="cartTotal"
      :order-number="orderNumber"
      @close="showPaymentModal = false"
      @next-step="paymentStep++"
      @prev-step="paymentStep--"
      @go-to-payment="goToPayment"
      @process-payment="processPayment"
      @finish-payment="finishPayment"
    />
  </div>
</template>

<script>
import Header from './components/Header.vue'
import Sidebar from './components/Sidebar.vue'
import PromotionsCarousel from './components/PromotionsCarousel.vue'
import HeroBanner from './components/HeroBanner.vue'
import ProductList from './components/ProductList.vue'
import Footer from './components/Footer.vue'
import LoginModal from './components/LoginModal.vue'
import RegisterModal from './components/RegisterModal.vue'
import CartModal from './components/CartModal.vue'
import PaymentModal from './components/PaymentModal.vue'

export default {
  name: 'App',
  components: {
    Header,
    Sidebar,
    PromotionsCarousel,
    HeroBanner,
    ProductList,
    Footer,
    LoginModal,
    RegisterModal,
    CartModal,
    PaymentModal
  },
  data() {
    return {
      menuActive: false,
      cartVisible: false,
      showLoginModal: false,
      showRegisterModal: false,
      showPaymentModal: false,
      paymentStep: 1,
      orderNumber: null,
      searchQuery: '',
      selectedCategory: '',
      promotionsHidden: false,
      currentPromotion: 0,
      carouselInterval: null,
      currentUser: null,
      
      // SOLO 5 PRODUCTOS como solicitaste
      products: [
        {
          id: 1,
          name: 'Aceite para bebé Johnson',
          description: 'Hidratación suave para la piel del bebé',
          price: 8.50,
          image: 'https://via.placeholder.com/300x200/FFE0B2/white?text=Aceite+Bebé',
          category: 'aseo-personal',
          badge: 'Esencial'
        },
        {
          id: 15,
          name: 'Paracetamol 500mg',
          description: 'Alivia el dolor y reduce la fiebre',
          price: 5.50,
          image: 'https://via.placeholder.com/300x200/FFEBEE/333?text=Paracetamol',
          category: 'medicamentos',
          badge: 'Más Vendido'
        },
        {
          id: 30,
          name: 'Protector solar FPS 50',
          description: 'Protección alta contra rayos UV',
          price: 18.50,
          image: 'https://via.placeholder.com/300x200/FFF9C4/333?text=Protector+Solar',
          category: 'cuidado-piel',
          badge: 'Nuevo'
        },
        {
          id: 44,
          name: 'Complejo B',
          description: 'Suplemento de vitaminas del grupo B',
          price: 16.80,
          image: 'https://via.placeholder.com/300x200/FFF9C4/333?text=Complejo+B',
          category: 'vitaminas'
        },
        {
          id: 60,
          name: 'Alcohol etílico 250ml',
          description: 'Antiséptico para limpieza de heridas',
          price: 4.20,
          image: 'https://via.placeholder.com/300x200/E8F5E8/333?text=Alcohol+Etílico',
          category: 'primeros-auxilios',
          badge: 'Esencial'
        }
      ],
      filteredProducts: [],
      cartItems: [],
      
      promotions: [
        {
          id: 101,
          name: 'Paracetamol x2',
          description: 'Llévate 2 y paga 1',
          oldPrice: 17.00,
          newPrice: 8.50,
          discount: '50% OFF',
          image: 'https://via.placeholder.com/80x80/4CAF50/white?text=2x1'
        },
        {
          id: 102,
          name: 'Kit Auxilios',
          description: 'Todo en uno',
          oldPrice: 45.00,
          newPrice: 32.00,
          discount: '30% OFF',
          image: 'https://via.placeholder.com/80x80/2196F3/white?text=Kit'
        },
        {
          id: 103,
          name: 'Vitaminas Pack',
          description: 'Completo mensual',
          oldPrice: 60.00,
          newPrice: 45.00,
          discount: '25% OFF',
          image: 'https://via.placeholder.com/80x80/FF9800/white?text=Vitaminas'
        }
      ]
    }
  },
  computed: {
    cartTotal() {
      return this.cartItems.reduce((total, item) => {
        return total + (item.price * item.quantity)
      }, 0).toFixed(2)
    }
  },
  mounted() {
    this.filterProducts()
    this.startCarousel()
  },
  methods: {
    toggleMenu() {
      this.menuActive = !this.menuActive
    },
    toggleCart() {
      this.cartVisible = !this.cartVisible
    },
    closeCart() {
      this.cartVisible = false
    },
    handleSearch(query) {
      this.searchQuery = query
      this.filterProducts()
    },
    handleCategoryFilter(category) {
      this.selectedCategory = category
      this.filterProducts()
    },
    filterProducts() {
      this.filteredProducts = this.products.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            product.description.toLowerCase().includes(this.searchQuery.toLowerCase())
        const matchesCategory = !this.selectedCategory || product.category === this.selectedCategory
        
        return matchesSearch && matchesCategory
      })
    },
    addToCart(product) {
      const existingItem = this.cartItems.find(item => item.id === product.id)
      
      if (existingItem) {
        existingItem.quantity += 1
      } else {
        this.cartItems.push({
          ...product,
          quantity: 1
        })
      }
      
      alert(`${product.name} agregado al carrito`)
    },
    removeFromCart(item) {
      this.cartItems = this.cartItems.filter(cartItem => cartItem.id !== item.id)
    },
    increaseQuantity(item) {
      item.quantity += 1
    },
    decreaseQuantity(item) {
      if (item.quantity > 1) {
        item.quantity -= 1
      } else {
        this.removeFromCart(item)
      }
    },
    switchToRegister() {
      this.showLoginModal = false
      this.showRegisterModal = true
    },
    switchToLogin() {
      this.showRegisterModal = false
      this.showLoginModal = true
    },
    login(loginData) {
      if (loginData.email && loginData.password) {
        this.currentUser = {
          name: loginData.email.split('@')[0],
          email: loginData.email
        }
        this.showLoginModal = false
        alert(`¡Bienvenido ${this.currentUser.name}!`)
      } else {
        alert('Por favor completa todos los campos')
      }
    },
    register(registerData) {
      if (registerData.password !== registerData.confirmPassword) {
        alert('Las contraseñas no coinciden')
        return
      }
      
      if (registerData.name && registerData.email && registerData.password) {
        this.currentUser = {
          name: registerData.name,
          email: registerData.email
        }
        this.showRegisterModal = false
        alert(`¡Cuenta creada exitosamente! Bienvenido ${this.currentUser.name}`)
      } else {
        alert('Por favor completa todos los campos')
      }
    },
    logout() {
      this.currentUser = null
      alert('Sesión cerrada correctamente')
    },
    togglePromotions() {
      this.promotionsHidden = !this.promotionsHidden
      if (!this.promotionsHidden) {
        this.startCarousel()
      } else {
        this.stopCarousel()
      }
    },
    addToCartFromPromotion(promotion) {
      const product = {
        id: promotion.id,
        name: promotion.name,
        description: promotion.description,
        price: promotion.newPrice,
        image: promotion.image
      }
      this.addToCart(product)
    },
    startCarousel() {
      this.stopCarousel()
      this.carouselInterval = setInterval(() => {
        this.currentPromotion = (this.currentPromotion + 1) % this.promotions.length
      }, 3000)
    },
    stopCarousel() {
      if (this.carouselInterval) {
        clearInterval(this.carouselInterval)
        this.carouselInterval = null
      }
    },
    startCheckout() {
      if (this.cartItems.length === 0) {
        alert('Tu carrito está vacío')
        return
      }
      this.showPaymentModal = true
      this.paymentStep = 1
    },
    goToPayment(shippingInfo) {
      if (!shippingInfo.fullName || !shippingInfo.address || !shippingInfo.city || !shippingInfo.phone) {
        alert('Por favor completa toda la información de envío')
        return
      }
      this.paymentStep = 3
    },
    processPayment(paymentInfo) {
      if (!paymentInfo.method) {
        alert('Por favor selecciona un método de pago')
        return
      }

      if (paymentInfo.method === 'credit-card') {
        if (!paymentInfo.cardNumber || !paymentInfo.expiryDate || !paymentInfo.cvv) {
          alert('Por favor completa la información de la tarjeta')
          return
        }
      }

      this.orderNumber = 'ORD' + Date.now().toString().slice(-6)
      this.paymentStep = 4
    },
    finishPayment() {
      this.cartItems = []
      this.showPaymentModal = false
      this.cartVisible = false
      this.paymentStep = 1
      
      alert(`¡Gracias por tu compra! Tu orden #${this.orderNumber} ha sido procesada.`)
    }
  }
}
</script>

<style>
</style>