<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import { ref } from 'vue';
import { ArrowLeftIcon, CreditCardIcon, UserIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Props {
    employee: any;
    payment: {
        id: number;
        user: { id: number; name: string; email: string; phone: string } | null;
        type: string;
        amount: string;
        status: string;
        payment_method: string;
        reference: string;
        external_reference: string | null;
        payment_details: any;
        failure_reason: string | null;
        created_at: string;
        completed_at: string | null;
    };
}

const props = defineProps<Props>();
const showProcessModal = ref(false);

const form = useForm({ action: 'approve' as 'approve' | 'reject', notes: '' });

const processPayment = () => {
    form.post(route('employee.portal.delegated.payments.process', props.payment.id), {
        onSuccess: () => { showProcessModal.value = false; form.reset(); },
    });
};

const getStatusClass = (s: string) => ({
    'pending': 'bg-amber-100 text-amber-700',
    'processing': 'bg-blue-100 text-blue-700',
    'completed': 'bg-green-100 text-green-700',
    'failed': 'bg-red-100 text-red-700',
}[s] || 'bg-gray-100 text-gray-700');
</script>

<template>
    <Head :title="`Payment - ${payment.reference}`" />
    <EmployeePortalLayout>
        <template #header>Payment Details</template>
        
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('employee.portal.delegated.payments.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ payment.reference }}</h1>
                        <p class="text-gray-500 capitalize">{{ payment.type.replace('_', ' ') }}</p>
                    </div>
                </div>
                <button v-if="payment.status === 'pending'" @click="showProcessModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Process Payment
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Payment Details -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <CreditCardIcon class="h-5 w-5 text-gray-400" />Payment Information
                        </h2>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Amount</dt>
                                <dd class="text-2xl font-bold text-gray-900">K{{ Number(payment.amount).toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Status</dt>
                                <dd><span :class="['px-3 py-1 text-sm font-medium rounded-full capitalize', getStatusClass(payment.status)]">{{ payment.status }}</span></dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Payment Method</dt>
                                <dd class="font-medium text-gray-900 capitalize">{{ payment.payment_method?.replace('_', ' ') || 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Reference</dt>
                                <dd class="font-medium text-gray-900">{{ payment.reference }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Created</dt>
                                <dd class="font-medium text-gray-900">{{ new Date(payment.created_at).toLocaleString() }}</dd>
                            </div>
                            <div v-if="payment.completed_at">
                                <dt class="text-sm text-gray-500">Completed</dt>
                                <dd class="font-medium text-gray-900">{{ new Date(payment.completed_at).toLocaleString() }}</dd>
                            </div>
                        </dl>
                        <div v-if="payment.failure_reason" class="mt-4 p-3 bg-red-50 rounded-lg">
                            <p class="text-sm text-red-700"><strong>Failure Reason:</strong> {{ payment.failure_reason }}</p>
                        </div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <UserIcon class="h-5 w-5 text-gray-400" />User
                        </h3>
                        <div v-if="payment.user" class="space-y-3">
                            <p class="font-medium text-gray-900">{{ payment.user.name }}</p>
                            <p class="text-sm text-gray-500">{{ payment.user.email }}</p>
                            <p v-if="payment.user.phone" class="text-sm text-gray-500">{{ payment.user.phone }}</p>
                        </div>
                        <p v-else class="text-gray-500">No user information</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Process Modal -->
        <Teleport to="body">
            <div v-if="showProcessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showProcessModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Process Payment</h2>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <button @click="form.action = 'approve'" :class="['flex-1 p-3 rounded-lg border-2 flex items-center justify-center gap-2', form.action === 'approve' ? 'border-green-500 bg-green-50' : 'border-gray-200']">
                                <CheckIcon class="h-5 w-5 text-green-600" />Approve
                            </button>
                            <button @click="form.action = 'reject'" :class="['flex-1 p-3 rounded-lg border-2 flex items-center justify-center gap-2', form.action === 'reject' ? 'border-red-500 bg-red-50' : 'border-gray-200']">
                                <XMarkIcon class="h-5 w-5 text-red-600" />Reject
                            </button>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="form.notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Add notes..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showProcessModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="processPayment" :disabled="form.processing" :class="['px-4 py-2 rounded-lg text-white', form.action === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700']">
                            {{ form.action === 'approve' ? 'Approve' : 'Reject' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </EmployeePortalLayout>
</template>
