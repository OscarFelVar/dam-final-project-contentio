import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '../api/axios'

export const useClinicStore = defineStore('clinic', () => {
  const clinics = ref([])
  const loading = ref(false)

  async function fetchClinics() {
    loading.value = true
    try {
      const { data } = await api.get('/clinics')
      clinics.value = data
    } finally {
      loading.value = false
    }
  }

  async function createClinic(payload) {
    const { data } = await api.post('/clinics', payload)
    clinics.value.push(data)
    clinics.value.sort((a, b) => a.name.localeCompare(b.name))
    return data
  }

  async function updateClinic(id, payload) {
    const { data } = await api.put(`/clinics/${id}`, payload)
    const idx = clinics.value.findIndex(c => c.id === id)
    if (idx !== -1) clinics.value[idx] = data
    clinics.value.sort((a, b) => a.name.localeCompare(b.name))
    return data
  }

  async function deleteClinic(id) {
    await api.delete(`/clinics/${id}`)
    clinics.value = clinics.value.filter(c => c.id !== id)
  }

  async function fetchClinicUsers(clinicId) {
    const { data } = await api.get(`/clinics/${clinicId}/users`)
    return data
  }

  async function createClinicUser(clinicId, payload) {
    const { data } = await api.post(`/clinics/${clinicId}/users`, payload)
    return data
  }

  async function deleteClinicUser(clinicId, userId) {
    await api.delete(`/clinics/${clinicId}/users/${userId}`)
  }

  return {
    clinics, loading,
    fetchClinics, createClinic, updateClinic, deleteClinic,
    fetchClinicUsers, createClinicUser, deleteClinicUser,
  }
})
