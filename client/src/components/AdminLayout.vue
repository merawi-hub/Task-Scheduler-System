<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden">
    <!-- Sidebar -->
    <AdminSidebar />

    <!-- Main Content Area -->
    <div class="flex-1 ml-64 overflow-auto">
      <router-view />
    </div>
  </div>
</template>

<script setup>
import AdminSidebar from './AdminSidebar.vue'
</script>
