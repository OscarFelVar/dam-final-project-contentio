import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const useContentStore = defineStore('content', () => {
  const items = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchItems() {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get('/content-items')
      items.value = data
    } catch (e) {
      error.value = 'Error al cargar los contenidos.'
    } finally {
      loading.value = false
    }
  }

  async function createItem(payload) {
    const { data } = await api.post('/content-items', payload)
    items.value.unshift(data)
    return data
  }

  async function updateItem(id, payload) {
    const { data } = await api.put(`/content-items/${id}`, payload)
    const index = items.value.findIndex(i => i.id === id)
    if (index !== -1) items.value[index] = data
    return data
  }

  async function deleteItem(id) {
    await api.delete(`/content-items/${id}`)
    items.value = items.value.filter(i => i.id !== id)
  }

  async function uploadMedia(contentItemId, file) {
    const form = new FormData()
    form.append('file', file)
    const { data } = await api.post(`/content-items/${contentItemId}/media`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    // Actualizar el item en la lista con el nuevo asset
    const item = items.value.find(i => i.id === contentItemId)
    if (item) item.media_assets = [...(item.media_assets ?? []), data]
    return data
  }

  async function deleteMedia(contentItemId, mediaId) {
    await api.delete(`/content-items/${contentItemId}/media/${mediaId}`)
    const item = items.value.find(i => i.id === contentItemId)
    if (item) item.media_assets = item.media_assets.filter(a => a.id !== mediaId)
  }

  return { items, loading, error, fetchItems, createItem, updateItem, deleteItem, uploadMedia, deleteMedia }
})
