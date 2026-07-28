const DB_NAME = 'GrowFinanceOffline'
const DB_VERSION = 1

class OfflineDb {
  constructor() {
    this.db = null
  }

  async open() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(DB_NAME, DB_VERSION)

      request.onupgradeneeded = (event) => {
        const db = event.target.result

        if (!db.objectStoreNames.contains('pending_journals')) {
          const store = db.createObjectStore('pending_journals', { keyPath: 'localId', autoIncrement: true })
          store.createIndex('status', 'status', { unique: false })
          store.createIndex('createdAt', 'createdAt', { unique: false })
        }

        if (!db.objectStoreNames.contains('accounts')) {
          db.createObjectStore('accounts', { keyPath: 'id' })
        }

        if (!db.objectStoreNames.contains('customers')) {
          db.createObjectStore('customers', { keyPath: 'id' })
        }

        if (!db.objectStoreNames.contains('sync_log')) {
          const store = db.createObjectStore('sync_log', { keyPath: 'id', autoIncrement: true })
          store.createIndex('syncedAt', 'syncedAt', { unique: false })
        }
      }

      request.onsuccess = (event) => {
        this.db = event.target.result
        resolve(this.db)
      }

      request.onerror = (event) => {
        reject(event.target.error)
      }
    })
  }

  async addPendingJournal(journal) {
    const db = await this.ensureOpen()
    return new Promise((resolve, reject) => {
      const tx = db.transaction('pending_journals', 'readwrite')
      const store = tx.objectStore('pending_journals')
      const entry = {
        ...journal,
        status: 'pending',
        createdAt: new Date().toISOString(),
        syncedAt: null,
      }
      const request = store.add(entry)
      request.onsuccess = () => resolve(request.result)
      request.onerror = () => reject(request.error)
    })
  }

  async getPendingJournals() {
    const db = await this.ensureOpen()
    return new Promise((resolve, reject) => {
      const tx = db.transaction('pending_journals', 'readonly')
      const store = tx.objectStore('pending_journals')
      const index = store.index('status')
      const request = index.getAll('pending')
      request.onsuccess = () => resolve(request.result)
      request.onerror = () => reject(request.error)
    })
  }

  async markSynced(localId, serverId) {
    const db = await this.ensureOpen()
    return new Promise((resolve, reject) => {
      const tx = db.transaction('pending_journals', 'readwrite')
      const store = tx.objectStore('pending_journals')
      const getRequest = store.get(localId)
      getRequest.onsuccess = () => {
        const entry = getRequest.result
        if (entry) {
          entry.status = 'synced'
          entry.serverId = serverId
          entry.syncedAt = new Date().toISOString()
          store.put(entry)
        }
        resolve()
      }
      getRequest.onerror = () => reject(getRequest.error)
    })
  }

  async cacheAccounts(accounts) {
    const db = await this.ensureOpen()
    const tx = db.transaction('accounts', 'readwrite')
    const store = tx.objectStore('accounts')
    accounts.forEach(account => store.put(account))
    return new Promise((resolve, reject) => {
      tx.oncomplete = () => resolve()
      tx.onerror = () => reject(tx.error)
    })
  }

  async getCachedAccounts() {
    const db = await this.ensureOpen()
    return new Promise((resolve, reject) => {
      const tx = db.transaction('accounts', 'readonly')
      const store = tx.objectStore('accounts')
      const request = store.getAll()
      request.onsuccess = () => resolve(request.result)
      request.onerror = () => reject(request.error)
    })
  }

  async cacheCustomers(customers) {
    const db = await this.ensureOpen()
    const tx = db.transaction('customers', 'readwrite')
    const store = tx.objectStore('customers')
    customers.forEach(customer => store.put(customer))
    return new Promise((resolve, reject) => {
      tx.oncomplete = () => resolve()
      tx.onerror = () => reject(tx.error)
    })
  }

  async getCachedCustomers() {
    const db = await this.ensureOpen()
    return new Promise((resolve, reject) => {
      const tx = db.transaction('customers', 'readonly')
      const store = tx.objectStore('customers')
      const request = store.getAll()
      request.onsuccess = () => resolve(request.result)
      request.onerror = () => reject(request.error)
    })
  }

  async getSyncStatus() {
    const pending = await this.getPendingJournals()
    return {
      pendingCount: pending.length,
      pendingEntries: pending,
      lastSync: pending.length > 0 ? pending[pending.length - 1].syncedAt : null,
    }
  }

  async ensureOpen() {
    if (this.db) return this.db
    return this.open()
  }
}

const offlineDb = new OfflineDb()
export default offlineDb
