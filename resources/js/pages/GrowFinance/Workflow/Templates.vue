<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { useToast } from '@/composables/useToast';

const page = usePage();
const { toast } = useToast();

interface WorkflowStep {
    step_order: number;
    role: string;
    approver_id: number | null;
    action: string;
}

interface WorkflowTemplate {
    id: number;
    business_id: number;
    name: string;
    description: string | null;
    entity_type: string;
    steps: string | WorkflowStep[];
    is_active: boolean;
    created_at: string;
}

const props = defineProps<{
    templates: WorkflowTemplate[];
}>();

const showCreateForm = ref(false);
const form = ref({
    name: '',
    description: '',
    entity_type: 'journal',
    steps: [{ step_order: 0, role: '', approver_id: null as number | null, action: 'approve' }] as WorkflowStep[],
});

const parseSteps = (template: WorkflowTemplate): WorkflowStep[] => {
    if (typeof template.steps === 'string') {
        try { return JSON.parse(template.steps); } catch { return []; }
    }
    return template.steps as WorkflowStep[];
};

const addStep = () => {
    form.value.steps.push({
        step_order: form.value.steps.length,
        role: '',
        approver_id: null,
        action: 'approve',
    });
};

const removeStep = (index: number) => {
    if (form.value.steps.length <= 1) return;
    form.value.steps.splice(index, 1);
    form.value.steps.forEach((s, i) => s.step_order = i);
};

const submitForm = () => {
    router.post(route('growfinance.workflow.templates.store'), {
        name: form.value.name,
        description: form.value.description || null,
        entity_type: form.value.entity_type,
        steps: form.value.steps.map((s, i) => ({ ...s, step_order: i })),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Template created.');
            showCreateForm.value = false;
            form.value = { name: '', description: '', entity_type: 'journal', steps: [{ step_order: 0, role: '', approver_id: null, action: 'approve' }] };
        },
        onError: () => { toast.error('Failed to create template.'); },
    });
};

const entityTypeLabel = (type: string) => {
    const labels: Record<string, string> = { journal: 'Journal Entry', invoice: 'Invoice', expense: 'Expense' };
    return labels[type] || type;
};

const formatDate = (date: string) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-ZM', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <GrowFinanceLayout>
        <div class="px-6 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Workflow Templates</h1>
                    <p class="text-sm text-gray-500 mt-1">Define approval workflows for journals, invoices, and expenses</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('growfinance.workflow.index')" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Back to Approvals
                    </Link>
                    <button @click="showCreateForm = !showCreateForm" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        New Template
                    </button>
                </div>
            </div>

            <!-- Create Form -->
            <div v-if="showCreateForm" class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Create Workflow Template</h2>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input v-model="form.name" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="e.g., Journal Approval" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Entity Type</label>
                            <select v-model="form.entity_type" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="journal">Journal Entry</option>
                                <option value="invoice">Invoice</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Optional description"></textarea>
                    </div>

                    <!-- Steps -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">Approval Steps</label>
                            <button type="button" @click="addStep" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">+ Add Step</button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(step, index) in form.steps" :key="index" class="flex items-center gap-3 bg-gray-50 p-3 rounded-lg">
                                <span class="text-xs font-medium text-gray-400 w-6">#{{ index + 1 }}</span>
                                <input v-model="step.role" placeholder="Role (e.g., manager, director)" class="flex-1 px-2 py-1.5 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                                <input v-model.number="step.approver_id" type="number" placeholder="User ID (optional)" class="w-32 px-2 py-1.5 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                                <button v-if="form.steps.length > 1" type="button" @click="removeStep(index)" class="text-red-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showCreateForm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Save Template</button>
                    </div>
                </form>
            </div>

            <!-- Templates List -->
            <div class="space-y-3">
                <div v-if="props.templates.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    <h3 class="mt-3 text-sm font-medium text-gray-900">No templates yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Create your first approval workflow template.</p>
                </div>

                <div v-for="template in props.templates" :key="template.id" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ template.name }}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="template.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                                    {{ template.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <p v-if="template.description" class="text-sm text-gray-500 mb-2">{{ template.description }}</p>
                            <div class="flex items-center gap-4 text-sm text-gray-400">
                                <span>{{ entityTypeLabel(template.entity_type) }}</span>
                                <span>{{ parseSteps(template).length }} step{{ parseSteps(template).length !== 1 ? 's' : '' }}</span>
                                <span>Created {{ formatDate(template.created_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Steps visualization -->
                    <div class="mt-3 flex items-center gap-2">
                        <div v-for="(step, i) in parseSteps(template)" :key="i" class="flex items-center gap-2">
                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 rounded-lg text-xs font-medium text-gray-600 border border-gray-100">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">{{ i + 1 }}</span>
                                <span>{{ step.role || 'any' }}</span>
                            </div>
                            <svg v-if="i < parseSteps(template).length - 1" class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
