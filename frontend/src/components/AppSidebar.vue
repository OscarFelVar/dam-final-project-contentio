<script setup>
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

defineProps({ isOpen: Boolean })
const emit = defineEmits(['close'])

const auth   = useAuthStore()
const router = useRouter()
const route  = useRoute()

const initials = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() ?? '??'

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}

function isActive(path) {
  return route.path.startsWith(path)
}
</script>

<template>
  <aside
    class="sidebar-bg fixed left-0 top-0 h-screen w-64 flex flex-col z-10 select-none transition-transform duration-300 md:translate-x-0"
    :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
  >
    <!-- Logo -->
    <div class="px-6 pt-7 pb-5">
      <img
        src="/logo.png"
        alt="CONTENTIO"
        class="w-44 h-auto object-contain"
      >
    </div>
    <div class="mx-6 border-t border-white/10 mb-5"></div>

    <!-- Nav -->
    <nav class="flex-1 px-4 space-y-0.5">
      <p class="px-3 mb-2 text-[10px] tracking-[0.15em] text-white/30 uppercase">Principal</p>

      <!-- Administración (solo admin) -->
      <RouterLink
        v-if="auth.user?.role === 'admin'"
        to="/admin"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150"
        :class="isActive('/admin')
          ? 'bg-white/10 text-white border-l-2 border-white/40'
          : 'text-[#D7C9B8] hover:bg-white/8 hover:text-white'"
        @click="emit('close')"
      >
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        Administración
      </RouterLink>

      <RouterLink
        to="/dashboard"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150"
        :class="isActive('/dashboard')
          ? 'bg-white/10 text-white border-l-2 border-white/40'
          : 'text-[#D7C9B8] hover:bg-white/8 hover:text-white'"
        @click="emit('close')"
      >
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Inicio
      </RouterLink>

      <RouterLink
        to="/content-items"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150"
        :class="isActive('/content-items')
          ? 'bg-white/10 text-white border-l-2 border-white/40'
          : 'text-[#D7C9B8] hover:bg-white/8 hover:text-white'"
        @click="emit('close')"
      >
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
        </svg>
        Contenidos
      </RouterLink>

      <RouterLink
        v-if="auth.user?.role === 'agency'"
        to="/clinics"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150"
        :class="isActive('/clinics')
          ? 'bg-white/10 text-white border-l-2 border-white/40'
          : 'text-[#D7C9B8] hover:bg-white/8 hover:text-white'"
        @click="emit('close')"
      >
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Clínicas
      </RouterLink>

    </nav>

    <!-- User -->
    <div class="px-4 pb-5 pt-4 border-t border-white/10">
      <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg group">
        <div class="w-7 h-7 rounded-full bg-[#D7C9B8] flex items-center justify-center text-[#4A342A] text-xs flex-shrink-0">
          {{ initials(auth.user?.name) }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-white text-xs truncate">{{ auth.user?.name }}</p>
          <p class="text-white/35 text-[10px] capitalize">{{ auth.user?.role }}</p>
        </div>
        <button
          @click="handleLogout"
          class="text-white/25 hover:text-white/70 transition-colors flex-shrink-0"
          title="Cerrar sesión"
        >
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
        </button>
      </div>
    </div>
  </aside>
</template>
