<script setup>
import AppSidebar from './AppSidebar.vue'
</script>

<template>
  <div class="flex min-h-screen bg-[#FAF9F6]">
    <AppSidebar />
    <main class="ml-64 flex-1 p-8 lg:p-10 min-h-screen">
      <slot />
    </main>
  </div>
</template>
