import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import ContentListView from '../views/ContentListView.vue'
import ContentFormView from '../views/ContentFormView.vue'

const routes = [
  { path: '/',               redirect: '/login' },
  { path: '/login',          name: 'login',          component: LoginView,       meta: { requiresGuest: true } },
  { path: '/register',       name: 'register',        component: RegisterView,    meta: { requiresGuest: true } },
  { path: '/content-items',  name: 'content-list',    component: ContentListView, meta: { requiresAuth: true } },
  { path: '/content-items/:id', name: 'content-form', component: ContentFormView, meta: { requiresAuth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  // Ruta protegida y el usuario no está autenticado → al login
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  // Ruta de invitado (login/register) y el usuario ya está autenticado → al listado
  if (to.meta.requiresGuest && auth.isAuthenticated) {
    return { name: 'content-list' }
  }
})

export default router
