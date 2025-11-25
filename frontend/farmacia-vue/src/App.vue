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
      @show-orders="showOrderHistory"
      @show-user-profile="showUserProfile"
      @show-admin="showAdminDashboard"
    />

    <Sidebar 
      :active="menuActive"
      :current-user="currentUser"
      @close="toggleMenu"
      @show-orders="showOrderHistory"
      @show-favorites="showFavorites" 
      @show-user-profile="showUserProfile"
      @show-admin-chat="showAdminChatManagementModal"
      @show-admin-dashboard="showAdminDashboard"
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

    <!-- RUTAS -->
    <router-view />

    <!-- Contenido Principal - SOLO en ruta principal -->
    <main class="main-content" v-if="$route.path === '/'">
      <!-- Banner Hero -->
      <HeroBanner />

      <!-- Sección de Productos -->
      <ProductList 
        :products="filteredProducts"
        :favorite-products="favoriteProducts"
        @add-to-cart="addToCart"
        @toggle-favorite="toggleFavorite"
        :user-reviews="userReviews"
        @write-review="showAddReview"
      />
    </main>

    <!-- Footer - SOLO en ruta principal -->
    <Footer v-if="$route.path === '/'" />

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
      :show="cartVisible"
      :items="cartItems"
      :total="cartTotal"
      @close="closeCart"
      @remove-from-cart="removeFromCart"
      @increase-quantity="increaseQuantity"
      @decrease-quantity="decreaseQuantity"
      @start-checkout="startCheckout"
      @reload-cart="loadCart"
      @clear-cart="clearCartLocal"
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

    <!-- Historial de Pedidos -->
    <OrderHistory 
      v-if="showOrderHistoryModal"
      @close="showOrderHistoryModal = false"
    />

    <FavoritesModal  
      v-if="showFavoritesModal"
      @close="showFavoritesModal = false"
      @add-to-cart="addToCart"
    />

    <!-- Gestión de Pedidos (modales antiguos) -->
    <OrdersManagement 
      v-if="showOrdersManagementModal"
      @close="showOrdersManagementModal = false"
    />

    <!-- Gestión de Productos -->
    <ProductsManagement 
      v-if="showProductsManagementModal"
      @close="showProductsManagementModal = false"
    />

    <!-- Gestión de Usuarios -->
    <UsersManagement 
      v-if="showUsersManagementModal"
      @close="showUsersManagementModal = false"
    />

    <ReportsDashboard 
      v-if="showReportsDashboardModal" 
      @close="showReportsDashboardModal = false" 
    />

    <AddReviewModal 
      v-if="showAddReviewModal"
      :product="selectedProductForReview"
      @close="showAddReviewModal = false"
      @review-added="loadProductReviews"
      @view-reviews="openReviewsFromAddReview"
    />

    <ProductReviews 
      v-if="showProductReviewsModal"
      :product="selectedProductForReview"
      @close="showProductReviewsModal = false"
    />

    <UserProfile 
      v-if="showUserProfileModal"
      :currentUser="currentUser"
      @close="showUserProfileModal = false"
      @edit-profile="showEditProfile"
    />

    <EditProfileModal 
      v-if="showEditProfileModal"
      :currentUser="currentUser"
      @close="showEditProfileModal = false"
      @profile-updated="handleProfileUpdated"
    />

    <ChatSupport 
      v-if="showChatSupport"
      :currentUser="currentUser"
      @close="showChatSupport = false"
    />

    <CategoriesManagement 
      v-if="showCategoriesManagementModal"
      @close="showCategoriesManagementModal = false"
    />

    <AdminChatManagement 
      v-if="showAdminChatManagement"
      @close="showAdminChatManagement = false"
    />

    <!-- Botón flotante de chat -->
    <div v-if="currentUser" class="chat-floating-btn" @click="showChatSupportModal">
      <i class="fas fa-comments"></i>
      <span class="notification-badge" v-if="unreadMessages > 0">{{ unreadMessages }}</span>
    </div>
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
import OrderHistory from './components/OrderHistory.vue'
import AdminDashboard from './components/AdminDashboard.vue'
import OrdersManagement from './components/OrdersManagement.vue'
import ProductsManagement from './components/ProductsManagement.vue'
import UsersManagement from './components/UsersManagement.vue'
import ReportsDashboard from './components/ReportsDashboard.vue'
import CategoriesManagement from './components/CategoriesManagement.vue'
import FavoritesModal from './components/FavoritesModal.vue'
import AddReviewModal from './components/AddReviewModal.vue'
import ProductReviews from './components/ProductReviews.vue'
import UserProfile from './components/UserProfile.vue'
import EditProfileModal from './components/EditProfileModal.vue'
import ChatSupport from './components/ChatSupport.vue'
import AdminChatManagement from './components/AdminChatManagement.vue'

// ✅ IMPORTAR SERVICIOS REALES
import { authService } from './services/authService'
import { productService } from './services/productService'
import { cartService } from './services/cartService'
import { orderService } from './services/orderService'
import { favoriteService } from './services/favoriteService'

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
    PaymentModal,
    OrderHistory,
    AdminDashboard,
    OrdersManagement,
    ProductsManagement,
    UsersManagement,
    CategoriesManagement,
    FavoritesModal,
    AddReviewModal,
    ProductReviews,
    UserProfile,
    EditProfileModal,
    ChatSupport,
    AdminChatManagement,
    ReportsDashboard
  },
  data() {
    return {
      menuActive: false,
      cartVisible: false,
      showLoginModal: false,
      showRegisterModal: false,
      showPaymentModal: false,
      showOrderHistoryModal: false,
      showAdminDashboardModal: false,
      showOrdersManagementModal: false,
      showProductsManagementModal: false,
      showUsersManagementModal: false,
      showReportsDashboardModal: false,
      showFavoritesModal: false,
      favoriteProducts: [],
      showAddReviewModal: false,
      selectedProductForReview: null,
      showProductReviewsModal: false,
      userReviews: [],
      showCategoriesManagementModal: false,
      paymentStep: 1,
      orderNumber: null,
      searchQuery: '',
      selectedCategory: '',
      promotionsHidden: false,
      showUserProfileModal: false,
      showEditProfileModal: false,
      showChatSupport: false,
      showAdminChatManagement: false,
      unreadMessages: 0,
      currentPromotion: 0,
      carouselInterval: null,
      currentUser: null,
      
      // ✅ PRODUCTOS VACÍOS - SE CARGARÁN DESDE EL BACKEND
      products: [],
      filteredProducts: [],
      cartItems: [],
      
      // ✅ PROMOCIONES VACÍAS - SE CARGARÁN DESDE EL BACKEND
      promotions: []
    }
  },
  watch: {
    showAdminChatManagement(newVal) {
      console.log('🔄 showAdminChatManagement cambió a:', newVal)
    }
  },
  computed: {
    cartTotal() {
      return this.cartItems.reduce((total, item) => {
        return total + (item.price * item.quantity)
      }, 0).toFixed(2)
    }
  },
  async mounted() {
    console.log('🚀 App montada - Cargando datos...');
    
    // ✅ CARGAR DATOS REALES AL INICIAR
    await this.loadProducts();
    await this.loadPromotions();
    await this.loadCart();
    
    // Verificar si hay usuario autenticado
    if (authService.isAuthenticated()) {
      this.currentUser = authService.getCurrentUser();
      console.log('👤 Usuario autenticado:', this.currentUser);
    } else {
      console.log('🔓 Usuario no autenticado');
    }
    
    this.filterProducts();
    this.startCarousel();
    
    console.log('✅ App completamente cargada');
  },
  methods: {
    showAdminChatManagementModal() {
      this.showAdminChatManagement = true;
    },
    showChatSupportModal() {
      if (!this.currentUser) {
        this.showLoginModal = true;
        return;
      }
      this.showChatSupport = true;
    },
    showEditProfile() {
      console.log('✏️ Abriendo editor de perfil')
      this.showEditProfileModal = true
    },
    
    handleProfileUpdated(updatedUser) {
      console.log('🔄 App.vue: Recibiendo usuario actualizado', updatedUser)
      
      if (!updatedUser) {
        console.error('❌ updatedUser es null o undefined')
        return
      }
      
      // Actualizar currentUser
      this.currentUser = { ...this.currentUser, ...updatedUser }
      console.log('👤 Usuario actualizado en App.vue:', this.currentUser)
      
      // Guardar también en localStorage para persistencia
      localStorage.setItem('user_data', JSON.stringify(this.currentUser))
      console.log('💾 Datos guardados en localStorage')
    },
    
    showUserProfile() {
      console.log('👤 Abriendo perfil de usuario')
      this.showUserProfileModal = true
    },
    
    async toggleFavorite(product) {
      if (!this.currentUser) {
        alert('Debes iniciar sesión para agregar favoritos');
        return;
      }
      
      try {
        console.log('🔄 Toggle favorite para producto:', product.id, product.name);
        
        // Verificar si ya es favorito
        const checkResponse = await favoriteService.checkFavorite(product.id);
        const esFavorito = checkResponse.data.es_favorito;
        console.log('❤️ Es favorito?:', esFavorito);
        
        // Asegurar que favoriteProducts existe
        if (!this.favoriteProducts) {
          this.favoriteProducts = [];
        }
        
        if (esFavorito) {
          // Remover de favoritos
          await favoriteService.removeFromFavorites(product.id);
          // Actualizar estado local
          this.favoriteProducts = this.favoriteProducts.filter(id => id !== product.id);
          console.log('❌ Producto removido de favoritos. Estado actual:', this.favoriteProducts);
        } else {
          // Agregar a favoritos
          await favoriteService.addToFavorites(product.id);
          // Actualizar estado local
          this.favoriteProducts.push(product.id);
          console.log('✅ Producto agregado a favoritos. Estado actual:', this.favoriteProducts);
        }
        
      } catch (error) {
        console.error('Error al toggle favorite:', error);
        alert('Error al actualizar favoritos');
      }
    },
    
    // Cargar reseñas del usuario
    async loadUserReviews() {
      if (!this.currentUser) return;
      
      try {
        // Importar dinámicamente para evitar dependencias circulares
        const { reviewService } = await import('./services/reviewService');
        const response = await reviewService.getMyReviews();
        if (response.data.success) {
          this.userReviews = response.data.resenas;
        }
      } catch (error) {
        console.error('Error cargando reseñas del usuario:', error);
      }
    },
    
    showAddReview(product) {
      this.selectedProductForReview = product;
      this.showAddReviewModal = true;
    },
    
    openReviewsFromAddReview() {
      this.showAddReviewModal = false;
      this.showProductReviewsModal = true;
    },
    
    // ✅ CARGAR PRODUCTOS DESDE BACKEND
    async loadProducts() {
      try {
        console.log('📦 Cargando productos...');
        const response = await productService.getProducts();
        
        // TRANSFORMAR DATOS DEL BACKEND AL FORMATO DEL FRONTEND
        this.products = (response.productos || response.data || response).map(product => ({
          id: product.id,
          name: product.nombre,
          description: product.descripcion,
          price: parseFloat(product.precio),
          image: product.imagen,
          category: product.categoria?.slug || 'general',
          badge: product.badge
        }));
        
        this.filteredProducts = [...this.products];
        console.log(`✅ ${this.products.length} productos cargados`);
      } catch (error) {
        console.error('❌ Error cargando productos:', error);
        this.products = this.getBackupProducts();
        this.filteredProducts = [...this.products];
        console.log('🔄 Usando datos de respaldo');
      }
    },
    
    showProductReviews(product) {
      this.selectedProductForReview = product;
      this.showProductReviewsModal = true;
    },
    
    // ✅ CARGAR PROMOCIONES DESDE BACKEND
    async loadPromotions() {
      try {
        console.log('🎯 Cargando promociones...');
        const response = await productService.getActivePromotions();
        
        this.promotions = (response.promociones || response.data || response).map(promocion => {
          const basePrice = 20;
          const discount = promocion.descuento_porcentaje / 100;
          
          return {
            id: promocion.id,
            name: promocion.nombre,
            description: promocion.descripcion,
            discount: promocion.descuento_porcentaje + '% OFF',
            image: promocion.imagen,
            oldPrice: basePrice,
            newPrice: basePrice * (1 - discount)
          };
        });
        
        console.log('✅ Promociones transformadas:', this.promotions);
      } catch (error) {
        console.error('❌ Error cargando promociones:', error);
        this.promotions = this.getBackupPromotions();
      }
    },
    
    // ✅ CARGAR CARRITO DESDE BACKEND
    async loadCart() {
      if (!authService.isAuthenticated()) {
        console.log('🛒 Usuario no autenticado - Saltando carga de carrito');
        return;
      }
      
      try {
        console.log('🛒 Cargando carrito desde BD...');
        const response = await cartService.getCart();
        
        this.cartItems = (response.items || []).map(item => ({
          id: item.id,
          productId: item.producto_id,
          name: item.producto?.nombre || 'Producto',
          description: item.producto?.descripcion || '',
          price: parseFloat(item.precio_unitario || 0),
          image: item.producto?.imagen || '',
          quantity: item.cantidad || 1
        }));
        
        console.log(`✅ ${this.cartItems.length} items cargados desde BD`);
      } catch (error) {
        console.error('❌ Error cargando carrito:', error);
      }
    },

    // ✅ CORREGIDO: MÉTODO ÚNICO PARA GESTIÓN DE PEDIDOS
    showAdminOrders() {
      console.log('🎯 App.vue: showAdminOrders() EJECUTADO');
      this.showOrdersManagementModal = true;
      this.showAdminDashboardModal = false;
      console.log('✅ Estados actualizados:', {
        showOrdersManagementModal: this.showOrdersManagementModal,
        showAdminDashboardModal: this.showAdminDashboardModal
      });
    },
    
    // 🔥 NUEVO: MÉTODO PARA MOSTRAR FAVORITOS
    showFavorites() {
      console.log('🎯 App.vue: showFavorites() EJECUTADO');
      console.log('📊 Estado ANTES:', this.showFavoritesModal);
      this.showFavoritesModal = true;
      setTimeout(() => {
        console.log('📊 Estado DESPUÉS:', this.showFavoritesModal);
      }, 100);
      console.log('✅ Modal de favoritos activado');
    },
    
    // 🔥 NUEVO: MÉTODO PARA GESTIÓN DE PRODUCTOS
    showProductsManagement() {
      console.log('🎯 App.vue: showProductsManagement() EJECUTADO');
      this.showProductsManagementModal = true;
      this.showAdminDashboardModal = false;
      console.log('✅ Estados actualizados:', {
        showProductsManagementModal: this.showProductsManagementModal,
        showAdminDashboardModal: this.showAdminDashboardModal
      });
    },
    
    // 🔥 NUEVO: MÉTODO PARA GESTIÓN DE USUARIOS
    showUsersManagement() {
      console.log('🎯 App.vue: showUsersManagement() EJECUTADO');
      this.showUsersManagementModal = true;
      this.showAdminDashboardModal = false;
      console.log('✅ Estados actualizados:', {
        showUsersManagementModal: this.showUsersManagementModal,
        showAdminDashboardModal: this.showAdminDashboardModal
      });
    },
    
    // 🔥 NUEVO: MÉTODO PARA DASHBOARD DE REPORTES
    showReportsDashboard() {
      console.log('🎯 App.vue: showReportsDashboard() EJECUTADO');
      this.showReportsDashboardModal = true;
      this.showAdminDashboardModal = false;
      console.log('✅ Estados actualizados:', {
        showReportsDashboardModal: this.showReportsDashboardModal,
        showAdminDashboardModal: this.showAdminDashboardModal
      });
    },
    
    // ✅ DATOS DE RESPALDO
    getBackupProducts() {
      return [
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
          id: 2,
          name: 'Paracetamol 500mg',
          description: 'Alivia el dolor y reduce la fiebre',
          price: 5.50,
          image: 'https://via.placeholder.com/300x200/FFEBEE/333?text=Paracetamol',
          category: 'medicamentos',
          badge: 'Más Vendido'
        },
        {
          id: 3,
          name: 'Protector solar FPS 50',
          description: 'Protección alta contra rayos UV',
          price: 18.50,
          image: 'https://via.placeholder.com/300x200/FFF9C4/333?text=Protector+Solar',
          category: 'cuidado-piel',
          badge: 'Nuevo'
        },
        {
          id: 4,
          name: 'Complejo B',
          description: 'Suplemento de vitaminas del grupo B',
          price: 16.80,
          image: 'https://via.placeholder.com/300x200/FFF9C4/333?text=Complejo+B',
          category: 'vitaminas'
        },
        {
          id: 5,
          name: 'Alcohol etílico 250ml',
          description: 'Antiséptico para limpieza de heridas',
          price: 4.20,
          image: 'https://via.placeholder.com/300x200/E8F5E8/333?text=Alcohol+Etílico',
          category: 'primeros-auxilios',
          badge: 'Esencial'
        }
      ];
    },
    
    getBackupPromotions() {
      return [
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
      ];
    },

    // MÉTODOS DE NAVEGACIÓN
    toggleMenu() {
      this.menuActive = !this.menuActive;
    },
    
    toggleCart() {
      this.cartVisible = !this.cartVisible;
    },
    
    closeCart() {
      this.cartVisible = false;
    },
    
    handleSearch(query) {
      this.searchQuery = query;
      this.filterProducts();
    },
    
    handleCategoryFilter(category) {
      this.selectedCategory = category;
      this.filterProducts();
    },
    
    filterProducts() {
      this.filteredProducts = this.products.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            product.description.toLowerCase().includes(this.searchQuery.toLowerCase());
        const matchesCategory = !this.selectedCategory || product.category === this.selectedCategory;
        
        return matchesSearch && matchesCategory;
      });
    },
    
    // ✅ AGREGAR AL CARRITO
    async addToCart(product) {
      if (!authService.isAuthenticated()) {
        alert('Por favor inicia sesión para agregar productos al carrito y realizar compras');
        this.showLoginModal = true;
        return;
      }

      try {
        const existingItem = this.cartItems.find(item => item.productId === product.id);
        
        if (existingItem) {
          existingItem.quantity += 1;
        } else {
          this.cartItems.push({
            id: Date.now(),
            productId: product.id,
            name: product.name,
            description: product.description,
            price: product.price,
            image: product.image,
            quantity: 1
          });
        }

        if (authService.isAuthenticated()) {
          await cartService.addToCart({
            producto_id: product.id,
            cantidad: 1,
            precio_unitario: product.price
          });
          await this.loadCart();
        }
        
        this.showCartNotification(product.name);
        
      } catch (error) {
        console.error('❌ Error guardando en BD:', error);
        this.cartItems = this.cartItems.filter(item => item.productId !== product.id);
        alert('Error al agregar producto al carrito');
      }
    },
    
    // ✅ NUEVO MÉTODO - NOTIFICACIÓN DEL CARRITO
    showCartNotification(productName) {
      const notification = document.createElement('div');
      notification.className = 'cart-notification';
      notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>¡${productName} agregado al carrito!</span>
      `;
      
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4caf50;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInRight 0.3s ease;
        font-weight: 600;
      `;
      
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
          }
        }, 300);
      }, 3000);
    },

    // ✅ MÉTODOS DEL CARRITO
    async increaseQuantity(item) {
      try {
        item.quantity += 1;
        
        if (authService.isAuthenticated()) {
          await cartService.updateCartItem(item.id, item.quantity);
        }
      } catch (error) {
        console.error('❌ Error aumentando cantidad:', error);
        item.quantity -= 1;
      }
    },

    async decreaseQuantity(item) {
      try {
        if (item.quantity > 1) {
          item.quantity -= 1;
          
          if (authService.isAuthenticated()) {
            await cartService.updateCartItem(item.id, item.quantity);
          }
        } else {
          await this.removeFromCart(item);
        }
      } catch (error) {
        console.error('❌ Error actualizando cantidad:', error);
        item.quantity += 1;
      }
    },

    async removeFromCart(item) {
      try {
        this.cartItems = this.cartItems.filter(cartItem => cartItem.id !== item.id);
        
        if (authService.isAuthenticated()) {
          await cartService.removeFromCart(item.id);
        }
      } catch (error) {
        console.error('❌ Error eliminando item:', error);
        alert('Error al eliminar el producto del carrito');
      }
    },

    async clearCartLocal() {
      try {
        this.cartItems = [];
        if (authService.isAuthenticated()) {
          await cartService.clearCart();
        }
      } catch (error) {
        console.error('❌ Error vaciando carrito:', error);
      }
    },
    
    // ✅ MOSTRAR HISTORIAL DE PEDIDOS
    showOrderHistory() {
      this.showOrderHistoryModal = true;
      this.menuActive = false;
    },

    // ✅ MOSTRAR PANEL ADMINISTRATIVO
    showAdminDashboard() {
      console.log('🎯 App.vue: showAdminDashboard() EJECUTADO');
      
      if (this.currentUser && this.currentUser.is_admin) {
        this.showAdminDashboardModal = true;
        console.log('✅ Panel administrativo abierto');
      } else {
        console.log('❌ Usuario no es administrador');
        alert('No tienes permisos de administrador para acceder a esta sección');
      }
    },

    // ✅ MOSTRAR GESTIÓN DE CATEGORÍAS
    showCategoriesManagement() {
      this.showCategoriesManagementModal = true;
    },

    switchToRegister() {
      this.showLoginModal = false;
      this.showRegisterModal = true;
    },

    switchToLogin() {
      this.showRegisterModal = false;
      this.showLoginModal = true;
    },

    // ✅ CORREGIDO: AUTENTICACIÓN
    async login(userData) {
      try {
        console.log('🔐 Ejecutando login...', userData);
        
        if (!userData || !userData.user) {
          throw new Error('Datos de login inválidos');
        }
        
        this.currentUser = userData.user;
        localStorage.setItem('auth_token', userData.authorization.token);
        localStorage.setItem('user', JSON.stringify(userData.user));
        this.showLoginModal = false;
        
        await this.$nextTick();
        
        alert(`¡Bienvenido ${this.currentUser.name}!`);
        await this.loadCart();
        await this.loadUserReviews();
        
        console.log('✅ Login completado correctamente');
      } catch (error) {
        console.error('❌ Error en login:', error);
        alert('Error al iniciar sesión: ' + error.message);
      }
    },
    
    async register(userData) {
      try {
        console.log('📝 App.vue - Ejecutando registro...', userData);
        
        console.log('🔍 Campos disponibles en userData:', Object.keys(userData));
        console.log('🔍 userData completo:', userData);
        
        const user = userData.user || userData;
        const token = userData.authorization?.token || userData.token || userData.user?.token;
        
        if (!user || !token) {
          console.error('❌ No se pudo extraer usuario o token:', { user, token });
          throw new Error('Estructura de datos de registro inválida');
        }
        
        console.log('✅ Usuario y token extraídos:', { user, token });
        
        this.currentUser = user;
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));
        this.showRegisterModal = false;
        
        console.log('🎉 Usuario registrado y logueado:', this.currentUser);
        await this.loadCart();
        await this.loadUserReviews();
        
        alert(`¡Cuenta creada exitosamente! Bienvenido ${user.name}`);
        
      } catch (error) {
        console.error('❌ Error en registro App.vue:', error);
        if (!error.message.includes('inválida')) {
          alert('Error en el registro: ' + error.message);
        }
      }
    },  
    
    logout() {
      authService.logout();
      this.currentUser = null;
      this.cartItems = [];
      alert('Sesión cerrada correctamente');
    },
    
    togglePromotions() {
      this.promotionsHidden = !this.promotionsHidden;
      if (!this.promotionsHidden) {
        this.startCarousel();
      } else {
        this.stopCarousel();
      }
    },
    
    addToCartFromPromotion(promotion) {
      const product = {
        id: promotion.id,
        name: promotion.name,
        description: promotion.description,
        price: promotion.newPrice,
        image: promotion.image
      };
      this.addToCart(product);
    },
    
    startCarousel() {
      this.stopCarousel();
      this.carouselInterval = setInterval(() => {
        this.currentPromotion = (this.currentPromotion + 1) % this.promotions.length;
      }, 3000);
    },
    
    stopCarousel() {
      if (this.carouselInterval) {
        clearInterval(this.carouselInterval);
        this.carouselInterval = null;
      }
    },
    
    startCheckout() {
      if (this.cartItems.length === 0) {
        alert('Tu carrito está vacío');
        return;
      }
      this.showPaymentModal = true;
      this.paymentStep = 1;
    },
    
    goToPayment(shippingInfo) {
      if (!shippingInfo.fullName || !shippingInfo.address || !shippingInfo.city || !shippingInfo.phone) {
        alert('Por favor completa toda la información de envío');
        return;
      }
      this.paymentStep = 3;
    },
    
    processPayment(paymentInfo) {
      if (!paymentInfo.method) {
        alert('Por favor selecciona un método de pago');
        return;
      }

      this.orderNumber = 'ORD' + Date.now().toString().slice(-6);
      this.paymentStep = 4;
    },
    
    finishPayment() {
      this.cartItems = [];
      this.showPaymentModal = false;
      this.cartVisible = false;
      this.paymentStep = 1;
      
      alert(`¡Gracias por tu compra! Tu orden #${this.orderNumber} ha sido procesada.`);
    }
  }
}
</script>

<style>
/* Estilos para la notificación del carrito */
.cart-notification {
  position: fixed;
  top: 20px;
  right: 20px;
  background: #4caf50;
  color: white;
  padding: 15px 20px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  z-index: 10000;
  display: flex;
  align-items: center;
  gap: 10px;
  animation: slideInRight 0.3s ease;
  font-weight: 600;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slideOutRight {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(100%);
    opacity: 0;
  }
}

/* Botón flotante de chat */
.chat-floating-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(30, 136, 229, 0.4);
  z-index: 999;
  transition: all 0.3s ease;
}

.chat-floating-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(30, 136, 229, 0.6);
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ff4757;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

/* Responsive */
@media (max-width: 768px) {
  .chat-floating-btn {
    bottom: 15px;
    right: 15px;
    width: 55px;
    height: 55px;
    font-size: 20px;
  }
}
</style>