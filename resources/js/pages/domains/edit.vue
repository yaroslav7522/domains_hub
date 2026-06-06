<script setup>
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const id = route.params.id

const domain = ref('')
const isLoading = ref(false)
const isFetching = ref(true)
const errorMessage = ref('')
const successMessage = ref('')

async function fetchDomain() {
  try {
    const res = await fetch(`/api/domains/${id}`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
      },
    })
    if (!res.ok) {
      errorMessage.value = 'Domain not found or access denied.'
      return
    }
    const data = await res.json()
    domain.value = data.domain
  } catch {
    errorMessage.value = 'Network error. Please try again.'
  } finally {
    isFetching.value = false
  }
}

async function handleSubmit() {
  errorMessage.value = ''
  successMessage.value = ''
  isLoading.value = true
  try {
    const res = await fetch(`/api/domains/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
      },
      body: JSON.stringify({ domain: domain.value }),
    })
    const data = await res.json()
    if (!res.ok) {
      errorMessage.value = data.errors?.domain?.[0] ?? data.message ?? 'Failed to update domain.'
      return
    }
    successMessage.value = `Domain "${data.domain}" updated successfully.`
    setTimeout(() => {
      router.push('/domains')
    }, 1500)
  } catch {
    errorMessage.value = 'Network error. Please try again.'
  } finally {
    isLoading.value = false
  }
}

fetchDomain()
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <VCard title="Edit Domain">
          <VForm @submit.prevent="handleSubmit" class="px-8 py-8">
            <VRow>
              <!-- alerts -->
              <VCol v-if="errorMessage" cols="12">
                <VAlert type="error" variant="tonal" density="compact">{{ errorMessage }}</VAlert>
              </VCol>
              <VCol v-if="successMessage" cols="12">
                <VAlert type="success" variant="tonal" density="compact">{{ successMessage }}</VAlert>
              </VCol>

              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3">
                    <label for="domain">Domain</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <VTextField
                      id="domain"
                      v-model="domain"
                      placeholder="example.com"
                      persistent-placeholder
                      :loading="isFetching"
                      :disabled="isFetching"
                    />
                  </VCol>
                </VRow>
              </VCol>

              <!-- submit and back buttons -->
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" />
                  <VCol cols="12" md="9">
                    <VBtn
                      type="submit"
                      class="me-4"
                      :loading="isLoading"
                      :disabled="isFetching"
                    >
                      Save Changes
                    </VBtn>
                    <VBtn
                      color="secondary"
                      variant="tonal"
                      @click="router.push('/domains')"
                    >
                      Back
                    </VBtn>
                  </VCol>
                </VRow>
              </VCol>
            </VRow>
          </VForm>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
