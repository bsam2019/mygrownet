<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Gateway {
    identifier: string;
    name: string;
    countries: string[];
    currencies: string[];
    supportsCollections: boolean;
    supportsDisbursements: boolean;
}

interface PaymentResponse {
    success: boolean;
    transaction_id: string;
    provider_reference: string;
    status: string;
    message: string;
}

const gateways = ref<Record<string, Gateway>>({});
const defaultGateway = ref('');
const loading = ref(false);
const result = ref<PaymentResponse | null>(null);
const error = ref('');

// Check if selected gateway supports disbursements
const selectedGateway = computed(() => gateways.value[form.value.gateway]);
const canDisburse = computed(() => selectedGateway.value?.supportsDisbursements ?? false);

// Form data
const form = ref({
    phone_number: '0971234567',
    amount: 10,
    currency: 'ZMW',
    provider: 'mtn',
    reference: '',
    description: 'Test payment',
    gateway: '',
});

const statusCheck = ref({
    transactionId: '',
    type: 'collection',
    gateway: '',
});

const statusResult = ref<{ transaction_id: string; status: string } | null>(null);

async function fetchGateways() {
    try {
        const response = await fetch('/api/payments/gateways', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
        });
        const data = await response.json();
        gateways.value = data.gateways;
        defaultGateway.value = data.default;
        form.value.gateway = data.default;
    } catch (e) {
        error.value = 'Failed to fetch gateways';
    }
}

async function testCollection() {
    loading.value = true;
    error.value = '';
    result.value = null;

    try {
        const response = await fetch('/api/payments/collect', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            credentials: 'include',
            body: JSON.stringify({
                ...form.value,
                reference: form.value.reference || `TEST-${Date.now()}`,
            }),
        });
        result.value = await response.json();
        if (result.value?.transaction_id) {
            statusCheck.value.transactionId = result.value.transaction_id;
            statusCheck.value.gateway = form.value.gateway;
        }
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Collection failed';
    } finally {
        loading.value = false;
    }
}

async function testDisbursement() {
    loading.value = true;
    error.value = '';
    result.value = null;

    try {
        const response = await fetch('/api/payments/disburse', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            credentials: 'include',
            body: JSON.stringify({
                ...form.value,
                reference: form.value.reference || `PAYOUT-${Date.now()}`,
            }),
        });
        result.value = await response.json();
        if (result.value?.transaction_id) {
            statusCheck.value.transactionId = result.value.transaction_id;
            statusCheck.value.type = 'disbursement';
            statusCheck.value.gateway = form.value.gateway;
        }
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Disbursement failed';
    } finally {
        loading.value = false;
    }
}


async function checkStatus() {
    if (!statusCheck.value.transactionId) {
        error.value = 'Enter a transaction ID';
        return;
    }

    loading.value = true;
    error.value = '';
    statusResult.value = null;

    try {
        const params = new URLSearchParams({
            type: statusCheck.value.type,
            gateway: statusCheck.value.gateway || defaultGateway.value,
        });
        const response = await fetch(`/api/payments/status/${statusCheck.value.transactionId}?${params}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
        });
        statusResult.value = await response.json();
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Status check failed';
    } finally {
        loading.value = false;
    }
}

onMounted(fetchGateways);
</script>

<template>
    <Head title="Payment Gateway Test" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Payment Gateway Test</h1>

                <!-- Available Gateways -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">Available Gateways</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="(gateway, key) in gateways"
                            :key="key"
                            class="border rounded-lg p-4"
                            :class="key === defaultGateway ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium">{{ gateway.name }}</span>
                                <span v-if="key === defaultGateway" class="text-xs bg-blue-500 text-white px-2 py-1 rounded">Default</span>
                            </div>
                            <div class="text-sm text-gray-600">
                                <p>Countries: {{ gateway.countries.join(', ') }}</p>
                                <p>Currencies: {{ gateway.currencies.join(', ') }}</p>
                                <p class="mt-1">
                                    <span v-if="gateway.supportsCollections" class="text-green-600">✓ Collections</span>
                                    <span v-if="gateway.supportsDisbursements" class="ml-2 text-green-600">✓ Disbursements</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Form -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">Test Payment</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gateway</label>
                            <select v-model="form.gateway" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option v-for="(gateway, key) in gateways" :key="key" :value="key">
                                    {{ gateway.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input v-model="form.phone_number" type="text" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="0971234567" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                            <input v-model.number="form.amount" type="number" min="1" class="w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                            <select v-model="form.currency" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="ZMW">ZMW (Zambian Kwacha)</option>
                                <option value="USD">USD (US Dollar)</option>
                                <option value="KES">KES (Kenyan Shilling)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                            <select v-model="form.provider" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="mtn">MTN Mobile Money</option>
                                <option value="airtel">Airtel Money</option>
                                <option value="zamtel">Zamtel Kwacha</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input v-model="form.description" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <button
                            @click="testCollection"
                            :disabled="loading"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                        >
                            {{ loading ? 'Processing...' : 'Test Collection (Receive)' }}
                        </button>
                        <button
                            @click="testDisbursement"
                            :disabled="loading || !canDisburse"
                            :title="!canDisburse ? 'This gateway does not support disbursements. Use PawaPay instead.' : ''"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ loading ? 'Processing...' : 'Test Disbursement (Send)' }}
                        </button>
                        <span v-if="!canDisburse" class="text-sm text-amber-600 self-center">
                            ⚠️ {{ selectedGateway?.name || 'This gateway' }} doesn't support disbursements. Use PawaPay for payouts.
                        </span>
                    </div>
                </div>

                <!-- Result -->
                <div v-if="result" class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">Result</h2>
                    <div :class="result.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'" class="border rounded-lg p-4">
                        <p class="font-medium" :class="result.success ? 'text-green-800' : 'text-red-800'">
                            {{ result.success ? 'Success' : 'Failed' }}
                        </p>
                        <div class="mt-2 text-sm space-y-1">
                            <p><span class="font-medium">Transaction ID:</span> {{ result.transaction_id || 'N/A' }}</p>
                            <p><span class="font-medium">Provider Ref:</span> {{ result.provider_reference || 'N/A' }}</p>
                            <p><span class="font-medium">Status:</span> {{ result.status }}</p>
                            <p v-if="result.message"><span class="font-medium">Message:</span> {{ result.message }}</p>
                        </div>
                    </div>
                </div>

                <!-- Status Check -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Check Transaction Status</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction ID</label>
                            <input v-model="statusCheck.transactionId" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select v-model="statusCheck.type" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="collection">Collection</option>
                                <option value="disbursement">Disbursement</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gateway</label>
                            <select v-model="statusCheck.gateway" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option v-for="(gateway, key) in gateways" :key="key" :value="key">{{ gateway.name }}</option>
                            </select>
                        </div>
                    </div>
                    <button
                        @click="checkStatus"
                        :disabled="loading"
                        class="mt-4 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 disabled:opacity-50"
                    >
                        Check Status
                    </button>
                    <div v-if="statusResult" class="mt-4 bg-gray-50 border rounded-lg p-4">
                        <p><span class="font-medium">Transaction:</span> {{ statusResult.transaction_id }}</p>
                        <p><span class="font-medium">Status:</span> 
                            <span :class="{
                                'text-green-600': statusResult.status === 'completed',
                                'text-yellow-600': statusResult.status === 'pending' || statusResult.status === 'processing',
                                'text-red-600': statusResult.status === 'failed' || statusResult.status === 'cancelled'
                            }">{{ statusResult.status }}</span>
                        </p>
                    </div>
                </div>

                <!-- Error -->
                <div v-if="error" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4 text-red-800">
                    {{ error }}
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
