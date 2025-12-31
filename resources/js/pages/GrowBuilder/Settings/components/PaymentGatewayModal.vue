<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ existingConfig ? 'Edit' : 'Add' }} Payment Gateway
                    </h2>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600"
                        aria-label="Close modal"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <form @submit.prevent="submit" class="p-6 space-y-6">
                <!-- Gateway Selection -->
                <div v-if="!existingConfig">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Gateway
                    </label>
                    <select
                        v-model="form.gateway"
                        @change="loadGatewayFields"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Select a gateway...</option>
                        <option
                            v-for="gw in availableGateways"
                            :key="gw.value"
                            :value="gw.value"
                        >
                            {{ gw.label }}
                        </option>
                    </select>
                </div>

                <!-- Gateway Info -->
                <div v-if="selectedGatewayInfo" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-1">{{ selectedGatewayInfo.label }}</h3>
                    <p class="text-sm text-blue-700 mb-2">{{ selectedGatewayInfo.description }}</p>
                    <a
                        :href="selectedGatewayInfo.website"
                        target="_blank"
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                        Get API credentials →
                    </a>
                </div>

                <!-- Dynamic Fields -->
                <div v-if="fields.length > 0" class="space-y-4">
                    <div v-for="field in fields" :key="field.name">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ field.label }}
                            <span v-if="field.required" class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.credentials[field.name]"
                            :type="field.type"
                            :required="field.required"
                            :placeholder="field.description"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p v-if="field.description" class="mt-1 text-xs text-gray-600">
                            {{ field.description }}
                        </p>
                    </div>
                </div>

                <!-- Test Mode -->
                <div class="flex items-center gap-3">
                    <input
                        v-model="form.test_mode"
                        type="checkbox"
                        id="test_mode"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <label for="test_mode" class="text-sm text-gray-700">
                        Enable test mode (use sandbox/test API keys)
                    </label>
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        id="is_active"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <label for="is_active" class="text-sm text-gray-700">
                        Activate this gateway immediately
                    </label>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-800">{{ error }}</p>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="processing || !form.gateway"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ processing ? 'Saving...' : 'Save Configuration' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

interface Props {
    site: any;
    gateway?: string | null;
    existingConfig?: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'saved']);

const form = ref({
    gateway: props.gateway || props.existingConfig?.gateway || '',
    credentials: {} as Record<string, string>,
    test_mode: props.existingConfig?.test_mode || false,
    is_active: props.existingConfig?.is_active ?? true,
});

const fields = ref<any[]>([]);
const processing = ref(false);
const error = ref('');
const availableGateways = ref([
    { value: 'pawapay', label: 'PawaPay', description: 'Multi-provider mobile money aggregator for Africa', website: 'https://pawapay.io' },
    { value: 'flutterwave', label: 'Flutterwave', description: 'Accept payments via mobile money, cards, and bank transfers', website: 'https://flutterwave.com' },
    { value: 'dpo', label: 'DPO PayGate', description: 'Secure payment gateway supporting mobile money and cards', website: 'https://www.dpogroup.com' },
]);

const selectedGatewayInfo = computed(() => {
    return availableGateways.value.find((g) => g.value === form.value.gateway);
});

async function loadGatewayFields() {
    if (!form.value.gateway) return;

    try {
        const response = await fetch(`/growbuilder/api/payment/gateway-fields?gateway=${form.value.gateway}`);
        const data = await response.json();
        fields.value = data.fields || [];
    } catch (err) {
        console.error('Failed to load gateway fields:', err);
    }
}

async function submit() {
    processing.value = true;
    error.value = '';

    try {
        router.post(
            `/growbuilder/sites/${props.site.id}/payment/config`,
            form.value,
            {
                onSuccess: () => {
                    emit('saved');
                },
                onError: (errors) => {
                    error.value = Object.values(errors).flat().join(', ');
                },
                onFinish: () => {
                    processing.value = false;
                },
            }
        );
    } catch (err: any) {
        error.value = err.message || 'Failed to save configuration';
        processing.value = false;
    }
}

onMounted(() => {
    if (form.value.gateway) {
        loadGatewayFields();
    }
});
</script>
