<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Contentio</h1>
        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-500">{{ auth.user?.name }}</span>
          <button @click="handleLogout" class="text-sm text-red-500 hover:underline">Cerrar sesión</button>
        </div>
      </div>
    </header>

    <!-- Main -->
    <main class="max-w-5xl mx-auto px-4 py-8">

      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-700">Contenidos</h2>
        <RouterLink
          to="/content-items/new"
          class="bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition"
        >
          + Nuevo contenido
        </RouterLink>
      </div>

      <!-- Loading -->
      <div v-if="store.loading" class="text-center text-gray-400 py-20">Cargando...</div>

      <!-- Error -->
      <div v-else-if="store.error" class="text-center text-red-500 py-20">{{ store.error }}</div>

      <!-- Empty -->
      <div v-else-if="store.items.length === 0" class="text-center text-gray-400 py-20">
        <p class="text-lg">No hay contenidos todavía.</p>
        <p class="text-sm mt-1">Crea el primero con el botón <strong>+ Nuevo contenido</strong>.</p>
      </div>

      <!-- Lista -->
      <div v-else class="grid gap-4">
        <div
          v-for="item in store.items"
          :key="item.id"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-start justify-between gap-4"
        >
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span :class="typeBadge(item.type)" class="text-xs font-medium px-2 py-0.5 rounded-full uppercase">
                {{ item.type }}
              </span>
              <span :class="statusBadge(item.status)" class="text-xs font-medium px-2 py-0.5 rounded-full">
                {{ statusLabel(item.status) }}
              </span>
            </div>
            <h3 class="font-semibold text-gray-800 truncate">{{ item.title }}</h3>
            <p class="text-xs text-gray-400 mt-1">
              Clínica: {{ item.clinic?.name }}
              <span v-if="item.deadline_at"> · Entrega: {{ formatDate(item.deadline_at) }}</span>
              <span v-if="item.media_assets?.length"> · {{ item.media_assets.length }} archivo(s)</span>
            </p>
          </div>

          <div class="flex gap-2 shrink-0">
            <RouterLink
              :to="`/content-items/${item.id}`"
              class="text-sm text-blue-600 hover:underline"
            >
              Editar
            </RouterLink>
            <button
              v-if="item.status === 'draft'"
              @click="handleDelete(item)"
              class="text-sm text-red-500 hover:underline"
            >
              Eliminar
            </button>
          </div>
        </div>
      </div>

    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useContentStore } from '../stores/content'

const auth  = useAuthStore()
const store = useContentStore()
const router = useRouter()

onMounted(() => store.fetchItems())

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}

async function handleDelete(item) {
  if (!confirm(`¿Eliminar "${item.title}"?`)) return
  await store.deleteItem(item.id)
}

// ─── Helpers visuales ────────────────────────────────────────────────────────

function typeBadge(type) {
  return {
    reel:  'bg-purple-100 text-purple-700',
    post:  'bg-blue-100 text-blue-700',
    story: 'bg-orange-100 text-orange-700',
  }[type] ?? 'bg-gray-100 text-gray-600'
}

function statusBadge(status) {
  return {
    draft:             'bg-gray-100 text-gray-500',
    ready_for_review:  'bg-yellow-100 text-yellow-700',
    in_correction:     'bg-orange-100 text-orange-700',
    approved:          'bg-green-100 text-green-700',
    rejected:          'bg-red-100 text-red-700',
  }[status] ?? 'bg-gray-100 text-gray-500'
}

function statusLabel(status) {
  return {
    draft:             'Borrador',
    ready_for_review:  'En revisión',
    in_correction:     'En corrección',
    approved:          'Aprobado',
    rejected:          'Rechazado',
  }[status] ?? status
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>
