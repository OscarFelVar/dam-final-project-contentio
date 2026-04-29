<script setup>
defineProps({
  histories: { type: Array, required: true },
})

function statusLabel(s) {
  return {
    draft:            'Borrador',
    ready_for_review: 'En revision',
    in_correction:    'En correccion',
    approved:         'Aprobado',
    rejected:         'Rechazado',
  }[s] ?? s
}

function dotClass(s) {
  return {
    draft:            'bg-[#D7C9B8] border-[#D7C9B8]',
    ready_for_review: 'bg-amber-400 border-amber-400',
    in_correction:    'bg-orange-400 border-orange-400',
    approved:         'bg-emerald-500 border-emerald-500',
    rejected:         'bg-red-500 border-red-500',
  }[s] ?? 'bg-[#D7C9B8] border-[#D7C9B8]'
}

function badgeClass(s) {
  return {
    draft:            'bg-[#F5F1EA] text-[#7D5A44] border-[#D7C9B8]',
    ready_for_review: 'bg-amber-50 text-amber-700 border-amber-200',
    in_correction:    'bg-orange-50 text-orange-700 border-orange-200',
    approved:         'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected:         'bg-red-50 text-red-700 border-red-200',
  }[s] ?? 'bg-[#F5F1EA] text-[#A08A7A] border-[#D7C9B8]'
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('es-ES', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}
</script>

<template>
  <div class="relative">

    <!-- Línea vertical conectora -->
    <div
      v-if="histories.length > 1"
      class="absolute left-[7px] top-4 bottom-4 w-px bg-[#E8E0D8]"
    />

    <div class="space-y-0">
      <div
        v-for="(h, index) in histories"
        :key="h.id"
        class="relative flex items-start gap-4 pb-5 last:pb-0"
      >
        <!-- Dot -->
        <div :class="['w-4 h-4 rounded-full border-2 shrink-0 mt-0.5 z-10', dotClass(h.to_status)]" />

        <!-- Contenido -->
        <div class="flex-1 min-w-0 -mt-0.5">
          <div class="flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-2">
              <!-- from_status solo si existe (el primer registro puede no tenerlo) -->
              <template v-if="h.from_status">
                <span :class="['text-[10px] border rounded-full px-2 py-0.5 shrink-0', badgeClass(h.from_status)]">
                  {{ statusLabel(h.from_status) }}
                </span>
                <svg class="w-3 h-3 text-[#D7C9B8] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </template>
              <span :class="['text-[10px] border rounded-full px-2 py-0.5 shrink-0', badgeClass(h.to_status)]">
                {{ statusLabel(h.to_status) }}
              </span>
            </div>
            <span class="text-[10px] text-[#D7C9B8] shrink-0">{{ formatDate(h.created_at) }}</span>
          </div>
          <p class="text-xs text-[#A08A7A] mt-0.5">por {{ h.user?.name ?? 'Sistema' }}</p>
        </div>
      </div>
    </div>

    <!-- Empty -->
    <p v-if="!histories.length" class="text-xs text-[#D7C9B8]">Sin cambios de estado aún.</p>

  </div>
</template>
