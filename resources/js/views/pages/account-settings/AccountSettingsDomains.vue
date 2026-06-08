<script setup>
import { checkAuth } from '@/plugins/router'


const accountDataLocal = ref({ checkInterval: '', requestTimeout: '', checkMethod: '' })
const original = ref({ name: '', email: '' })
const isLoading = ref(false)
const isFetching = ref(true)
const errorMessage = ref('')
const successMessage = ref('')

async function fetchUser() {
  try {
    const res = await fetch('/api/user', {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
      },
    })
    if (!res.ok) return
    const data = await res.json()
    original.value = { checkInterval: data.check_interval, requestTimeout: data.request_timeout, checkMethod: data.check_method }
    accountDataLocal.value = { checkInterval: data.check_interval, requestTimeout: data.request_timeout, checkMethod: data.check_method }
  } finally {
    isFetching.value = false
  }
}

function resetForm() {
  accountDataLocal.value = { ...original.value }
  errorMessage.value = ''
  successMessage.value = ''
}

async function saveChanges() {
  errorMessage.value = ''
  successMessage.value = ''
  isLoading.value = true
  try {
    const res = await fetch('/api/user', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
      },
      body: JSON.stringify(accountDataLocal.value),
    })
    const data = await res.json()
    if (!res.ok) {
      errorMessage.value = data.errors?.name?.[0] ?? data.errors?.email?.[0] ?? data.message ?? 'Failed to save changes.'
      return
    }
    original.value = { checkInterval: data.check_interval, requestTimeout: data.request_timeout, checkMethod: data.check_method }
    successMessage.value = 'Availability check settings updated successfully.'
  } catch {
    errorMessage.value = 'Network error. Please try again.'
  } finally {
    isLoading.value = false
  }
}

fetchUser()
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Availability check settings">
        <VCardText>
          <VForm class="mt-6" @submit.prevent="saveChanges">
            <VRow>
              <!-- alerts -->
              <VCol v-if="errorMessage" cols="12">
                <VAlert type="error" variant="tonal" density="compact">{{ errorMessage }}</VAlert>
              </VCol>
              <VCol v-if="successMessage" cols="12">
                <VAlert type="success" variant="tonal" density="compact">{{ successMessage }}</VAlert>
              </VCol>

              <!-- Name -->
              <VCol md="6" cols="12">
                <VTextField
                  v-model="accountDataLocal.name"
                  placeholder="enter your name"
                  label="Name"
                  :loading="isFetching"
                  :disabled="isFetching"
                />
              </VCol>

              <!-- Email -->
              <VCol cols="12" md="6">
                <VTextField
                  v-model="accountDataLocal.email"
                  label="E-mail"
                  placeholder="example@gmail.com"
                  type="email"
                  :loading="isFetching"
                  :disabled="isFetching"
                />
              </VCol>

              <!-- Actions -->
              <VCol cols="12" class="d-flex flex-wrap gap-4">
                <VBtn type="submit" :loading="isLoading" :disabled="isFetching">Save changes</VBtn>
                <VBtn
                  color="secondary"
                  variant="tonal"
                  :disabled="isFetching"
                  @click.prevent="resetForm"
                >
                  Reset
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
