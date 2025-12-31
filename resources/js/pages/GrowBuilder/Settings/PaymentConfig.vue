<template>
    <AppLayout>
        <Head :title="`Payment Settings - ${site.name}`" />
        
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Back Link -->
                <Link
                    :href="route('growbuilder.sites.settings', site.id)"
                    class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Settings
                </Link>
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Payment Settings</h1>
                <p class="mt-2 text-gray-600">
                    Configure payment gateways to accept payments on your website
                </p>
            </div>

            <!-- Current Configuration -->
            <div v-if="config" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Active Gateway</h2>
                        <p class="text-sm text-gray-600">{{ getGatewayLabel(config.gateway) }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            :class="[
                                'px-3 py-1 rounded-full text-sm font-medium',
                                config.is_active
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-gray-100 text-gray-800',
                            ]"
                        >
                            {{ config.is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span
                            v-if="config.test_mode"
                            class="px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800"
                        >
                            Test Mode
                        </span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button
                        @click="showEditModal = true"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Edit Configuration
                    </button>
                    <button
                        @click="testConfiguration"
                        :disabled="testing"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-50"
                    >
                        {{ testing ? 'Testing...' : 'Test Connection' }}
                    </button>
                    <button
                        @click="confirmDelete"
                        class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
                    >
                        Remove
                    </button>
                </div>

                <!-- Webhook URL -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Webhook URL
                    </label>
                    <div class="flex gap-2">
                        <input
                            type="text"
                            :value="webhookUrl"
                            readonly
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm"
                        />
                        <button
                            @click="copyWebhookUrl"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                        >
                            {{ copied ? 'Copied!' : 'Copy' }}
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-gray-600">
                        Add this URL to your payment gateway's webhook settings to receive payment notifications
                    </p>
                </div>
            </div>

            <!-- No Configuration -->
            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                        />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Payment Gateway Configured</h3>
                <p class="text-gray-600 mb-6">
                    Set up a payment gateway to start accepting payments on your website
                </p>
                <button
                    @click="showAddModal = true"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    Add Payment Gateway
                </button>
            </div>

            <!-- Available Gateways -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Available Payment Gateways</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div
                        v-for="gateway in availableGateways"
                        :key="gateway.value"
                        class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition-colors"
                    >
                        <h3 class="font-semibold text-gray-900 mb-1">{{ gateway.label }}</h3>
                        <p class="text-sm text-gray-600 mb-3">{{ gateway.description }}</p>
                        <button
                            v-if="!config || config.gateway !== gateway.value"
                            @click="selectGateway(gateway.value)"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                            Configure →
                        </button>
                        <span v-else class="text-sm text-green-600 font-medium">✓ Active</span>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <TransactionHistory :site-id="site.id" class="mt-6" />
            </div>
        </div>

        <!-- Add/Edit Gateway Modal -->
        <PaymentGatewayModal
            v-if="showAddModal || showEditModal"
            :site="site"
            :gateway="selectedGateway"
            :existing-config="showEditModal ? config : null"
            @close="closeModal"
            @saved="handleSaved"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PaymentGatewayModal from './components/PaymentGatewayModal.vue';
import TransactionHistory from './components/TransactionHistory.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Props {
    site: any;
    config: any;
    availableGateways: Array<{
        value: string;
        label: string;
        description: string;
    }>;
}

const props = defineProps<Props>();

const showAddModal = ref(false);
const showEditModal = ref(false);
const selectedGateway = ref<string | null>(null);
const testing = ref(false);
const copied = ref(false);

const webhookUrl = computed(() => {
    return `${window.location.origin}/api/growbuilder/sites/${props.site.id}/payment/webhook`;
});

function getGatewayLabel(value: string): string {
    const gateway = props.availableGateways.find((g) => g.value === value);
    return gateway?.label || value;
}

function selectGateway(gateway: string) {
    selectedGateway.value = gateway;
    showAddModal.value = true;
}

function closeModal() {
    showAddModal.value = false;
    showEditModal.value = false;
    selectedGateway.value = null;
}

function handleSaved() {
    closeModal();
    router.reload();
}

async function testConfiguration() {
    testing.value = true;
    try {
        const response = await fetch(`/growbuilder/sites/${props.site.id}/payment/test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        const data = await response.json();

        if (data.success) {
            alert('✓ Configuration is valid and working!');
        } else {
            alert(`✗ Configuration test failed:\n${data.errors?.join('\n') || data.message}`);
        }
    } catch (error) {
        alert('✗ Failed to test configuration');
    } finally {
        testing.value = false;
    }
}

function copyWebhookUrl() {
    navigator.clipboard.writeText(webhookUrl.value);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
}

function confirmDelete() {
    if (confirm('Are you sure you want to remove this payment gateway configuration?')) {
        router.delete(`/growbuilder/sites/${props.site.id}/payment/config/${props.config.id}`);
    }
}
</script>
