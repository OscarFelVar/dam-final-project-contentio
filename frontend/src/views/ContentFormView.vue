<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="max-w-2xl mx-auto px-4 py-4 flex items-center gap-3">
        <RouterLink to="/content-items" class="text-gray-400 hover:text-gray-600">←</RouterLink>
        <h1 class="text-xl font-bold text-gray-800">{{ isEdit ? 'Editar contenido' : 'Nuevo contenido' }}</h1>
      </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-8">
      <form @submit.prevent="handleSubmit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">

        <!-- Título -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
          <input
            v-model="form.title"
            type="text"
            placeholder="Ej: Post semana 1 - Tratamiento facial"
            required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Tipo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
          <select
            v-model="form.type"
            required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="" disabled>Selecciona un tipo</option>
            <option value="post">Post</option>
            <option value="reel">Reel</option>
            <option value="story">Story</option>
          </select>
        </div>

        <!-- Clínica (solo en creación) -->
        <div v-if="!isEdit">
          <label class="block text-sm font-medium text-gray-700 mb-1">Clínica</label>
          <select
            v-model="form.clinic_id"
            required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="" disabled>Selecciona una clínica</option>
            <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
              {{ clinic.name }}
            </option>
          </select>
        </div>

        <!-- Fecha límite -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Fecha límite <span class="text-gray-400">(opcional)</span></label>
          <input
            v-model="form.deadline_at"
            type="date"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Archivo (opcional) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Archivo <span class="text-gray-400">(opcional — puedes subirlo después)</span>
          </label>
          <input
            ref="fileInput"
            type="file"
            accept="image/*,video/*"
            @change="onFileChange"
            class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
          />
          <p v-if="selectedFile" class="text-xs text-gray-400 mt-1">{{ selectedFile.name }} ({{ formatSize(selectedFile.size) }})</p>
        </div>

        <!-- Archivos existentes (en edición) -->
        <div v-if="isEdit && currentItem?.media_assets?.length">
          <label class="block text-sm font-medium text-gray-700 mb-2">Archivos subidos</label>
          <ul class="space-y-2">
            <li
              v-for="asset in currentItem.media_assets"
              :key="asset.id"
              class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2 text-sm"
            >
              <span class="text-gray-600 truncate">{{ asset.file_name }}</span>
              <button
                type="button"
                @click="handleDeleteMedia(asset)"
                class="text-red-500 hover:underline text-xs ml-3 shrink-0"
              >
                Eliminar
              </button>
            </li>
          </ul>
        </div>

        <!-- Error -->
        <p v-if="error" class="text-sm text-red-500">{{ error }}</p>

        <!-- Botones -->
        <div class="flex gap-3 pt-2">
          <button
            type="submit"
            :disabled="submitting"
            class="flex-1 bg-blue-600 text-white text-sm py-2 rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
          >
            {{ submitting ? 'Guardando...' : (isEdit ? 'Guardar cambios' : 'Crear contenido') }}
          </button>
          <RouterLink
            to="/content-items"
            class="flex-1 text-center border border-gray-300 text-sm py-2 rounded-lg hover:bg-gray-50 transition"
          >
            Cancelar
          </RouterLink>
        </div>

      </form>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useContentStore } from '../stores/content'
import api from '../api/axios'

const route  = useRoute()
const router = useRouter()
const store  = useContentStore()

const isEdit = computed(() => route.params.id !== 'new' && !!route.params.id)

const form = ref({ title: '', type: '', clinic_id: '', deadline_at: '' })
const clinics     = ref([])
const selectedFile = ref(null)
const fileInput   = ref(null)
const submitting  = ref(false)
const error       = ref(null)

const currentItem = computed(() => store.items.find(i => i.id === Number(route.params.id)))

onMounted(async () => {
  // Cargar clínicas del usuario
  const { data } = await api.get('/clinics')
  clinics.value = data

  // Si es edición, cargar datos del item
  if (isEdit.value) {
    if (store.items.length === 0) await store.fetchItems()
    const item = currentItem.value
    if (item) {
      form.value.title       = item.title
      form.value.type        = item.type
      form.value.deadline_at = item.deadline_at ? item.deadline_at.substring(0, 10) : ''
    }
  }
})

function onFileChange(e) {
  selectedFile.value = e.target.files[0] ?? null
}

async function handleSubmit() {
  error.value   = null
  submitting.value = true

  try {
    let item

    if (isEdit.value) {
      item = await store.updateItem(Number(route.params.id), {
        title:       form.value.title,
        type:        form.value.type,
        deadline_at: form.value.deadline_at || null,
      })
    } else {
      item = await store.createItem({
        clinic_id:   form.value.clinic_id,
        title:       form.value.title,
        type:        form.value.type,
        deadline_at: form.value.deadline_at || null,
      })
    }

    // Si hay archivo seleccionado, subirlo inmediatamente
    if (selectedFile.value) {
      await store.uploadMedia(item.id, selectedFile.value)
    }

    router.push('/content-items')
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Error al guardar. Revisa los datos.'
  } finally {
    submitting.value = false
  }
}

async function handleDeleteMedia(asset) {
  if (!confirm(`¿Eliminar el archivo "${asset.file_name}"?`)) return
  await store.deleteMedia(Number(route.params.id), asset.id)
}

function formatSize(bytes) {
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}
</script>
