import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import ContentListView from '../views/ContentListView.vue'
import ContentFormView from '../views/ContentFormView.vue'
import ContentReviewView from '../views/ContentReviewView.vue'
import ClinicsView from '../views/ClinicsView.vue'
import AdminView from '../views/AdminView.vue'

const routes = [
  { path: '/',                         redirect: '/dashboard' },
  { path: '/admin',                    name: 'admin',          component: AdminView,          meta: { requiresAuth: true, requiresRole: 'admin' } },
  { path: '/login',                    name: 'login',          component: LoginView,          meta: { requiresGuest: true } },
  { path: '/dashboard',                name: 'dashboard',      component: DashboardView,      meta: { requiresAuth: true } },
  { path: '/clinics',                  name: 'clinics',        component: ClinicsView,        meta: { requiresAuth: true, requiresRole: 'agency' } },
  { path: '/content-items',            name: 'content-list',   component: ContentListView,    meta: { requiresAuth: true } },
  { path: '/content-items/new',        name: 'content-new',    component: ContentFormView,    meta: { requiresAuth: true } },
  { path: '/content-items/:id/edit',   name: 'content-form',   component: ContentFormView,    meta: { requiresAuth: true } },
  { path: '/content-items/:id/review', name: 'content-review', component: ContentReviewView,  meta: { requiresAuth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.requiresGuest && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }

  if (to.meta.requiresRole && auth.user?.role !== to.meta.requiresRole) {
    return { name: 'dashboard' }
  }
})

export default router
