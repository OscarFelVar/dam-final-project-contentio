<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  transition: { type: Object, default: null },  // { status, label, style }
  itemTitle:  { type: String,  default: '' },
})

const emit = defineEmits(['confirm', 'cancel'])

const note = ref('')

const config = computed(() => {
  const map = {
    approved:         { icon: 'check',   color: 'emerald', heading: 'Aprobar contenido',           desc: 'El contenido quedará marcado como aprobado y la agencia será notificada.' },
    in_correction:    { icon: 'pencil',  color: 'orange',  heading: 'Solicitar corrección',         desc: 'Se notificará a la agencia que debe realizar correcciones antes de continuar.' },
    rejected:         { icon: 'x',       color: 'red',     heading: 'Rechazar contenido',           desc: 'El contenido quedará rechazado. Esta acción es definitiva.' },
    ready_for_review: { icon: 'send',    color: 'brown',   heading: 'Enviar a revisión',            desc: 'El contenido se enviará a la clínica para su revisión y aprobación.' },
  }
  return map[props.transition?.status] ?? {}
})

const iconColors = {
  emerald: 'bg-emerald-50 text-emerald-600',
  orange:  'bg-orange-50 text-orange-500',
  red:     'bg-red-50 text-red-500',
  brown:   'bg-[#F5F1EA] text-[#7D5A44]',
}

const btnColors = {
  emerald: 'bg-emerald-600 hover:bg-emerald-700',
  orange:  'bg-orange-500 hover:bg-orange-600',
  red:     'bg-red-600 hover:bg-red-700',
  brown:   'bg-[#4A342A] hover:bg-[#7D5A44]',
}

const showNote = computed(() =>
  ['in_correction', 'rejected'].includes(props.transition?.status)
)

function handleConfirm() {
  emit('confirm', note.value.trim() || null)
  note.value = ''
}

function handleCancel() {
  note.value = ''
  emit('cancel')
}
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="transition"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm"
        @click.self="handleCancel"
      >
        <Transition
          enter-active-class="transition duration-150 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="transition"
            class="bg-white rounded-2xl shadow-xl w-full max-w-md border border-[#E8E0D8]"
            @click.stop
          >
            <!-- Header -->
            <div class="p-6 pb-4 flex items-start gap-4">
              <!-- Icono -->
              <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0', iconColors[config.color]]">

                <!-- check -->
                <svg v-if="config.icon === 'check'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <!-- pencil -->
                <svg v-else-if="config.icon === 'pencil'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <!-- x -->
                <svg v-else-if="config.icon === 'x'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <!-- send -->
                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>

              </div>

              <div class="flex-1 min-w-0">
                <h3 class="text-base text-[#4A342A] font-medium mb-0.5">{{ config.heading }}</h3>
                <p class="text-xs text-[#A08A7A] leading-relaxed">{{ config.desc }}</p>
              </div>
            </div>

            <!-- Item title -->
            <div class="mx-6 mb-4 bg-[#F5F1EA] rounded-lg px-4 py-2.5">
              <p class="text-xs text-[#7D5A44] truncate">{{ itemTitle }}</p>
            </div>

            <!-- Nota (solo in_correction / rejected) -->
            <div v-if="showNote" class="mx-6 mb-4">
              <label class="block text-xs text-[#A08A7A] mb-1.5">
                {{ transition.status === 'rejected' ? 'Motivo del rechazo' : 'Indicaciones para la corrección' }}
                <span class="text-[#D7C9B8]">(opcional)</span>
              </label>
              <textarea
                v-model="note"
                rows="3"
                placeholder="Escribe un comentario..."
                class="w-full text-xs text-[#4A342A] placeholder-[#D7C9B8] border border-[#E8E0D8] rounded-lg p-3 resize-none outline-none focus:border-[#7D5A44] transition-colors"
              />
            </div>

            <!-- Acciones -->
            <div class="px-6 pb-6 flex gap-3 justify-end">
              <button
                @click="handleCancel"
                class="text-sm text-[#A08A7A] hover:text-[#4A342A] px-4 py-2 transition-colors"
              >
                Cancelar
              </button>
              <button
                @click="handleConfirm"
                :class="['text-sm text-white rounded-xl px-5 py-2 transition-colors', btnColors[config.color]]"
              >
                {{ transition.label }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
