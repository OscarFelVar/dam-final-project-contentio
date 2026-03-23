<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useContentStore } from '../stores/content'
import AppLayout from '../components/AppLayout.vue'

const auth   = useAuthStore()
const store  = useContentStore()
const router = useRouter()

const activeFilter = ref('all')

onMounted(() => store.fetchItems())

const filters = [
  { key: 'all',              label: 'Todos' },
  { key: 'draft',            label: 'Borrador' },
  { key: 'ready_for_review', label: 'En revisión' },
  { key: 'in_correction',    label: 'En corrección' },
  { key: 'approved',         label: 'Aprobado' },
  { key: 'rejected',         label: 'Rechazado' },
]

const filtered = computed(() =>
  activeFilter.value === 'all'
    ? store.items
    : store.items.filter(i => i.status === activeFilter.value)
)

function filterCount(key) {
  return key === 'all' ? store.items.length : store.items.filter(i => i.status === key).length
}

function statusBadgeClass(status) {
  return {
    draft:             'bg-[#F5F1EA] text-[#7D5A44] border-[#D7C9B8]',
    ready_for_review:  'bg-amber-50 text-amber-700 border-amber-200',
    in_correction:     'bg-orange-50 text-orange-700 border-orange-200',
    approved:          'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected:          'bg-red-50 text-red-700 border-red-200',
  }[status] ?? 'bg-[#F5F1EA] text-[#A08A7A] border-[#D7C9B8]'
}

function statusLabel(s) {
  return { draft: 'Borrador', ready_for_review: 'En revisión', in_correction: 'En corrección', approved: '✓ Aprobado', rejected: '✗ Rechazado' }[s] ?? s
}

function typeBadgeClass(type) {
  return 'bg-white/90 text-[#7D5A44] border-[#E8E0D8]'
}

function typeLabel(t) {
  return { reel: 'Reel', post: 'Post', story: 'Story' }[t] ?? t
}

function cardGradient(i) {
  const gradients = [
    'from-[#e6dac8] to-[#D7C9B8]',
    'from-[#F5F1EA] to-[#e6dac8]',
    'from-[#e6dac8] to-[#c2b8b0]',
    'from-[#D7C9B8] to-[#c2b8b0]',
    'from-[#F5F1EA] to-[#D7C9B8]',
  ]
  return gradients[i % gradients.length]
}

function isVideo(item) {
  return item.media_assets?.some(a => a.file_name?.match(/\.(mp4|mov|avi|webm)$/i))
}

async function handleDelete(item) {
  if (!confirm(`¿Eliminar "${item.title}"?`)) return
  await store.deleteItem(item.id)
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>

<template>
  <AppLayout>

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <p class="text-xs tracking-[0.12em] uppercase text-[#A08A7A] mb-1">Gestión</p>
        <h1 class="text-2xl text-[#4A342A] tracking-wide">Contenidos</h1>
      </div>
      <RouterLink to="/content-items/new">
        <button class="flex items-center gap-2 bg-[#4A342A] hover:bg-[#7D5A44] text-white rounded-lg px-5 py-2.5 text-sm transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nuevo contenido
        </button>
      </RouterLink>
    </div>

    <!-- Filter tabs -->
    <div class="flex items-center gap-1 mb-6 flex-wrap">
      <button
        v-for="f in filters"
        :key="f.key"
        @click="activeFilter = f.key"
        class="px-4 py-2 text-xs rounded-lg transition-colors"
        :class="activeFilter === f.key
          ? 'bg-[#4A342A] text-white'
          : 'text-[#A08A7A] hover:bg-[#F5F1EA]'"
      >
        {{ f.label }} ({{ filterCount(f.key) }})
      </button>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="text-center py-20 text-[#D7C9B8] text-sm">
      Cargando...
    </div>

    <!-- Error -->
    <div v-else-if="store.error" class="text-center py-20 text-red-500 text-sm">
      {{ store.error }}
    </div>

    <!-- Empty state -->
    <div v-else-if="filtered.length === 0" class="text-center py-20">
      <div class="w-14 h-14 bg-[#F5F1EA] rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-6 h-6 text-[#D7C9B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
        </svg>
      </div>
      <p class="text-sm text-[#A08A7A]">No hay contenidos en esta categoría.</p>
      <RouterLink to="/content-items/new" class="text-xs text-[#7D5A44] hover:underline mt-2 inline-block">
        Crear nuevo contenido →
      </RouterLink>
    </div>

    <!-- Content grid -->
    <div v-else class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <RouterLink
        v-for="(item, idx) in filtered"
        :key="item.id"
        :to="`/content-items/${item.id}`"
        class="card-lift bg-white border border-[#E8E0D8] rounded-xl overflow-hidden"
      >
        <!-- Thumbnail -->
        <div class="relative">
          <div
            :class="['h-40 bg-gradient-to-br flex items-center justify-center', cardGradient(idx)]"
          >
            <!-- Video icon -->
            <svg v-if="isVideo(item)" class="w-8 h-8 text-[#A08A7A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M15 10l4.553-2.069A1 1 0 0121 8.868V15.13a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <!-- Image icon -->
            <svg v-else class="w-8 h-8 text-[#A08A7A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>

          <!-- Type badge -->
          <div class="absolute top-2.5 right-2.5">
            <span :class="['text-[10px] border rounded-full px-2 py-0.5', typeBadgeClass(item.type)]">
              {{ typeLabel(item.type) }}
            </span>
          </div>

          <!-- Status badge -->
          <div class="absolute bottom-2.5 left-2.5">
            <span :class="['text-[10px] border rounded-full px-2.5 py-1', statusBadgeClass(item.status)]">
              {{ statusLabel(item.status) }}
            </span>
          </div>
        </div>

        <!-- Info -->
        <div class="p-3.5">
          <p class="text-xs text-[#4A342A] truncate mb-1">{{ item.title }}</p>
          <div class="flex items-center justify-between">
            <p class="text-[10px] text-[#D7C9B8] truncate">{{ item.clinic?.name }}</p>
            <p v-if="item.created_at" class="text-[10px] text-[#D7C9B8] flex-shrink-0 ml-2">
              {{ formatDate(item.created_at) }}
            </p>
          </div>
        </div>
      </RouterLink>
    </div>

  </AppLayout>
</template>
