import offlineDb from './Db'
import syncService from './SyncService'

export default {
  install(app) {
    app.config.globalProperties.$offlineDb = offlineDb
    app.config.globalProperties.$syncService = syncService
  }
}

export { offlineDb, syncService }
