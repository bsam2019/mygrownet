<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import { ref } from 'vue';
import { ArrowLeftIcon, BanknotesIcon, UserIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Props {
    employee: any;
    withdrawal: {
        id: number;
        user: { id: number; name: string; email: string; phone: string } | null;
        amount: string;
        fee: string;
        penalty_amount: string;
        net_amount: string;
        status: string;
        payment_method: string;
        bank_details: any;
        rejection_reason: string | null;
        admin_notes: string | null;
        created_at: string;
        requested_at: string;
        approved_at: string | null;
        processed_at: string | null;
    };
}

const props = defineProps<Props>();
const showProcessModal = ref(false);

const form = useForm({ action: 'approve' as 'approve' | 'reject', notes: '' });

const processWithdrawal = () => {
    form.post(route('employee.portal.delegated.withdrawals.process', props.withdrawal.id), {
        onSuccess: () => { showProcessModal.value = false; form.reset(); },
    });
};

const getStatusClass = (s: string) => ({
    'pending': 'bg-amber-100 text-amber-700',
    'approved': 'bg-blue-100 text-blue-700',
    'processed': 'bg-green-100 text-green-700',
    'rejected': 'bg-red-100 text-red-700',
}[s] || 'bg-gray-100 text-gray-700');
</script>

<template>
    <Head :title="`Withdrawal #${withdrawal.id}`" />
    <EmployeePortalLayout>
        <template #header>Withdrawal Details</template>
        
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('employee.portal.delegated.withdrawals.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Withdrawal #{{ withdrawal.id }}</h1>
                        <p class="text-gray-500">{{ withdrawal.user?.name }}</p>
                    </div>
                </div>
                <button v-if="withdrawal.status === 'pending'" @click="showProcessModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Process Withdrawal
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Withdrawal Details -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <BanknotesIcon class="h-5 w-5 text-gray-400" />Withdrawal Information
                        </h2>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Requested Amount</dt>
                                <dd class="text-2xl font-bold text-gray-900">K{{ Number(withdrawal.amount).toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Status</dt>
                                <dd><span :class="['px-3 py-1 text-sm font-medium rounded-full capitalize', getStatusClass(withdrawal.status)]">{{ withdrawal.status }}</span></dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Fee</dt>
                                <dd class="font-medium text-gray-900">K{{ Number(withdrawal.fee || 0).toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Penalty</dt>
                                <dd class="font-medium text-gray-900">K{{ Number(withdrawal.penalty_amount || 0).toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Net Amount</dt>
                                <dd class="text-xl font-bold text-green-600">K{{ Number(withdrawal.net_amount).toLocaleString() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Payment Method</dt>
                                <dd class="font-medium text-gray-900 capitalize">{{ withdrawal.payment_method?.replace('_', ' ') || 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Requested</dt>
                                <dd class="font-medium text-gray-900">{{ new Date(withdrawal.requested_at || withdrawal.created_at).toLocaleString() }}</dd>
                            </div>
                            <div v-if="withdrawal.approved_at">
                                <dt class="text-sm text-gray-500">Approved</dt>
                                <dd class="font-medium text-gray-900">{{ new Date(withdrawal.approved_at).toLocaleString() }}</dd>
                            </div>
                        </dl>
                        <div v-if="withdrawal.rejection_reason" class="mt-4 p-3 bg-red-50 rounded-lg">
                            <p class="text-sm text-red-700"><strong>Rejection Reason:</strong> {{ withdrawal.rejection_reason }}</p>
                        </div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <UserIcon class="h-5 w-5 text-gray-400" />User
                        </h3>
                        <div v-if="withdrawal.user" class="space-y-3">
                            <p class="font-medium text-gray-900">{{ withdrawal.user.name }}</p>
                            <p class="text-sm text-gray-500">{{ withdrawal.user.email }}</p>
                            <p v-if="withdrawal.user.phone" class="text-sm text-gray-500">{{ withdrawal.user.phone }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Process Modal -->
        <Teleport to="body">
            <div v-if="showProcessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showProcessModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Process Withdrawal</h2>
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
                            <textarea v-model="form.notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2" :placeholder="form.action === 'reject' ? 'Reason for rejection...' : 'Add notes...'"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showProcessModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="processWithdrawal" :disabled="form.processing" :class="['px-4 py-2 rounded-lg text-white', form.action === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700']">
                            {{ form.action === 'approve' ? 'Approve' : 'Reject' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </EmployeePortalLayout>
</template>
