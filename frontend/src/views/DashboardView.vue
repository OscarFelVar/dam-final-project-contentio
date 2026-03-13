<script setup>
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center gap-4">
    <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
    <p class="text-gray-500">Bienvenido, <span class="font-semibold">{{ auth.user?.name }}</span></p>
    <p class="text-xs text-gray-400">Rol: {{ auth.user?.role }}</p>
    <button
      @click="handleLogout"
      class="mt-4 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm"
    >
      Cerrar sesion
    </button>
  </div>
</template>
