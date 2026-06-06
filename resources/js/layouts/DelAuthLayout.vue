<template>
  <slot v-if="isAuthenticated" />
  <div v-else class="auth-redirect">
    <p>Redirecting to login...</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { checkAuth } from '@/plugins/router'

const isAuthenticated = ref(false)
const vueRouter = useRouter()

onMounted(async () => {
  isAuthenticated.value = await checkAuth()
  if (!isAuthenticated.value) vueRouter.push('/login')
})
</script>