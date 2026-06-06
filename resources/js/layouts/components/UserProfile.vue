<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import { useRouter } from 'vue-router'
import { resetAuth } from '@/plugins/router'

const router = useRouter()
const userName = ref(localStorage.getItem('user_name'))
const errorMessage = ref('')

async function handleLogout() {
  const token = localStorage.getItem('auth_token')
  errorMessage.value = ''
  try {
    const res = await fetch('/api/logout', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json', 
        Accept: 'application/json',
        Authorization: `Bearer ${token}`, },
    })
    const data = await res.json()
    if (!res.ok) {
      errorMessage.value = data.errors?.email?.[0] ?? data.message ?? 'Logout failed.'
      return
    }
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user_name')
    errorMessage.value = ''
    resetAuth()
    router.push('/login')
  } catch {
    errorMessage.value = 'Network error. Please try again.'
  }  
}
</script>

<template>
  <VBadge
    dot
    location="bottom right"
    offset-x="3"
    offset-y="3"
    color="success"
    bordered
  >
    <VAvatar
      class="cursor-pointer"
      color="primary"
      variant="tonal"
    >
      <VImg :src="avatar1" />

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="230"
        location="bottom end"
        offset="14px"
      >
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                >
                  <VAvatar
                    color="primary"
                    variant="tonal"
                  >
                    <VImg :src="avatar1" />
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle class="font-weight-semibold">
              {{ userName  || 'Best User' }}
            </VListItemTitle>
            <VListItemSubtitle>Admin</VListItemSubtitle>
          </VListItem>
          <VDivider class="my-2" />

          <!-- 👉 Logout -->
          <VListItem @click="handleLogout">
            <template #prepend>
              <VIcon
                class="me-2"
                icon="bx-log-out"
                size="22"
              />
            </template>

            <VListItemTitle>Logout</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>
