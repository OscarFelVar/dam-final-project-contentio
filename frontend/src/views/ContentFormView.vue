<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useContentStore } from '../stores/content'
import AppLayout from '../components/AppLayout.vue'
import api from '../api/axios'

const route  = useRoute()
const router = useRouter()
const store  = useContentStore()

const isEdit = computed(() => route.params.id !== 'new' && !!route.params.id)

const form = ref({ title: '', type: '', clinic_id: '', deadline_at: '' })
const clinics       = ref([])
const selectedFiles = ref([])
const fileInput     = ref(null)
const submitting    = ref(false)
const uploadProgress = ref('')   // "Subiendo 2/3..."
const error         = ref(null)
const isDragging    = ref(false)
const createdItem   = ref(null)  // evita duplicados si falla el upload

const currentItem = computed(() => store.items.find(i => i.id === Number(route.params.id)))

onMounted(async () => {
  const { data } = await api.get('/clinics')
  clinics.value = data

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
  selectedFiles.value = [...selectedFiles.value, ...Array.from(e.target.files)]
  e.target.value = ''  // permite volver a seleccionar los mismos archivos
}

function onDrop(e) {
  isDragging.value = false
  selectedFiles.value = [...selectedFiles.value, ...Array.from(e.dataTransfer.files)]
}

function removeFile(index) {
  selectedFiles.value = selectedFiles.value.filter((_, i) => i !== index)
}

async function handleSubmit(sendForReview = false) {
  error.value      = null
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
      // Si ya se creó el item en un intento anterior, no crear otro
      if (!createdItem.value) {
        createdItem.value = await store.createItem({
          clinic_id:   form.value.clinic_id,
          title:       form.value.title,
          type:        form.value.type,
          deadline_at: form.value.deadline_at || null,
        })
      }
      item = createdItem.value
    }
    for (let i = 0; i < selectedFiles.value.length; i++) {
      uploadProgress.value = `Subiendo ${i + 1}/${selectedFiles.value.length}...`
      await store.uploadMedia(item.id, selectedFiles.value[i])
    }
    uploadProgress.value = ''
    if (sendForReview && !isEdit.value) {
      await store.transition(item.id, 'ready_for_review')
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

const typeOptions = [
  { value: 'reel',  label: 'Reel',  icon: 'video' },
  { value: 'post',  label: 'Post',  icon: 'image' },
  { value: 'story', label: 'Story', icon: 'mobile' },
]
</script>

<template>
  <AppLayout>

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 mb-8 text-xs text-[#A08A7A]">
      <RouterLink to="/content-items" class="hover:text-[#7D5A44] transition-colors">Contenidos</RouterLink>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
      <span class="text-[#4A342A]">{{ isEdit ? 'Editar contenido' : 'Nuevo contenido' }}</span>
    </div>

    <!-- Page heading -->
    <div class="mb-8">
      <p class="text-xs tracking-[0.12em] uppercase text-[#A08A7A] mb-1">
        {{ isEdit ? 'Edición' : 'Creación' }}
      </p>
      <h1 class="text-2xl text-[#4A342A] tracking-wide">
        {{ isEdit ? 'Editar contenido' : 'Nuevo contenido' }}
      </h1>
    </div>

    <!-- Form card -->
    <div class="max-w-2xl">
      <form @submit.prevent="handleSubmit" class="bg-white border border-[#E8E0D8] rounded-xl divide-y divide-[#F0EAE2]">

        <!-- Type selector -->
        <div class="p-6">
          <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-3">
            Tipo de contenido
          </label>
          <div class="grid grid-cols-3 gap-2 sm:gap-3">
            <button
              v-for="t in typeOptions"
              :key="t.value"
              type="button"
              @click="form.type = t.value"
              class="border rounded-xl p-4 text-center transition-all duration-150"
              :class="form.type === t.value
                ? 'border-[#4A342A] bg-[#F5F1EA]'
                : 'border-[#E8E0D8] hover:border-[#D7C9B8] hover:bg-[#FDFCFB]'"
            >
              <div
                class="w-8 h-8 mx-auto mb-2 rounded-lg flex items-center justify-center"
                :class="form.type === t.value ? 'bg-[#4A342A]' : 'bg-[#F5F1EA]'"
              >
                <!-- Video icon -->
                <svg v-if="t.icon === 'video'"
                  class="w-4 h-4"
                  :class="form.type === t.value ? 'text-white' : 'text-[#A08A7A]'"
                  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 10l4.553-2.069A1 1 0 0121 8.868V15.13a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <!-- Image icon -->
                <svg v-else-if="t.icon === 'image'"
                  class="w-4 h-4"
                  :class="form.type === t.value ? 'text-white' : 'text-[#A08A7A]'"
                  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <!-- Mobile icon -->
                <svg v-else
                  class="w-4 h-4"
                  :class="form.type === t.value ? 'text-white' : 'text-[#A08A7A]'"
                  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
              </div>
              <p class="text-xs" :class="form.type === t.value ? 'text-[#4A342A]' : 'text-[#7D5A44]'">
                {{ t.label }}
              </p>
            </button>
          </div>
        </div>

        <!-- Title -->
        <div class="p-6">
          <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">Título</label>
          <input
            v-model="form.title"
            type="text"
            required
            placeholder="Ej: Reel Primavera 2026"
            class="input-contentio"
          >
        </div>

        <!-- Clinic (only on create) -->
        <div v-if="!isEdit" class="p-6">
          <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">Clínica</label>
          <select v-model="form.clinic_id" required class="input-contentio appearance-none cursor-pointer">
            <option value="" disabled>Selecciona una clínica</option>
            <option v-for="c in clinics" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>

        <!-- Deadline -->
        <div class="p-6">
          <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">
            Fecha límite
            <span class="text-[#D7C9B8] normal-case tracking-normal">(opcional)</span>
          </label>
          <input v-model="form.deadline_at" type="date" class="input-contentio">
        </div>

        <!-- File upload -->
        <div class="p-6">
          <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">
            Archivo
            <span class="text-[#D7C9B8] normal-case tracking-normal">(opcional — puedes subirlo después)</span>
          </label>

          <div
            class="border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all duration-200"
            :class="isDragging
              ? 'border-[#7D5A44] bg-[#F5F1EA]'
              : 'border-[#D7C9B8] hover:border-[#7D5A44] hover:bg-[#FDFCFB]'"
            @dragover.prevent="isDragging = true"
            @dragleave="isDragging = false"
            @drop.prevent="onDrop"
            @click="fileInput?.click()"
          >
            <input
              ref="fileInput"
              type="file"
              accept="image/*,video/*"
              multiple
              class="hidden"
              @change="onFileChange"
            >

            <!-- Empty state -->
            <div v-if="!selectedFiles.length">
              <div class="w-10 h-10 bg-[#F5F1EA] rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5 text-[#A08A7A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
              </div>
              <p class="text-sm text-[#7D5A44] mb-1">Arrastra tus archivos aquí</p>
              <p class="text-xs text-[#D7C9B8]">o haz clic para seleccionar</p>
              <p class="text-[10px] text-[#D7C9B8] mt-3 tracking-wide">JPG · PNG · MP4 · máx. 500 MB por archivo</p>
            </div>

            <!-- Lista de archivos seleccionados -->
            <div v-else class="w-full space-y-2 text-left" @click.stop>
              <div
                v-for="(file, i) in selectedFiles"
                :key="i"
                class="flex items-center gap-3 bg-[#FDFCFB] border border-[#F0EAE2] rounded-lg px-3 py-2"
              >
                <div class="w-7 h-7 bg-[#F5F1EA] rounded-lg flex items-center justify-center shrink-0">
                  <svg class="w-3.5 h-3.5 text-[#7D5A44]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-[#4A342A] truncate">{{ file.name }}</p>
                  <p class="text-[10px] text-[#A08A7A]">{{ formatSize(file.size) }}</p>
                </div>
                <button
                  type="button"
                  @click="removeFile(i)"
                  class="text-[#D7C9B8] hover:text-red-400 transition-colors shrink-0"
                >
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
              <button
                type="button"
                @click="fileInput?.click()"
                class="text-xs text-[#A08A7A] hover:text-[#4A342A] transition-colors pt-1"
              >
                + Añadir más archivos
              </button>
            </div>
          </div>
        </div>

        <!-- Existing media (edit mode) -->
        <div v-if="isEdit && currentItem?.media_assets?.length" class="p-6">
          <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-3">
            Archivos subidos
          </label>
          <ul class="space-y-2">
            <li
              v-for="asset in currentItem.media_assets"
              :key="asset.id"
              class="flex items-center justify-between bg-[#FDFCFB] border border-[#F0EAE2] rounded-lg px-4 py-2.5"
            >
              <span class="text-sm text-[#4A342A] truncate">{{ asset.file_name }}</span>
              <button
                type="button"
                @click="handleDeleteMedia(asset)"
                class="text-[#D7C9B8] hover:text-red-500 transition-colors ml-3 flex-shrink-0"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </li>
          </ul>
        </div>

        <!-- Error -->
        <div v-if="error" class="px-6 py-4">
          <p class="text-xs text-red-500 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5">
            {{ error }}
          </p>
        </div>

        <!-- Actions -->
        <div class="p-6 bg-[#FDFCFB] flex flex-wrap items-center justify-end gap-3 rounded-b-xl">
          <RouterLink
            to="/content-items"
            class="px-5 py-2.5 border border-[#E8E0D8] text-[#A08A7A] rounded-lg text-sm hover:bg-white transition-colors"
          >
            Cancelar
          </RouterLink>

          <!-- Botón borrador (siempre visible) -->
          <button
            type="button"
            :disabled="submitting"
            @click="handleSubmit(false)"
            class="px-6 py-2.5 border border-[#4A342A] text-[#4A342A] hover:bg-[#F5F1EA] disabled:opacity-50 rounded-lg text-sm transition-colors"
          >
            {{ submitting ? 'Guardando...' : (isEdit ? 'Guardar cambios' : 'Guardar borrador') }}
          </button>

          <!-- Botón enviar a revisión (solo en creación) -->
          <button
            v-if="!isEdit"
            type="button"
            :disabled="submitting"
            @click="handleSubmit(true)"
            class="px-6 py-2.5 bg-[#4A342A] hover:bg-[#7D5A44] disabled:opacity-50 text-white rounded-lg text-sm transition-colors flex items-center gap-2"
          >
            <svg v-if="!submitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            {{ uploadProgress || (submitting ? 'Enviando...' : 'Guardar y enviar a revisión') }}
          </button>
        </div>

      </form>
    </div>

  </AppLayout>
</template>
