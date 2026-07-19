<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { CheckCircleIcon, XCircleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

interface Props {
    settings: {
        enabled: boolean;
        provider: string | null;
        api_key: string | null;
        username: string | null;
        sender_id: string | null;
        account_sid: string | null;
        auth_token: string | null;
        from_number: string | null;
    };
    statistics: {
        total: number;
        sent: number;
        failed: number;
        success_rate: number;
        total_cost: number;
    };
    isEnabled: boolean;
}

const props = defineProps<Props>();

const form = ref({ ...props.settings });
const isSaving = ref(false);
const testNumber = ref('');
const isTesting = ref(false);

const saveSettings = async () => {
    isSaving.value = true;

    try {
        const response = await fetch(route('cms.settings.sms.update'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form.value),
        });

        const data = await response.json();

        if (data.success) {
            alert('SMS settings saved successfully!');
            router.reload();
        } else {
            alert(data.error || 'Failed to save settings');
        }
    } catch (error) {
        console.error('Error saving settings:', error);
        alert('An error occurred. Please try again.');
    } finally {
        isSaving.value = false;
    }
};

const testConnection = async () => {
    if (!testNumber.value) {
        alert('Please enter a test phone number');
        return;
    }

    isTesting.value = true;

    try {
        const response = await fetch(route('cms.settings.sms.test-connection'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ test_number: testNumber.value }),
        });

        const data = await response.json();

        if (data.success) {
            alert('Test SMS sent successfully! Check your phone.');
        } else {
            alert(data.error || 'Failed to send test SMS');
        }
    } catch (error) {
        console.error('Error testing connection:', error);
        alert('An error occurred. Please try again.');
    } finally {
        isTesting.value = false;
    }
};
</script>

<template>
    <Head title="SMS Settings - CMS" />

    <CMSLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">SMS Settings</h1>
                <p class="text-gray-600 mt-1">Configure SMS notifications for invoices, payments, and reminders</p>
            </div>

            <!-- Info Banner -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <InformationCircleIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-blue-900 mb-1">SMS Service (Optional)</h3>
                        <p class="text-sm text-blue-800">
                            SMS notifications are optional. You'll need to subscribe to Africa's Talking or Twilio to enable this feature.
                            Leave disabled if you don't have a subscription yet.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Statistics (if enabled) -->
            <div v-if="isEnabled" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="text-sm text-gray-600 mb-1">Total Sent</div>
                    <div class="text-2xl font-bold text-gray-900">{{ statistics.total }}</div>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="text-sm text-gray-600 mb-1">Successful</div>
                    <div class="text-2xl font-bold text-green-600">{{ statistics.sent }}</div>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="text-sm text-gray-600 mb-1">Failed</div>
                    <div class="text-2xl font-bold text-red-600">{{ statistics.failed }}</div>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="text-sm text-gray-600 mb-1">Total Cost</div>
                    <div class="text-2xl font-bold text-gray-900">K{{ statistics.total_cost.toFixed(2) }}</div>
                </div>
            </div>

            <!-- Settings Form -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="space-y-6">
                    <!-- Enable SMS -->
                    <div class="flex items-center justify-between pb-6 border-b border-gray-200">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Enable SMS Notifications</h3>
                            <p class="text-sm text-gray-600 mt-1">Turn on SMS notifications for your customers</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.enabled" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <div v-if="form.enabled" class="space-y-6">
                        <!-- Provider Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">SMS Provider</label>
                            <select v-model="form.provider" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select Provider</option>
                                <option value="africas_talking">Africa's Talking</option>
                                <option value="twilio">Twilio</option>
                            </select>
                        </div>

                        <!-- Africa's Talking Settings -->
                        <div v-if="form.provider === 'africas_talking'" class="space-y-4 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-semibold text-gray-900">Africa's Talking Configuration</h4>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                                <input v-model="form.api_key" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Your API Key" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                <input v-model="form.username" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="sandbox or your username" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sender ID (Optional)</label>
                                <input v-model="form.sender_id" type="text" maxlength="11" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="MyCompany" />
                                <p class="text-xs text-gray-500 mt-1">Max 11 characters. Leave empty to use default.</p>
                            </div>
                        </div>

                        <!-- Twilio Settings -->
                        <div v-if="form.provider === 'twilio'" class="space-y-4 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-semibold text-gray-900">Twilio Configuration</h4>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Account SID</label>
                                <input v-model="form.account_sid" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Your Account SID" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Auth Token</label>
                                <input v-model="form.auth_token" type="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Your Auth Token" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">From Number</label>
                                <input v-model="form.from_number" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="+1234567890" />
                            </div>
                        </div>

                        <!-- Test Connection -->
                        <div v-if="form.provider" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="font-semibold text-gray-900 mb-3">Test SMS Connection</h4>
                            <div class="flex gap-3">
                                <input v-model="testNumber" type="tel" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="+260XXXXXXXXX" />
                                <button @click="testConnection" :disabled="isTesting" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    {{ isTesting ? 'Sending...' : 'Send Test SMS' }}
                                </button>
                            </div>
                            <p class="text-xs text-gray-600 mt-2">Enter a phone number to test your SMS configuration</p>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end pt-6 border-t border-gray-200">
                        <button @click="saveSettings" :disabled="isSaving" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            {{ isSaving ? 'Saving...' : 'Save Settings' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- View Logs Link -->
            <div v-if="isEnabled" class="mt-6 text-center">
                <a :href="route('cms.settings.sms.logs')" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    View SMS Logs →
                </a>
            </div>
        </div>
    </CMSLayout>
</template>
