<script setup>
import { ref } from 'vue'
import AppSidebar from './AppSidebar.vue'

const sidebarOpen = ref(false)
</script>

<template>
  <div class="flex min-h-screen bg-[#FAF9F6]">
    <!-- Overlay oscuro al abrir sidebar en móvil -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 bg-black/50 z-[9] md:hidden"
      @click="sidebarOpen = false"
    />

    <AppSidebar :is-open="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex-1 md:ml-64 p-5 md:p-8 lg:p-10 min-h-screen">
      <!-- Botón hamburguesa solo en móvil -->
      <button
        class="mb-4 md:hidden p-1.5 rounded-lg text-[#4A342A] hover:bg-[#4A342A]/10 transition-colors"
        @click="sidebarOpen = true"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
      <slot />
    </main>
  </div>
</template>
