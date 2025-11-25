import { createRouter, createWebHistory } from 'vue-router'

// Importar componentes
import AdminDashboard from '../components/AdminDashboard.vue'
import OrdersManagement from '../components/OrdersManagement.vue'
import ProductsManagement from '../components/ProductsManagement.vue'
import UsersManagement from '../components/UsersManagement.vue'
import CategoriesManagement from '../components/CategoriesManagement.vue'
import ReportsDashboard from '../components/ReportsDashboard.vue'
import AdminChatManagement from '../components/AdminChatManagement.vue'
import AdminLayout from '../views/AdminLayout.vue'

const routes = [
  // Ruta principal
  {
    path: '/',
    name: 'Home'
  },
  
  // RUTAS ADMINISTRATIVAS CON LAYOUT SEPARADO
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      {
        path: '',  // /admin
        name: 'AdminDashboard',
        component: AdminDashboard
      },
      {
        path: 'orders',  // /admin/orders
        name: 'AdminOrders', 
        component: OrdersManagement
      },
      {
        path: 'products',  // /admin/products
        name: 'AdminProducts',
        component: ProductsManagement
      },
      {
        path: 'users',  // /admin/users
        name: 'AdminUsers',
        component: UsersManagement
      },
      {
        path: 'categories',  // /admin/categories
        name: 'AdminCategories',
        component: CategoriesManagement
      },
      {
        path: 'reports',  // /admin/reports
        name: 'AdminReports',
        component: ReportsDashboard
      }
      // ELIMINADA la ruta chat
    ]
  },

  // Ruta de prueba (mantener por ahora)
  {
    path: '/test',
    name: 'Test',
    component: {
      template: '<div style="background: red; color: white; padding: 20px; font-size: 24px;">🎯 COMPONENTE DE PRUEBA FUNCIONANDO</div>'
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router