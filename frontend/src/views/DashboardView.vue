<script setup>
import { computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useContentStore } from '../stores/content'
import AppLayout from '../components/AppLayout.vue'

const auth  = useAuthStore()
const store = useContentStore()

onMounted(() => store.fetchItems())

const total    = computed(() => store.items.length)
const pending  = computed(() => store.items.filter(i => i.status === 'ready_for_review').length)
const approved = computed(() => store.items.filter(i => i.status === 'approved').length)
const rejected = computed(() => store.items.filter(i => i.status === 'rejected').length)
const recent   = computed(() => [...store.items].reverse().slice(0, 5))

function statusLabel(s) {
  return { draft: 'Borrador', ready_for_review: 'En revisión', in_correction: 'En corrección', approved: 'Aprobado', rejected: 'Rechazado' }[s] ?? s
}
function statusDot(s) {
  return { draft: 'bg-[#D7C9B8]', ready_for_review: 'bg-amber-400', in_correction: 'bg-orange-400', approved: 'bg-emerald-400', rejected: 'bg-red-400' }[s] ?? 'bg-[#D7C9B8]'
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
      <RouterLink to="/content-items/new">
        <button class="flex items-center gap-2 bg-[#4A342A] hover:bg-[#7D5A44] text-white rounded-lg px-5 py-2.5 text-sm transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nuevo contenido
        </button>
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
          <div :class="['w-2 h-2 rounded-full flex-shrink-0 mt-0.5', statusDot(item.status)]"></div>
          <div class="flex-1 min-w-0">
            <p class="text-sm text-[#4A342A] truncate">{{ item.title }}</p>
            <p class="text-xs text-[#D7C9B8] mt-0.5">
              {{ item.clinic?.name }} · {{ statusLabel(item.status) }}
              <span v-if="item.updated_at"> · {{ formatDate(item.updated_at) }}</span>
            </p>
          </div>
          <RouterLink
            :to="`/content-items/${item.id}`"
            class="text-xs text-[#A08A7A] hover:text-[#7D5A44] transition-colors flex-shrink-0"
          >
            Ver
          </RouterLink>
        </div>
      </div>
    </div>

  </AppLayout>
</template>
