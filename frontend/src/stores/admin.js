import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '../api/axios'

export const useAdminStore = defineStore('admin', () => {
  const stats   = ref(null)
  const users   = ref([])
  const loading = ref(false)

  async function fetchStats() {
    const { data } = await api.get('/admin/stats')
    stats.value = data
  }

  async function fetchUsers() {
    loading.value = true
    try {
      const { data } = await api.get('/admin/users')
      users.value = data
    } finally {
      loading.value = false
    }
  }

  async function createUser(payload) {
    const { data } = await api.post('/admin/users', payload)
    users.value.push(data)
    return data
  }

  async function deleteUser(id) {
    await api.delete(`/admin/users/${id}`)
    users.value = users.value.filter(u => u.id !== id)
  }

  return { stats, users, loading, fetchStats, fetchUsers, createUser, deleteUser }
})
