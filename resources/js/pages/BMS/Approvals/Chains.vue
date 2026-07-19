<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import BMSLayout from '@/Layouts/BMSLayout.vue';
import {
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';
import { toast } from '@/utils/bizboost-toast';

interface ApprovalStep {
    level: number;
    role: string;
}

interface Chain {
    id: number;
    name: string;
    entity_type: string;
    min_amount: string;
    max_amount: string | null;
    approval_steps: ApprovalStep[];
    is_active: boolean;
    priority: number;
}

interface Props {
    chains: Chain[];
}

const props = defineProps<Props>();

const showForm = ref(false);
const editingChain = ref<Chain | null>(null);
const deletingChain = ref<Chain | null>(null);
const form = ref({
    name: '',
    entity_type: 'expense',
    min_amount: 0,
    max_amount: null as number | null,
    approval_steps: [{ level: 1, role: 'manager' }] as ApprovalStep[],
    priority: 0,
    is_active: true,
});

const addStep = () => {
    const nextLevel = form.value.approval_steps.length + 1;
    form.value.approval_steps.push({ level: nextLevel, role: 'manager' });
};

const removeStep = (index: number) => {
    if (form.value.approval_steps.length > 1) {
        form.value.approval_steps.splice(index, 1);
    }
};

const openCreate = () => {
    editingChain.value = null;
    form.value = {
        name: '',
        entity_type: 'expense',
        min_amount: 0,
        max_amount: null,
        approval_steps: [{ level: 1, role: 'manager' }],
        priority: 0,
        is_active: true,
    };
    showForm.value = true;
};

const openEdit = (chain: Chain) => {
    editingChain.value = chain;
    form.value = {
        name: chain.name,
        entity_type: chain.entity_type,
        min_amount: Number(chain.min_amount),
        max_amount: chain.max_amount ? Number(chain.max_amount) : null,
        approval_steps: chain.approval_steps.map((s, i) => ({ ...s, level: i + 1 })),
        priority: chain.priority,
        is_active: chain.is_active,
    };
    showForm.value = true;
};

const submit = () => {
    if (!form.value.name.trim()) {
        toast.warning('Validation', 'Chain name is required');
        return;
    }
    if (editingChain.value) {
        router.put(route('bms.approvals.chains.update', editingChain.value.id), form.value, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Updated', 'Approval chain updated');
                showForm.value = false;
            },
            onError: () => toast.error('Failed', 'Could not update chain'),
        });
    } else {
        router.post(route('bms.approvals.chains.store'), form.value, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Created', 'Approval chain created');
                showForm.value = false;
            },
            onError: () => toast.error('Failed', 'Could not create chain'),
        });
    }
};

const confirmDelete = (chain: Chain) => {
    deletingChain.value = chain;
};

const executeDelete = () => {
    if (!deletingChain.value) return;
    const chain = deletingChain.value;
    deletingChain.value = null;
    router.delete(route('bms.approvals.chains.delete', chain.id), {
        preserveScroll: true,
        onSuccess: () => toast.success('Deleted', 'Approval chain deleted'),
        onError: () => toast.error('Failed', 'Could not delete chain'),
    });
};

const getTypeLabel = (type: string) => {
    return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<template>
    <Head title="Approval Chains" />

    <BMSLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Approval Chains</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Define multi-step approval workflows for different entity types
                    </p>
                </div>
                <button
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
                >
                    <PlusIcon class="h-5 w-5" />
                    New Chain
                </button>
            </div>

            <div v-if="chains.length === 0" class="bg-white shadow-sm rounded-lg p-12 text-center">
                <ShieldCheckIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No approval chains</h3>
                <p class="mt-1 text-sm text-gray-500">Create your first approval chain to start managing approvals.</p>
            </div>

            <div v-else class="space-y-4">
                <div v-for="chain in chains" :key="chain.id" class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ chain.name }}</h3>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full" :class="chain.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'">
                                {{ chain.is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                {{ getTypeLabel(chain.entity_type) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="openEdit(chain)" class="p-2 text-gray-400 hover:text-blue-600 transition">
                                <PencilSquareIcon class="h-5 w-5" />
                            </button>
                            <button @click="confirmDelete(chain)" class="p-2 text-gray-400 hover:text-red-600 transition">
                                <TrashIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-500">Min Amount</p>
                                <p class="text-sm font-medium text-gray-900">K{{ Number(chain.min_amount).toLocaleString() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Max Amount</p>
                                <p class="text-sm font-medium text-gray-900">{{ chain.max_amount ? 'K' + Number(chain.max_amount).toLocaleString() : 'No limit' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Priority</p>
                                <p class="text-sm font-medium text-gray-900">{{ chain.priority }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-2">Approval Steps ({{ chain.approval_steps.length }})</p>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="(step, i) in chain.approval_steps" :key="i" class="flex items-center gap-1 px-3 py-1.5 bg-gray-100 rounded-lg text-xs">
                                    <span class="font-medium text-gray-700">L{{ step.level }}:</span>
                                    <span class="text-gray-600">{{ step.role.charAt(0).toUpperCase() + step.role.slice(1) }}</span>
                                    <span v-if="i < chain.approval_steps.length - 1" class="text-gray-400 ml-1">&rarr;</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="deletingChain" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="deletingChain = null">
                <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Approval Chain</h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Are you sure you want to delete <span class="font-medium text-gray-900">"{{ deletingChain.name }}"</span>? This action cannot be undone.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button @click="deletingChain = null" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button @click="executeDelete" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showForm = false">
                <div class="bg-white rounded-xl p-6 max-w-lg w-full mx-4 shadow-xl max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ editingChain ? 'Edit' : 'New' }} Approval Chain
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input v-model="form.name" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. High-value Contracts" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Entity Type</label>
                            <select v-model="form.entity_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="expense">Expense</option>
                                <option value="quotation">Quotation</option>
                                <option value="payment">Payment</option>
                                <option value="contract">Contract</option>
                                <option value="purchase_order">Purchase Order</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Min Amount (K)</label>
                                <input v-model.number="form.min_amount" type="number" min="0" step="0.01" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Max Amount (K)</label>
                                <input v-model.number="form.max_amount" type="number" min="0" step="0.01" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="No limit" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <input v-model.number="form.priority" type="number" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p class="text-xs text-gray-400 mt-1">Higher priority chains are checked first</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Is Active</label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-600">Active</span>
                            </label>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Approval Steps</label>
                                <button @click="addStep" type="button" class="text-xs text-blue-600 hover:text-blue-800">+ Add Step</button>
                            </div>
                            <div v-for="(step, index) in form.approval_steps" :key="index" class="flex items-center gap-3 mb-2 p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-500 w-8">L{{ step.level }}</span>
                                <select v-model="step.role" class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="owner">Owner</option>
                                    <option value="manager">Manager</option>
                                    <option value="accountant">Accountant</option>
                                </select>
                                <button @click="removeStep(index)" v-if="form.approval_steps.length > 1" type="button" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 justify-end mt-6">
                        <button @click="showForm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button @click="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            {{ editingChain ? 'Update' : 'Create' }} Chain
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </BMSLayout>
</template>
