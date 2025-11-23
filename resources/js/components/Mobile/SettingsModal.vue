<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-[100000] overflow-y-auto"
        @click.self="emit('close')"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
        
        <!-- Modal -->
        <div class="flex min-h-full items-end justify-center p-0">
          <div class="relative w-full bg-white rounded-t-3xl shadow-2xl transform transition-all max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-gray-700 to-gray-800 text-white px-6 py-4 rounded-t-3xl flex-shrink-0">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Settings</h3>
                <button
                  @click="emit('close')"
                  aria-label="Close settings modal"
                  class="p-2 hover:bg-white/20 rounded-full transition-colors"
                >
                  <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-4 overflow-y-auto flex-1">
              <!-- Push Notifications -->
              <div class="bg-white border border-gray-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3 flex-1">
                    <BellIcon class="h-5 w-5 text-gray-600 flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-gray-900">Push Notifications</h4>
                      <p class="text-xs text-gray-500 mt-0.5">Get notified about important updates</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 ml-3">
                    <input type="checkbox" v-model="settings.notifications" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
              </div>

              <!-- Email Notifications -->
              <div class="bg-white border border-gray-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3 flex-1">
                    <EnvelopeIcon class="h-5 w-5 text-gray-600 flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-gray-900">Email Notifications</h4>
                      <p class="text-xs text-gray-500 mt-0.5">Receive updates via email</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 ml-3">
                    <input type="checkbox" v-model="settings.emailNotifications" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
              </div>

              <!-- SMS Notifications -->
              <div class="bg-white border border-gray-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3 flex-1">
                    <DevicePhoneMobileIcon class="h-5 w-5 text-gray-600 flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-gray-900">SMS Notifications</h4>
                      <p class="text-xs text-gray-500 mt-0.5">Receive updates via SMS</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 ml-3">
                    <input type="checkbox" v-model="settings.smsNotifications" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
              </div>

              <!-- Desktop Dashboard -->
              <div class="bg-white border border-gray-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3 flex-1">
                    <ComputerDesktopIcon class="h-5 w-5 text-gray-600 flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-gray-900">Use Desktop Dashboard</h4>
                      <p class="text-xs text-gray-500 mt-0.5">Switch to full desktop view</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 ml-3">
                    <input type="checkbox" v-model="settings.useDesktopDashboard" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
              </div>

              <!-- Info -->
              <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-3">
                <InformationCircleIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-blue-800">
                  <p class="font-medium mb-1">Notification Preferences</p>
                  <p class="text-xs">Your notification settings will be saved and applied immediately.</p>
                </div>
              </div>

              <!-- Save Button -->
              <button
                @click="saveSettings"
                :disabled="saving"
                class="w-full bg-gradient-to-r from-gray-700 to-gray-800 text-white py-4 rounded-xl font-semibold hover:from-gray-800 hover:to-gray-900 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed active:scale-98"
              >
                <span v-if="saving">Saving...</span>
                <span v-else>Save Settings</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { XMarkIcon, BellIcon, EnvelopeIcon, DevicePhoneMobileIcon, InformationCircleIcon, ComputerDesktopIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

interface Props {
  show: boolean;
  user: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'success', 'error']);

const settings = ref({
  notifications: true,
  emailNotifications: true,
  smsNotifications: false,
  useDesktopDashboard: false,
});

const saving = ref(false);

// Load user settings when modal opens
watch(() => props.show, (newValue) => {
  if (newValue && props.user) {
    // Load from user preferences if available
    const prefs = props.user.notification_preferences;
    settings.value.notifications = prefs?.push_enabled ?? true;
    settings.value.emailNotifications = prefs?.email_enabled ?? true;
    settings.value.smsNotifications = prefs?.sms_enabled ?? false;
    settings.value.useDesktopDashboard = props.user.dashboard_preference === 'desktop';
  }
});

// Watch for desktop dashboard toggle
watch(() => settings.value.useDesktopDashboard, async (newValue) => {
  if (props.show) {
    try {
      const preference = newValue ? 'desktop' : 'mobile';
      await axios.post(route('mygrownet.api.user.dashboard-preference'), { preference });
      
      // Redirect to desktop dashboard if enabled
      if (newValue) {
        router.visit(route('dashboard'));
      }
    } catch (error) {
      console.error('Failed to update dashboard preference:', error);
      // Revert the toggle on error
      settings.value.useDesktopDashboard = !newValue;
    }
  }
});

const saveSettings = async () => {
  saving.value = true;
  
  try {
    await axios.post(route('profile.notification-settings'), {
      notifications: settings.value.notifications,
      email_notifications: settings.value.emailNotifications,
      sms_notifications: settings.value.smsNotifications,
    }, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      }
    });
    
    emit('success', 'Settings saved successfully!');
    emit('close');
  } catch (error: any) {
    console.error('Settings save error:', error);
    
    // Handle 302/303 redirects as success
    if (error.response?.status === 302 || error.response?.status === 303) {
      emit('success', 'Settings saved successfully!');
      emit('close');
    } else {
      emit('error', 'Failed to save settings. Please try again.');
    }
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: translateY(100%);
}
</style>
