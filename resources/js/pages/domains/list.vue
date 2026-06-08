<script setup>
import { onMounted, ref } from 'vue'

const domains = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
const confirmDialog = ref(false)
const deletingId = ref(null)
const isDeleting = ref(false)

function confirmDelete(id) {
  deletingId.value = id
  confirmDialog.value = true
}

async function deleteDomain() {
  isDeleting.value = true
  try {
    const res = await fetch(`/api/domains/${deletingId.value}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
      },
    })
    if (!res.ok) {
      errorMessage.value = res.status === 403 ? 'Access denied.' : 'Failed to delete domain.'
      return
    }
    domains.value = domains.value.filter(d => d.id !== deletingId.value)
  } catch {
    errorMessage.value = 'Network error. Please try again.'
  } finally {
    isDeleting.value = false
    confirmDialog.value = false
    deletingId.value = null
  }
}

async function fetchDomains() {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const res = await fetch('/api/domains', {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
      },
    })
    if (!res.ok) {
      errorMessage.value = res.status === 401 ? 'Unauthorized. Please log in.' : 'Failed to load domains.'
      return
    }
    domains.value = await res.json()
  } catch {
    errorMessage.value = 'Network error. Please try again.'
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchDomains)
</script>

<template>
  <VRow>
    <VCol cols="12">        
      <VCard title="My Domains">
        <VCardText v-if="errorMessage">
          <VAlert
            type="error"
            variant="tonal"
            density="compact"
          >
            {{ errorMessage }}
          </VAlert>
        </VCardText>
        
        <VCardText class="d-flex justify-end">
          <VBtn
            color="success"
            variant="tonal"
            @click="$router.push('/domains/store')"
          >          
            + Add Domain
          </VBtn>
        </VCardText>        

        <VTable class="px-8 pb-8">
          <thead>
            <tr>
              <th class="text-uppercase">
                id
              </th>
              <th class="text-uppercase">
                domain
              </th>
              <th class="text-uppercase">
                actions
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="isLoading">
              <td
                colspan="3"
                class="text-center py-4"
              >
                <VProgressCircular
                  indeterminate
                  color="primary"
                />
              </td>
            </tr>

            <tr v-else-if="!domains.length && !errorMessage">
              <td
                colspan="3"
                class="text-center py-4 text-medium-emphasis"
              >
                No domains yet.
              </td>
            </tr>

            <tr
              v-for="item in domains"
              :key="item.id"
            >
              <td>{{ item.id }}</td>
              <td>{{ item.domain }}</td>
              <td>
                <VBtn
                  color="primary"
                  variant="tonal"
                  icon="bx-edit"
                  class="mr-2"
                  @click="$router.push(`/domains/${item.id}`)"
                />
                <VBtn
                  color="error"
                  variant="tonal"
                  icon="bx-trash"
                  @click="confirmDelete(item.id)"
                />
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>
  </VRow>

  <!-- Confirm delete dialog -->
  <VDialog v-model="confirmDialog" max-width="400">
    <VCard title="Delete Domain">
      <VCardText>Are you sure you want to delete this domain? This action cannot be undone.</VCardText>
      <VCardActions class="justify-end">
        <VBtn variant="tonal" @click="confirmDialog = false">Cancel</VBtn>
        <VBtn color="error" variant="tonal" :loading="isDeleting" @click="deleteDomain">Delete</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
