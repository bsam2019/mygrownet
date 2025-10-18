<template>
  <div class="settings-management">
    <h2 class="text-xl font-semibold mb-4">System Settings</h2>
    <div class="grid gap-6">
      <div class="card p-4">
        <h3 class="text-lg mb-3">Platform Settings</h3>
        <form @submit.prevent="savePlatformSettings">
          <div class="grid gap-4">
            <div>
              <label>Platform Name</label>
              <input v-model="settings.platformName" type="text" class="form-input" />
            </div>
            <div>
              <label>Maintenance Mode</label>
              <input v-model="settings.maintenanceMode" type="checkbox" class="form-checkbox" />
            </div>
            <div>
              <label>Minimum Investment Amount</label>
              <input v-model="settings.minInvestment" type="number" class="form-input" />
            </div>
            <div>
              <label>Maximum Withdrawal Amount</label>
              <input v-model="settings.maxWithdrawal" type="number" class="form-input" />
            </div>
          </div>
          <button type="submit" class="btn-primary mt-4">Save Settings</button>
        </form>
      </div>

      <div class="card p-4">
        <h3 class="text-lg mb-3">Email Configuration</h3>
        <form @submit.prevent="saveEmailSettings">
          <div class="grid gap-4">
            <div>
              <label>SMTP Host</label>
              <input v-model="emailSettings.host" type="text" class="form-input" />
            </div>
            <div>
              <label>SMTP Port</label>
              <input v-model="emailSettings.port" type="number" class="form-input" />
            </div>
            <div>
              <label>From Email</label>
              <input v-model="emailSettings.fromEmail" type="email" class="form-input" />
            </div>
          </div>
          <button type="submit" class="btn-primary mt-4">Save Email Settings</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'

export default {
  name: 'AdminSettings',
  setup() {
    const settings = ref({
      platformName: '',
      maintenanceMode: false,
      minInvestment: 0,
      maxWithdrawal: 0
    })

    const emailSettings = ref({
      host: '',
      port: 587,
      fromEmail: ''
    })

    const fetchSettings = async () => {
      try {
        const response = await fetch('/api/admin/settings')
        const data = await response.json()
        settings.value = { ...settings.value, ...data.platform }
        emailSettings.value = { ...emailSettings.value, ...data.email }
      } catch (error) {
        console.error('Error fetching settings:', error)
      }
    }

    const savePlatformSettings = async () => {
      try {
        await fetch('/api/admin/settings/platform', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(settings.value)
        })
      } catch (error) {
        console.error('Error saving platform settings:', error)
      }
    }

    const saveEmailSettings = async () => {
      try {
        await fetch('/api/admin/settings/email', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(emailSettings.value)
        })
      } catch (error) {
        console.error('Error saving email settings:', error)
      }
    }

    onMounted(fetchSettings)

    return {
      settings,
      emailSettings,
      savePlatformSettings,
      saveEmailSettings
    }
  }
}
</script>
