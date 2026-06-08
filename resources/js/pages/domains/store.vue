<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const domain = ref('')
const checkInterval = ref(5)
const requestTimeout = ref(30)
const checkMethod = ref('GET')
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const checkMethodOptions = ['GET', 'HEAD']

async function handleSubmit() {
  errorMessage.value = ''
  successMessage.value = ''
  isLoading.value = true
  try {
    const res = await fetch('/api/domains', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
      },
      body: JSON.stringify({
        domain: domain.value,
        check_interval: checkInterval.value,
        request_timeout: requestTimeout.value,
        check_method: checkMethod.value,
      }),
    })
    const data = await res.json()
    if (!res.ok) {
      errorMessage.value = data.errors?.domain?.[0] ?? data.message ?? 'Failed to add domain.'
      return
    }
    successMessage.value = `Domain "${data.domain}" added successfully.`
    setTimeout(() => {
      router.push('/domains')
    }, 1500)
  } catch {
    errorMessage.value = 'Network error. Please try again.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
    <div>
        <VRow>
            <VCol cols="12">
            <VCard title="Add Domain">
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
                            />
                        </VCol>
                        </VRow>
                    </VCol>

                    <VCol cols="12">
                        <VRow no-gutters>
                        <VCol cols="12" md="3">
                            <label for="check_interval">Check Interval (min)</label>
                        </VCol>
                        <VCol cols="12" md="9">
                            <VTextField
                            id="check_interval"
                            v-model.number="checkInterval"
                            type="number"
                            placeholder="5"
                            persistent-placeholder
                            :min="1"
                            />
                        </VCol>
                        </VRow>
                    </VCol>

                    <VCol cols="12">
                        <VRow no-gutters>
                        <VCol cols="12" md="3">
                            <label for="request_timeout">Request Timeout (sec)</label>
                        </VCol>
                        <VCol cols="12" md="9">
                            <VTextField
                            id="request_timeout"
                            v-model.number="requestTimeout"
                            type="number"
                            placeholder="30"
                            persistent-placeholder
                            :min="1"
                            />
                        </VCol>
                        </VRow>
                    </VCol>

                    <VCol cols="12">
                        <VRow no-gutters>
                        <VCol cols="12" md="3">
                            <label for="check_method">Check Method</label>
                        </VCol>
                        <VCol cols="12" md="9">
                            <VSelect
                            id="check_method"
                            v-model="checkMethod"
                            :items="checkMethodOptions"
                            />
                        </VCol>
                        </VRow>
                    </VCol>

                    <!-- submit and reset button -->
                    <VCol cols="12">
                        <VRow no-gutters>
                        <VCol cols="12" md="3" />
                        <VCol cols="12" md="9">
                            <VBtn
                            type="submit"
                            class="me-4"
                            :loading="isLoading"
                            >
                            Add Domain
                            </VBtn>
                            <VBtn
                            color="secondary"
                            variant="tonal"
                            type="reset"
                            @click="$router.push('/domains')"
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
