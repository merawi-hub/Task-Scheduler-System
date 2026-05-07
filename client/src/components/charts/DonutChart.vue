<template>
  <div class="w-full h-[280px] flex flex-col items-center">
    <div class="w-full h-[200px]">
      <Doughnut :data="chartData" :options="chartOptions" />
    </div>
    <div class="mt-4 w-full">
      <div class="grid grid-cols-2 gap-3">
        <div v-for="(label, index) in chartData.labels" :key="index" class="flex items-center gap-2">
          <div 
            class="w-3 h-3 rounded-full flex-shrink-0" 
            :style="{ backgroundColor: chartData.datasets[0].backgroundColor[index] }"
          ></div>
          <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-600 truncate">{{ label }}</p>
            <p class="text-sm font-semibold text-gray-900">{{ chartData.datasets[0].data[index] }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'

// Register Chart.js components
ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const chartData = computed(() => props.data)

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '70%',
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      titleFont: {
        size: 13,
        weight: 'bold'
      },
      bodyFont: {
        size: 12
      },
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1,
      displayColors: true,
      callbacks: {
        label: function(context) {
          const label = context.label || ''
          const value = context.parsed || 0
          const total = context.dataset.data.reduce((a, b) => a + b, 0)
          const percentage = ((value / total) * 100).toFixed(1)
          return `${label}: ${value} (${percentage}%)`
        }
      }
    }
  }
}
</script>
