<template>
  <div class="api-test">
    <h2>API Connection Test</h2>
    <button @click="testConnection" :disabled="loading">
      {{ loading ? 'Testing...' : 'Test Connection' }}
    </button>
    
    <div v-if="response" class="response success">
      <h3>✓ Connection Successful!</h3>
      <pre>{{ JSON.stringify(response, null, 2) }}</pre>
    </div>
    
    <div v-if="error" class="response error">
      <h3>✗ Connection Failed</h3>
      <pre>{{ error }}</pre>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/api'

const loading = ref(false)
const response = ref(null)
const error = ref(null)

const testConnection = async () => {
  loading.value = true
  response.value = null
  error.value = null
  
  try {
    const data = await api.testConnection()
    response.value = data
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Unknown error occurred'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.api-test {
  padding: 20px;
  max-width: 600px;
  margin: 0 auto;
}

h2 {
  color: #42b983;
  margin-bottom: 20px;
}

button {
  background-color: #42b983;
  color: white;
  border: none;
  padding: 10px 20px;
  font-size: 16px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

button:hover:not(:disabled) {
  background-color: #35a372;
}

button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.response {
  margin-top: 20px;
  padding: 15px;
  border-radius: 4px;
  text-align: left;
}

.response.success {
  background-color: #d4edda;
  border: 1px solid #c3e6cb;
  color: #155724;
}

.response.error {
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
  color: #721c24;
}

.response h3 {
  margin-top: 0;
  margin-bottom: 10px;
}

pre {
  background-color: rgba(0, 0, 0, 0.05);
  padding: 10px;
  border-radius: 4px;
  overflow-x: auto;
  font-size: 14px;
}
</style>
