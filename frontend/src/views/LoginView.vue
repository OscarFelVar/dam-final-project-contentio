<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth    = useAuthStore()
const router  = useRouter()
const email   = ref('')
const password = ref('')
const error   = ref('')
const loading = ref(false)
const showPwd = ref(false)

async function submit() {
  error.value   = ''
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    router.push({ name: 'dashboard' })
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Credenciales incorrectas'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex">

    <!-- Left — brand panel -->
    <div
      class="sidebar-bg hidden lg:flex lg:w-[48%] flex-col justify-between p-12 relative overflow-hidden"
    >
      <!-- Logo -->
      <img src="/logo.png" alt="CONTENTIO" class="w-44 h-auto object-contain">

      <!-- Quote -->
      <div>
        <div class="w-8 h-px bg-[#D7C9B8]/40 mb-8"></div>
        <p class="text-[#D7C9B8] text-2xl leading-relaxed max-w-sm">
          El espacio donde el contenido se convierte en aprobación.
        </p>
        <p class="text-[#7D5A44] text-sm mt-6 tracking-wide">Revisión · Anotación · Aprobación</p>
      </div>

      <!-- Dots -->
      <div class="flex items-center gap-2">
        <div class="w-2 h-2 rounded-full bg-[#D7C9B8]/30"></div>
        <div class="w-2 h-2 rounded-full bg-[#D7C9B8]/60"></div>
        <div class="w-2 h-2 rounded-full bg-white/80"></div>
      </div>

      <!-- Decorative circles -->
      <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full border border-white/5 pointer-events-none"></div>
      <div class="absolute -bottom-16 -right-16 w-48 h-48 rounded-full border border-white/5 pointer-events-none"></div>
    </div>

    <!-- Right — form -->
    <div class="flex-1 flex items-center justify-center px-8 sm:px-12 lg:px-16 bg-[#FAF9F6] relative">

      <div class="w-full max-w-sm">

        <!-- Mobile logo -->
        <div class="mb-10 lg:hidden">
          <img src="/logo.png" alt="CONTENTIO" class="w-40 h-auto object-contain">
        </div>

        <!-- Heading -->
        <div class="mb-8">
          <h1 class="text-2xl text-[#4A342A] tracking-wide">Bienvenido</h1>
          <p class="text-[#A08A7A] text-sm mt-1.5">Accede a tu espacio de trabajo</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-5">

          <div>
            <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">
              Correo electrónico
            </label>
            <input
              v-model="email"
              type="email"
              required
              placeholder="tu@agencia.com"
              class="input-contentio"
            >
          </div>

          <div>
            <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">
              Contraseña
            </label>
            <div class="relative">
              <input
                v-model="password"
                :type="showPwd ? 'text' : 'password'"
                required
                placeholder="••••••••••"
                class="input-contentio pr-11"
              >
              <button
                type="button"
                @click="showPwd = !showPwd"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#D7C9B8] hover:text-[#7D5A44] transition-colors"
              >
                <svg v-if="!showPwd" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="flex justify-end">
            <a href="#" class="text-xs text-[#A08A7A] hover:text-[#7D5A44] transition-colors">
              ¿Olvidaste tu contraseña?
            </a>
          </div>

          <!-- Error -->
          <p v-if="error" class="text-xs text-red-500 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5">
            {{ error }}
          </p>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-[#4A342A] hover:bg-[#7D5A44] disabled:opacity-50 text-white rounded-lg py-3 text-sm tracking-[0.08em] transition-colors duration-200"
          >
            {{ loading ? 'Accediendo...' : 'Iniciar sesión' }}
          </button>

        </form>

        <p class="text-center text-[#D7C9B8] text-xs mt-10 tracking-wide">
          Acceso solo por invitación
        </p>

      </div>
    </div>

  </div>
</template>
