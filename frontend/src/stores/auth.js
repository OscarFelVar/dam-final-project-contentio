import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../api/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('user') ?? 'null'))
  const token = ref(localStorage.getItem('token') ?? null)

  const isAuthenticated = computed(() => !!token.value)

  function persist(userData, authToken) {
    user.value = userData
    token.value = authToken
    localStorage.setItem('user', JSON.stringify(userData))
    localStorage.setItem('token', authToken)
    api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
  }

  function clear() {
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    delete api.defaults.headers.common['Authorization']
  }

  // Restore token on store init (page refresh)
  if (token.value) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  async function login(email, password) {
    const { data } = await api.post('/auth/login', { email, password })
    persist(data.user, data.token)
  }

  async function register(name, email, password, password_confirmation) {
    const { data } = await api.post('/auth/register', {
      name,
      email,
      password,
      password_confirmation,
    })
    persist(data.user, data.token)
  }

  async function logout() {
    await api.post('/auth/logout')
    clear()
  }

  return { user, token, isAuthenticated, login, register, logout }
})
