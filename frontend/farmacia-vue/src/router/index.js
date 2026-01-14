import { createRouter, createWebHistory } from 'vue-router'
import AdminLayout from '../views/AdminLayout.vue'
import AdminDashboard from '../components/AdminDashboard.vue'
import OrdersManagement from '../components/OrdersManagement.vue'
import ProductsManagement from '../components/ProductsManagement.vue'
import UsersManagement from '../components/UsersManagement.vue'
import CategoriesManagement from '../components/CategoriesManagement.vue'
import ReportsDashboard from '../components/ReportsDashboard.vue'

const routes = [
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      {
        path: '',
        name: 'AdminDashboard',
        component: AdminDashboard
      },
      {
        path: 'orders',
        name: 'AdminOrders', 
        component: OrdersManagement
      },
      {
        path: 'products',
        name: 'AdminProducts',
        component: ProductsManagement
      },
      {
        path: 'users',
        name: 'AdminUsers',
        component: UsersManagement
      },
      {
        path: 'categories',
        name: 'AdminCategories',
        component: CategoriesManagement
      },
      {
        path: 'reports',
        name: 'AdminReports',
        component: ReportsDashboard
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router