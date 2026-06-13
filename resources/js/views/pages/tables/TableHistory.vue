<script setup>
import { onMounted, ref, watch } from 'vue'

const domains = ref([])
const selectedDomain = ref(null)
const history = ref([])
const isLoadingDomains = ref(false)
const isLoadingHistory = ref(false)
const errorMessage = ref('')
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })

function authHeaders() {
  return {
    Accept: 'application/json',
    Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
  }
}

async function fetchDomains() {
  isLoadingDomains.value = true
  errorMessage.value = ''
  try {
    const res = await fetch('/api/domains', { headers: authHeaders() })
    if (!res.ok) {
      errorMessage.value = res.status === 401 ? 'Unauthorized. Please log in.' : 'Failed to load domains.'
      return
    }
    domains.value = await res.json()
    if (domains.value.length) {
      selectedDomain.value = domains.value[0].id
    }
  } catch {
    errorMessage.value = 'Network error. Please try again.'
  } finally {
    isLoadingDomains.value = false
  }
}

async function fetchHistory(page = 1) {
  if (!selectedDomain.value) return
  isLoadingHistory.value = true
  errorMessage.value = ''
  try {
    const res = await fetch(`/api/domains/${selectedDomain.value}/history?page=${page}`, {
      headers: authHeaders(),
    })
    if (!res.ok) {
      errorMessage.value = res.status === 403 ? 'Access denied.' : 'Failed to load history.'
      history.value = []
      return
    }
    const json = await res.json()
    history.value = json.data
    pagination.value = { current_page: json.current_page, last_page: json.last_page, total: json.total }
  } catch {
    errorMessage.value = 'Network error. Please try again.'
  } finally {
    isLoadingHistory.value = false
  }
}

watch(selectedDomain, () => fetchHistory(1))

onMounted(fetchDomains)
</script>

<template>
  <VCardText>
    <VRow class="mb-4" align="center">
      <VCol cols="12" sm="4">
        <VSelect
          v-model="selectedDomain"
          :items="domains"
          item-title="domain"
          item-value="id"
          label="Filter by domain"
          :loading="isLoadingDomains"
          density="compact"
          hide-details
          clearable
        />
      </VCol>
    </VRow>

    <VAlert
      v-if="errorMessage"
      type="error"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      {{ errorMessage }}
    </VAlert>

    <VTable>
      <thead>
        <tr>
          <th class="text-uppercase">ID</th>
          <th class="text-uppercase">Status</th>
          <th class="text-uppercase">HTTP Code</th>
          <th class="text-uppercase">Response Time (ms)</th>
          <th class="text-uppercase">Error</th>
          <th class="text-uppercase">Date</th>
          <th class="text-uppercase">Time</th>
        </tr>
      </thead>

      <tbody>
        <tr v-if="isLoadingHistory">
          <td colspan="7" class="text-center py-4">
            <VProgressCircular indeterminate color="primary" />
          </td>
        </tr>

        <tr v-else-if="!selectedDomain">
          <td colspan="7" class="text-center py-4 text-medium-emphasis">
            Select a domain to view history.
          </td>
        </tr>

        <tr v-else-if="!history.length">
          <td colspan="7" class="text-center py-4 text-medium-emphasis">
            No history records found.
          </td>
        </tr>

        <tr v-for="item in history" :key="item.id">
          <td>{{ item.id }}</td>
          <td>
            <VChip
              :color="item.status === 'up' ? 'success' : 'error'"
              size="small"
              variant="tonal"
            >
              {{ item.status }}
            </VChip>
          </td>
          <td>{{ item.http_code ?? '—' }}</td>
          <td>{{ item.response_time_ms ?? '—' }}</td>
          <td>{{ item.error ?? '—' }}</td>
          <td>{{ new Date(item.created_at).toLocaleDateString() }}</td>
          <td>{{ new Date(item.created_at).toLocaleTimeString() }}</td>
        </tr>
      </tbody>
    </VTable>

    <div v-if="pagination.last_page > 1" class="d-flex justify-center mt-4">
      <VPagination
        :model-value="pagination.current_page"
        :length="pagination.last_page"
        @update:model-value="fetchHistory"
      />
    </div>
  </VCardText>
</template>
