<template>
  <div v-if="!online || pendingCount > 0" class="flex items-center gap-2 text-xs px-3 py-1 rounded-full"
    :class="online ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'">
    <span v-if="!online" class="font-medium">Offline</span>
    <span v-if="online && pendingCount > 0" class="font-medium">
      {{ pendingCount }} pending
    </span>
    <span v-if="isSyncing" class="animate-pulse">Syncing...</span>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import offlineDb from './Db'
import syncService from './SyncService'

const online = ref(navigator.onLine)
const pendingCount = ref(0)
const isSyncing = ref(false)

let interval = null

onMounted(async () => {
  window.addEventListener('online', updateOnlineStatus)
  window.addEventListener('offline', updateOnlineStatus)

  await offlineDb.open()

  interval = setInterval(async () => {
    const status = await syncService.getStatus()
    pendingCount.value = status.pendingCount
    isSyncing.value = status.isSyncing
  }, 5000)
})

onUnmounted(() => {
  window.removeEventListener('online', updateOnlineStatus)
  window.removeEventListener('offline', updateOnlineStatus)
  if (interval) clearInterval(interval)
})

function updateOnlineStatus() {
  online.value = navigator.onLine
}
</script>
