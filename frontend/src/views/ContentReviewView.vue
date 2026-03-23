<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useContentStore } from '../stores/content'
import AppLayout from '../components/AppLayout.vue'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()
const store  = useContentStore()

const item     = ref(null)
const comments = ref([])
const loading  = ref(true)
const error    = ref(null)
const newComment = ref('')
const submitting = ref(false)
const transitioning = ref(false)

const id = Number(route.params.id)

const isAgency = computed(() => auth.user?.role === 'agency')
const isClinic = computed(() => auth.user?.role === 'clinic')

// Transiciones disponibles según rol y estado actual
const availableTransitions = computed(() => {
  if (!item.value) return []
  const s = item.value.status
  if (isAgency.value) {
    if (s === 'draft' || s === 'in_correction') return [{ status: 'ready_for_review', label: 'Enviar a revisión', style: 'primary' }]
  }
  if (isClinic.value) {
    if (s === 'ready_for_review') return [
      { status: 'approved',    label: 'Aprobar',           style: 'success' },
      { status: 'in_correction', label: 'Pedir corrección', style: 'warning' },
      { status: 'rejected',    label: 'Rechazar',          style: 'danger' },
    ]
  }
  return []
})

onMounted(async () => {
  try {
    const [itemData, commentsData] = await Promise.all([
      store.fetchItem(id),
      store.fetchComments(id),
    ])
    item.value = itemData
    comments.value = commentsData
  } catch (e) {
    error.value = 'No se pudo cargar el contenido.'
  } finally {
    loading.value = false
  }
})

async function submitComment() {
  if (!newComment.value.trim()) return
  submitting.value = true
  try {
    const comment = await store.addComment(id, newComment.value.trim())
    comments.value.push(comment)
    newComment.value = ''
  } finally {
    submitting.value = false
  }
}

async function removeComment(commentId) {
  if (!confirm('¿Eliminar este comentario?')) return
  await store.deleteComment(id, commentId)
  comments.value = comments.value.filter(c => c.id !== commentId)
}

async function doTransition(status) {
  transitioning.value = true
  try {
    item.value = await store.transition(id, status)
  } catch (e) {
    alert('No se pudo realizar la transición.')
  } finally {
    transitioning.value = false
  }
}

function statusLabel(s) {
  return {
    draft:             'Borrador',
    ready_for_review:  'En revisión',
    in_correction:     'En corrección',
    approved:          'Aprobado',
    rejected:          'Rechazado',
  }[s] ?? s
}

function statusBadgeClass(s) {
  return {
    draft:             'bg-[#F5F1EA] text-[#7D5A44] border-[#D7C9B8]',
    ready_for_review:  'bg-amber-50 text-amber-700 border-amber-200',
    in_correction:     'bg-orange-50 text-orange-700 border-orange-200',
    approved:          'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected:          'bg-red-50 text-red-700 border-red-200',
  }[s] ?? 'bg-[#F5F1EA] text-[#A08A7A] border-[#D7C9B8]'
}

function transitionBtnClass(style) {
  return {
    primary: 'bg-[#4A342A] hover:bg-[#7D5A44] text-white',
    success: 'bg-emerald-600 hover:bg-emerald-700 text-white',
    warning: 'bg-orange-500 hover:bg-orange-600 text-white',
    danger:  'bg-red-600 hover:bg-red-700 text-white',
  }[style]
}

function isImage(asset) {
  return asset.mime_type?.startsWith('image/')
}

function isVideo(asset) {
  return asset.mime_type?.startsWith('video/')
}

function assetUrl(asset) {
  return `http://localhost:8080/storage/${asset.file_path}`
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <AppLayout>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-20 text-[#D7C9B8] text-sm">Cargando...</div>

    <!-- Error -->
    <div v-else-if="error" class="text-center py-20 text-red-500 text-sm">{{ error }}</div>

    <template v-else-if="item">

      <!-- Header -->
      <div class="flex items-start justify-between mb-8 gap-4">
        <div>
          <button @click="router.back()" class="text-xs text-[#A08A7A] hover:text-[#4A342A] mb-2 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver
          </button>
          <p class="text-xs tracking-[0.12em] uppercase text-[#A08A7A] mb-1">Revisión</p>
          <h1 class="text-2xl text-[#4A342A] tracking-wide">{{ item.title }}</h1>
          <p class="text-xs text-[#A08A7A] mt-1">{{ item.clinic?.name }}</p>
        </div>

        <!-- Estado + transiciones -->
        <div class="flex flex-col items-end gap-2 shrink-0">
          <span :class="['text-xs border rounded-full px-3 py-1', statusBadgeClass(item.status)]">
            {{ statusLabel(item.status) }}
          </span>
          <div class="flex gap-2 flex-wrap justify-end">
            <button
              v-for="t in availableTransitions"
              :key="t.status"
              @click="doTransition(t.status)"
              :disabled="transitioning"
              :class="['text-xs rounded-lg px-4 py-2 transition-colors disabled:opacity-50', transitionBtnClass(t.style)]"
            >
              {{ t.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Contenido principal: assets + comentarios -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Assets (2/3) -->
        <div class="lg:col-span-2 space-y-4">
          <h2 class="text-sm text-[#4A342A] mb-3">Archivos</h2>

          <div v-if="!item.media_assets?.length" class="bg-[#F5F1EA] rounded-xl p-10 text-center text-sm text-[#A08A7A]">
            Sin archivos adjuntos
          </div>

          <div v-for="asset in item.media_assets" :key="asset.id"
            class="bg-white border border-[#E8E0D8] rounded-xl overflow-hidden">

            <!-- Imagen -->
            <img
              v-if="isImage(asset)"
              :src="assetUrl(asset)"
              :alt="asset.file_name"
              class="w-full object-contain max-h-[500px] bg-[#F5F1EA]"
            />

            <!-- Vídeo -->
            <video
              v-else-if="isVideo(asset)"
              :src="assetUrl(asset)"
              controls
              class="w-full max-h-[500px] bg-black"
            />

            <!-- Otro tipo -->
            <div v-else class="p-6 flex items-center gap-3">
              <svg class="w-8 h-8 text-[#D7C9B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <span class="text-sm text-[#7D5A44]">{{ asset.file_name }}</span>
            </div>

            <!-- Pie del asset -->
            <div class="px-4 py-2 border-t border-[#F5F1EA] flex items-center justify-between">
              <span class="text-xs text-[#A08A7A] truncate">{{ asset.file_name }}</span>
              <span class="text-xs text-[#D7C9B8] ml-2 shrink-0">
                {{ (asset.size / 1024).toFixed(0) }} KB
              </span>
            </div>
          </div>

          <!-- Historial de estados -->
          <div v-if="item.state_histories?.length" class="mt-6">
            <h2 class="text-sm text-[#4A342A] mb-3">Historial de estados</h2>
            <div class="bg-white border border-[#E8E0D8] rounded-xl divide-y divide-[#F5F1EA]">
              <div v-for="h in item.state_histories" :key="h.id" class="px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span :class="['text-[10px] border rounded-full px-2 py-0.5', statusBadgeClass(h.to_status)]">
                    {{ statusLabel(h.to_status) }}
                  </span>
                  <span class="text-xs text-[#A08A7A]">por {{ h.user?.name ?? '—' }}</span>
                </div>
                <span class="text-[10px] text-[#D7C9B8]">{{ formatDate(h.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Comentarios (1/3) -->
        <div class="flex flex-col gap-4">
          <h2 class="text-sm text-[#4A342A]">Comentarios ({{ comments.length }})</h2>

          <!-- Lista -->
          <div class="flex flex-col gap-3 max-h-[500px] overflow-y-auto pr-1">
            <div v-if="!comments.length" class="text-xs text-[#D7C9B8] text-center py-6">
              Sin comentarios aún
            </div>
            <div
              v-for="c in comments"
              :key="c.id"
              class="bg-white border border-[#E8E0D8] rounded-xl p-3"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-[#4A342A]">{{ c.user?.name }}</span>
                <div class="flex items-center gap-2">
                  <span class="text-[10px] text-[#D7C9B8]">{{ formatDate(c.created_at) }}</span>
                  <button
                    v-if="c.user?.id === auth.user?.id"
                    @click="removeComment(c.id)"
                    class="text-[#D7C9B8] hover:text-red-400 transition-colors"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </div>
              <p class="text-xs text-[#7D5A44] leading-relaxed">{{ c.body }}</p>
            </div>
          </div>

          <!-- Formulario nuevo comentario -->
          <div class="bg-white border border-[#E8E0D8] rounded-xl p-3">
            <textarea
              v-model="newComment"
              rows="3"
              placeholder="Escribe un comentario..."
              class="w-full text-xs text-[#4A342A] placeholder-[#D7C9B8] bg-transparent resize-none outline-none"
            />
            <div class="flex justify-end mt-2">
              <button
                @click="submitComment"
                :disabled="submitting || !newComment.trim()"
                class="text-xs bg-[#4A342A] hover:bg-[#7D5A44] text-white rounded-lg px-4 py-1.5 transition-colors disabled:opacity-50"
              >
                Enviar
              </button>
            </div>
          </div>
        </div>

      </div>
    </template>

  </AppLayout>
</template>
