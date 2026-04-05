<script setup>
import { computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useContentStore } from '../stores/content'
import AppLayout from '../components/AppLayout.vue'

const auth  = useAuthStore()
const store = useContentStore()

onMounted(() => store.fetchItems())

const isAgency = computed(() => auth.user?.role === 'agency')
const isClinic = computed(() => auth.user?.role === 'clinic')

const total         = computed(() => store.items.length)
const pending       = computed(() => store.items.filter(i => i.status === 'ready_for_review').length)
const approved      = computed(() => store.items.filter(i => i.status === 'approved').length)
const rejected      = computed(() => store.items.filter(i => i.status === 'rejected').length)
const inCorrection  = computed(() => store.items.filter(i => i.status === 'in_correction').length)
const drafts        = computed(() => store.items.filter(i => i.status === 'draft').length)
const recent        = computed(() => [...store.items].reverse().slice(0, 5))

// Items que requieren acción del usuario actual
const actionRequired = computed(() => {
  if (isAgency.value)  return store.items.filter(i => i.status === 'in_correction' || i.status === 'draft')
  if (isClinic.value)  return store.items.filter(i => i.status === 'ready_for_review')
  return []
})

// Distribución porcentual para la barra de estado
const distribution = computed(() => {
  if (!total.value) return []
  return [
    { label: 'Borrador',      count: drafts.value,       pct: (drafts.value / total.value) * 100,       color: 'bg-[#D7C9B8]' },
    { label: 'En revisión',   count: pending.value,      pct: (pending.value / total.value) * 100,      color: 'bg-amber-400' },
    { label: 'En corrección', count: inCorrection.value, pct: (inCorrection.value / total.value) * 100, color: 'bg-orange-400' },
    { label: 'Aprobado',      count: approved.value,     pct: (approved.value / total.value) * 100,     color: 'bg-emerald-500' },
    { label: 'Rechazado',     count: rejected.value,     pct: (rejected.value / total.value) * 100,     color: 'bg-red-400' },
  ].filter(d => d.count > 0)
})

function statusLabel(s) {
  return { draft: 'Borrador', ready_for_review: 'En revisión', in_correction: 'En corrección', approved: 'Aprobado', rejected: 'Rechazado' }[s] ?? s
}
function statusDot(s) {
  return { draft: 'bg-[#D7C9B8]', ready_for_review: 'bg-amber-400', in_correction: 'bg-orange-400', approved: 'bg-emerald-400', rejected: 'bg-red-400' }[s] ?? 'bg-[#D7C9B8]'
}
function statusBadge(s) {
  return { draft: 'bg-[#F5F1EA] text-[#7D5A44] border-[#D7C9B8]', ready_for_review: 'bg-amber-50 text-amber-700 border-amber-200', in_correction: 'bg-orange-50 text-orange-700 border-orange-200', approved: 'bg-emerald-50 text-emerald-700 border-emerald-200', rejected: 'bg-red-50 text-red-700 border-red-200' }[s] ?? 'bg-[#F5F1EA] text-[#A08A7A] border-[#D7C9B8]'
}
function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' })
}
</script>

<template>
  <AppLayout>

    <!-- Top bar -->
    <div class="flex items-center justify-between mb-10">
      <div>
        <p class="text-xs tracking-[0.12em] uppercase text-[#A08A7A] mb-1">Panel de control</p>
        <h1 class="text-2xl text-[#4A342A] tracking-wide">
          Bienvenido, {{ auth.user?.name?.split(' ')[0] }}
        </h1>
      </div>
      <RouterLink v-if="isAgency" to="/content-items/new">
        <button class="flex items-center gap-2 bg-[#4A342A] hover:bg-[#7D5A44] text-white rounded-lg px-5 py-2.5 text-sm transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nuevo contenido
        </button>
      </RouterLink>
    </div>

    <!-- Alerta: acción requerida -->
    <div
      v-if="actionRequired.length"
      class="mb-8 bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex items-center justify-between gap-4"
    >
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
          <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
        </div>
        <div>
          <p class="text-sm text-amber-800 font-medium">
            {{ actionRequired.length }} {{ actionRequired.length === 1 ? 'contenido requiere' : 'contenidos requieren' }} tu atención
          </p>
          <p class="text-xs text-amber-600 mt-0.5">
            {{ isAgency ? 'Borradores pendientes de envío o correcciones solicitadas' : 'Contenidos pendientes de revisión' }}
          </p>
        </div>
      </div>
      <RouterLink
        to="/content-items"
        class="text-xs text-amber-700 hover:text-amber-900 font-medium shrink-0 transition-colors"
      >
        Revisar →
      </RouterLink>
    </div>

    <!-- Metric cards -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-10">

      <div class="card-lift bg-white border border-[#E8E0D8] rounded-xl p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-9 h-9 bg-[#F5F1EA] rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-[#7D5A44]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
          </div>
          <span class="text-xs text-[#A08A7A]">Total</span>
        </div>
        <p class="text-3xl text-[#4A342A] mb-1">{{ total }}</p>
        <p class="text-xs text-[#A08A7A] tracking-wide">Contenidos</p>
      </div>

      <div class="card-lift bg-white border border-[#E8E0D8] rounded-xl p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <span class="text-xs text-[#A08A7A]">Acción</span>
        </div>
        <p class="text-3xl text-[#4A342A] mb-1">{{ pending }}</p>
        <p class="text-xs text-[#A08A7A] tracking-wide">Pendientes de revisión</p>
      </div>

      <div class="card-lift bg-white border border-[#E8E0D8] rounded-xl p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <span class="text-xs text-[#A08A7A]">Total</span>
        </div>
        <p class="text-3xl text-emerald-600 mb-1">{{ approved }}</p>
        <p class="text-xs text-[#A08A7A] tracking-wide">Aprobados</p>
      </div>

      <div class="card-lift bg-white border border-[#E8E0D8] rounded-xl p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-9 h-9 bg-red-50 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <span class="text-xs text-[#A08A7A]">Total</span>
        </div>
        <p class="text-3xl text-red-500 mb-1">{{ rejected }}</p>
        <p class="text-xs text-[#A08A7A] tracking-wide">Rechazados</p>
      </div>

    </div>

    <!-- Distribución por estado -->
    <div v-if="total > 0" class="bg-white border border-[#E8E0D8] rounded-xl p-6 mb-6">
      <h2 class="text-sm text-[#4A342A] mb-4">Distribución por estado</h2>

      <!-- Barra segmentada -->
      <div class="flex h-2 rounded-full overflow-hidden gap-0.5 mb-4">
        <div
          v-for="d in distribution"
          :key="d.label"
          :class="['rounded-full transition-all', d.color]"
          :style="{ width: d.pct + '%' }"
        />
      </div>

      <!-- Leyenda -->
      <div class="flex flex-wrap gap-x-5 gap-y-2">
        <div v-for="d in distribution" :key="d.label" class="flex items-center gap-1.5">
          <div :class="['w-2 h-2 rounded-full shrink-0', d.color]" />
          <span class="text-xs text-[#7D5A44]">{{ d.label }}</span>
          <span class="text-xs text-[#A08A7A]">{{ d.count }}</span>
        </div>
      </div>
    </div>

    <!-- Recent activity -->
    <div class="bg-white border border-[#E8E0D8] rounded-xl">
      <div class="px-6 py-5 border-b border-[#F0EAE2] flex items-center justify-between">
        <h2 class="text-sm tracking-wide text-[#4A342A]">Actividad reciente</h2>
        <RouterLink to="/content-items" class="text-xs text-[#7D5A44] hover:text-[#4A342A] transition-colors">
          Ver todos →
        </RouterLink>
      </div>

      <!-- Loading -->
      <div v-if="store.loading" class="px-6 py-10 text-center text-[#D7C9B8] text-sm">
        Cargando...
      </div>

      <!-- Empty -->
      <div v-else-if="recent.length === 0" class="px-6 py-10 text-center">
        <p class="text-sm text-[#A08A7A]">Aún no hay contenidos.</p>
        <RouterLink to="/content-items/new" class="text-xs text-[#7D5A44] hover:underline mt-1 inline-block">
          Crea el primero →
        </RouterLink>
      </div>

      <!-- List -->
      <div v-else class="divide-y divide-[#F5F1EA]">
        <div
          v-for="item in recent"
          :key="item.id"
          class="px-6 py-4 flex items-center gap-4"
        >
          <div class="flex-1 min-w-0">
            <p class="text-sm text-[#4A342A] truncate">{{ item.title }}</p>
            <p class="text-xs text-[#D7C9B8] mt-0.5">
              {{ item.clinic?.name }}
              <span v-if="item.updated_at"> · {{ formatDate(item.updated_at) }}</span>
            </p>
          </div>
          <span :class="['text-[10px] border rounded-full px-2 py-0.5 shrink-0', statusBadge(item.status)]">
            {{ statusLabel(item.status) }}
          </span>
          <RouterLink
            :to="`/content-items/${item.id}/review`"
            class="text-xs text-[#A08A7A] hover:text-[#7D5A44] transition-colors flex-shrink-0"
          >
            Ver
          </RouterLink>
        </div>
      </div>
    </div>

  </AppLayout>
</template>
