import offlineDb from './Db'
import axios from 'axios'

class SyncService {
  constructor() {
    this.isSyncing = false
    this.syncInterval = null
  }

  startAutoSync(intervalMs = 30000) {
    this.stopAutoSync()
    this.syncInterval = setInterval(() => this.sync(), intervalMs)
  }

  stopAutoSync() {
    if (this.syncInterval) {
      clearInterval(this.syncInterval)
      this.syncInterval = null
    }
  }

  async sync() {
    if (this.isSyncing) return { synced: 0, failed: 0 }
    this.isSyncing = true

    try {
      const pending = await offlineDb.getPendingJournals()
      let synced = 0
      let failed = 0

      for (const entry of pending) {
        try {
          const response = await axios.post('/growfinance/offline/sync/journal', entry.data, {
            headers: { 'X-Offline-Sync': 'true' }
          })
          await offlineDb.markSynced(entry.localId, response.data.id)
          synced++
        } catch (error) {
          console.error('Sync failed for journal', entry.localId, error)
          failed++
        }
      }

      return { synced, failed }
    } finally {
      this.isSyncing = false
    }
  }

  async getStatus() {
    const status = await offlineDb.getSyncStatus()
    return {
      ...status,
      isSyncing: this.isSyncing,
      online: navigator.onLine,
    }
  }

  async cacheReferenceData(businessId) {
    try {
      const [accountsRes, customersRes] = await Promise.all([
        axios.get('/growfinance/accounts', { params: { all: true } }),
        axios.get('/growfinance/customers', { params: { all: true } }),
      ])
      await Promise.all([
        offlineDb.cacheAccounts(accountsRes.data || []),
        offlineDb.cacheCustomers(customersRes.data || []),
      ])
      return true
    } catch (e) {
      console.warn('Failed to cache reference data:', e)
      return false
    }
  }
}

const syncService = new SyncService()
export default syncService
