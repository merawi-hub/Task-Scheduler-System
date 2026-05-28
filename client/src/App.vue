<script setup>
// Main App component - uses router-view for navigation
import { onMounted } from 'vue'
import ToastContainer from '@/components/ToastContainer.vue'

onMounted(() => {
  console.log('App mounted successfully!')
})
</script>

<template>
  <div id="app">
    <RouterView />
    <ToastContainer />
  </div>
</template>

<style>
/* Global styles are imported in main.js */
</style>
