<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref } from 'vue';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import axios from 'axios';
import { PencilSquareIcon, TrashIcon, PlusIcon, CheckCircleIcon, LinkIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import { toast } from '@/utils/bizboost-toast';

interface Link {
    id: number;
    linkable_type: string;
    linkable_id: number;
    metric_field: string | null;
    auto_sync: boolean;
    label: string | null;
    linkable: Record<string, any> | null;
}

interface Objective {
    id: number;
    plan_id: number;
    title: string;
    description: string | null;
    type: string;
    target_value: number | null;
    current_value: number | null;
    unit: string | null;
    owner: string | null;
    target_date: string | null;
    completed_at: string | null;
    status: string;
    sort_order: number;
    links: Link[];
}

interface Plan {
    id: number;
    type: string;
    title: string;
    description: string | null;
    start_date: string | null;
    end_date: string | null;
    status: string;
    sort_order: number;
    metadata: Record<string, any> | null;
    parent: Plan | null;
    children: Plan[];
    objectives: Objective[];
    created_by: { name: string } | null;
    created_at: string;
}

const props = defineProps<{
    plan: Plan;
}>();

const showForm = ref(false);
const editing = ref<Objective | null>(null);
const form = ref({
    title: '',
    description: '',
    type: 'kpi',
    target_value: null as number | null,
    current_value: null as number | null,
    unit: '',
    owner: '',
    target_date: '',
    status: 'not_started',
    sort_order: 0,
});

function openAdd() {
    editing.value = null;
    form.value = { title: '', description: '', type: 'kpi', target_value: null, current_value: null, unit: '', owner: '', target_date: '', status: 'not_started', sort_order: 0 };
    showForm.value = true;
}

function openEdit(obj: Objective) {
    editing.value = obj;
    form.value = {
        title: obj.title,
        description: obj.description ?? '',
        type: obj.type,
        target_value: obj.target_value,
        current_value: obj.current_value,
        unit: obj.unit ?? '',
        owner: obj.owner ?? '',
        target_date: obj.target_date ?? '',
        status: obj.status,
        sort_order: obj.sort_order,
    };
    showForm.value = true;
}

function submit() {
    if (!form.value.title.trim()) {
        toast.warning('Validation', 'Title is required');
        return;
    }
    const payload = { ...form.value };
    if (editing.value) {
        router.put(route('cms.plans.objectives.update', { planId: props.plan.id, id: editing.value.id }), payload, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; toast.success('Updated', 'Objective updated'); },
            onError: () => toast.error('Failed', 'Could not update objective'),
        });
    } else {
        router.post(route('cms.plans.objectives.store', { planId: props.plan.id }), payload, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; toast.success('Added', 'Objective added'); },
            onError: () => toast.error('Failed', 'Could not add objective'),
        });
    }
}

function confirmDelete(obj: Objective) {
    if (confirm(`Delete objective "${obj.title}"?`)) {
        router.delete(route('cms.plans.objectives.delete', { planId: props.plan.id, id: obj.id }), {
            preserveScroll: true,
            onSuccess: () => toast.success('Deleted', 'Objective removed'),
            onError: () => toast.error('Failed', 'Could not delete objective'),
        });
    }
}

// Linking
const linkForm = ref<{ objectiveId: number | null; entityType: string; entityId: number | null; entityLabel: string; metricField: string; autoSync: boolean; label: string }>({
    objectiveId: null, entityType: 'budget', entityId: null, entityLabel: '', metricField: '', autoSync: false, label: '',
});
const showLinkForm = ref(false);
const searchResults = ref<{ id: number; label: string; type: string }[]>([]);
const searchQ = ref('');
const availableFields = ref<string[]>([]);
const searching = ref(false);

const entityTypes = [
    { value: 'budget', label: 'Budget', fieldLabel: 'e.g. total_budget, total_spent' },
    { value: 'job', label: 'Job', fieldLabel: 'e.g. actual_value, quoted_value' },
    { value: 'goal', label: 'Goal', fieldLabel: 'e.g. progress_percentage' },
    { value: 'customer', label: 'Customer', fieldLabel: 'e.g. outstanding_balance' },
    { value: 'invoice', label: 'Invoice', fieldLabel: 'e.g. total_amount, amount_due' },
    { value: 'task', label: 'Task', fieldLabel: 'e.g. estimated_hours, actual_hours' },
];

function openLinkForm(obj: Objective) {
    linkForm.value = { objectiveId: obj.id, entityType: 'budget', entityId: null, entityLabel: '', metricField: '', autoSync: false, label: '' };
    searchResults.value = [];
    searchQ.value = '';
    availableFields.value = [];
    showLinkForm.value = true;
    fetchFields('budget');
}

function fetchFields(type: string) {
    axios.get(route('cms.plans.entity-fields'), { params: { type } })
        .then((r: any) => { availableFields.value = r.data?.fields ?? []; })
        .catch(() => { availableFields.value = []; });
}

function onTypeChange() {
    linkForm.value.entityId = null;
    linkForm.value.entityLabel = '';
    linkForm.value.metricField = '';
    searchResults.value = [];
    searchQ.value = '';
    fetchFields(linkForm.value.entityType);
}

let searchTimer: ReturnType<typeof setTimeout>;
function doSearch() {
    clearTimeout(searchTimer);
    if (!searchQ.value.trim()) { searchResults.value = []; return; }
    searchTimer = setTimeout(() => {
        searching.value = true;
        axios.get(route('cms.plans.entity-search'), { params: { type: linkForm.value.entityType, q: searchQ.value } })
            .then((r: any) => { searchResults.value = r.data?.results ?? []; searching.value = false; })
            .catch(() => { searchResults.value = []; searching.value = false; });
    }, 300);
}

function selectEntity(item: { id: number; label: string }) {
    linkForm.value.entityId = item.id;
    linkForm.value.entityLabel = item.label;
    searchResults.value = [];
    searchQ.value = '';
}

function submitLink() {
    if (!linkForm.value.entityId) {
        toast.warning('Validation', 'Select an entity to link');
        return;
    }
    router.post(
        route('cms.plans.objectives.link', { planId: props.plan.id, id: linkForm.value.objectiveId }),
        {
            linkable_type: linkForm.value.entityType,
            linkable_id: linkForm.value.entityId,
            metric_field: linkForm.value.metricField || null,
            auto_sync: linkForm.value.autoSync,
            label: linkForm.value.label || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => { showLinkForm.value = false; toast.success('Linked', 'Entity linked to objective'); },
            onError: () => toast.error('Failed', 'Could not link entity'),
        }
    );
}

function confirmUnlink(objId: number, linkId: number) {
    if (confirm('Remove this link?')) {
        router.delete(route('cms.plans.objectives.unlink', { planId: props.plan.id, id: objId, linkId }), {
            preserveScroll: true,
            onSuccess: () => toast.success('Removed', 'Link removed'),
        });
    }
}

function syncLink(objId: number, linkId: number) {
    router.post(route('cms.plans.objectives.sync-link', { planId: props.plan.id, id: objId, linkId }), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Synced', 'Value synced from linked entity'),
    });
}

const typeLabels: Record<string, string> = {
    kpi: 'KPI',
    milestone: 'Milestone',
    key_result: 'Key Result',
};

const statusColors: Record<string, string> = {
    not_started: 'bg-gray-100 text-gray-500',
    on_track: 'bg-green-100 text-green-800',
    at_risk: 'bg-amber-100 text-amber-800',
    behind: 'bg-red-100 text-red-600',
    completed: 'bg-blue-100 text-blue-800',
};

const typeColors: Record<string, string> = {
    kpi: 'bg-purple-100 text-purple-800',
    milestone: 'bg-amber-100 text-amber-800',
    key_result: 'bg-blue-100 text-blue-800',
    strategic: 'bg-purple-100 text-purple-800',
    business: 'bg-blue-100 text-blue-800',
    operational: 'bg-amber-100 text-amber-800',
    schedule: 'bg-green-100 text-green-800',
};

const statusColorsPlan: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-600',
    active: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-600',
    archived: 'bg-red-100 text-red-600',
};

function progress(obj: Objective): number {
    if (!obj.target_value || obj.target_value === 0) return obj.status === 'completed' ? 100 : 0;
    return Math.min(100, Math.round(((obj.current_value ?? 0) / obj.target_value) * 100));
}

function progressColor(pct: number): string {
    if (pct >= 100) return 'bg-green-500';
    if (pct >= 60) return 'bg-blue-500';
    if (pct >= 30) return 'bg-amber-500';
    return 'bg-red-500';
}

function linkLabel(link: Link): string {
    const name = link.linkable?.name ?? link.linkable?.job_title ?? link.linkable?.goal_title ?? link.linkable?.invoice_number ?? link.linkable?.title ?? `#${link.linkable_id}`;
    const type = link.linkable_type.replace(/.*\\/, '').replace('Model', '');
    return link.label || `${type}: ${name}`;
}
</script>

<template>
    <Head :title="plan.title" />

    <CMSLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('cms.plans.index')" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Plans</Link>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('cms.plans.edit', plan.id)"
                        class="px-3 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 flex items-center gap-2"
                    >
                        <PencilSquareIcon class="h-4 w-4" />
                        Edit
                    </Link>
                </div>
            </div>

            <!-- Plan Details -->
            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${typeColors[plan.type]}`">
                                {{ plan.type === 'strategic' ? 'Strategic Plan' : plan.type === 'business' ? 'Business Plan' : plan.type === 'operational' ? 'Operational Plan' : 'Work Schedule' }}
                            </span>
                            <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${statusColorsPlan[plan.status]}`">
                                {{ plan.status }}
                            </span>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ plan.title }}</h1>
                    </div>
                </div>

                <div v-if="plan.description" class="text-sm text-gray-700 whitespace-pre-wrap">
                    {{ plan.description }}
                </div>

                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div v-if="plan.start_date">
                        <span class="text-gray-500">Start:</span>
                        <p class="font-medium text-gray-900">{{ plan.start_date }}</p>
                    </div>
                    <div v-if="plan.end_date">
                        <span class="text-gray-500">End:</span>
                        <p class="font-medium text-gray-900">{{ plan.end_date }}</p>
                    </div>
                    <div v-if="plan.parent">
                        <span class="text-gray-500">Parent:</span>
                        <Link :href="route('cms.plans.show', plan.parent.id)" class="font-medium text-blue-600 hover:text-blue-800">
                            {{ plan.parent.title }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Objectives Section -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Objectives &amp; KPIs
                        <span v-if="plan.objectives.length" class="ml-2 text-sm font-normal text-gray-500">({{ plan.objectives.length }})</span>
                    </h2>
                    <button
                        @click="openAdd"
                        class="px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 flex items-center gap-1.5"
                    >
                        <PlusIcon class="h-4 w-4" />
                        Add
                    </button>
                </div>

                <div v-if="plan.objectives.length === 0" class="p-6 text-center text-sm text-gray-500">
                    No objectives yet. Add KPIs, milestones, or key results to track this plan's performance.
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div v-for="obj in plan.objectives" :key="obj.id" class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${typeColors[obj.type] || 'bg-gray-100'}`">
                                        {{ typeLabels[obj.type] || obj.type }}
                                    </span>
                                    <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${statusColors[obj.status]}`">
                                        {{ obj.status.replace(/_/g, ' ') }}
                                    </span>
                                    <span v-if="obj.status === 'completed'" class="text-green-600">
                                        <CheckCircleIcon class="h-4 w-4" />
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-gray-900">{{ obj.title }}</p>
                                <p v-if="obj.description" class="text-xs text-gray-500 mt-0.5">{{ obj.description }}</p>

                                <!-- Value display -->
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                    <span v-if="obj.target_value">
                                        <span class="font-medium text-gray-700">{{ obj.current_value ?? 0 }}</span>
                                        <span v-if="obj.unit"> {{ obj.unit }}</span>
                                        / {{ obj.target_value }} {{ obj.unit }}
                                    </span>
                                    <span v-if="obj.owner">Owner: <span class="font-medium text-gray-700">{{ obj.owner }}</span></span>
                                    <span v-if="obj.target_date">Due: {{ obj.target_date }}</span>
                                </div>

                                <!-- Progress bar -->
                                <div v-if="obj.target_value && obj.target_value > 0" class="mt-2">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                            <div
                                                :style="{ width: progress(obj) + '%' }"
                                                :class="`h-full rounded-full transition-all duration-500 ${progressColor(progress(obj))}`"
                                            />
                                        </div>
                                        <span class="text-xs font-medium text-gray-600 w-10 text-right">{{ progress(obj) }}%</span>
                                    </div>
                                </div>

                                <!-- Linked entities -->
                                <div v-if="obj.links && obj.links.length" class="mt-3 flex flex-wrap gap-2">
                                    <div
                                        v-for="link in obj.links"
                                        :key="link.id"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 border border-blue-100 rounded-lg text-xs"
                                    >
                                        <LinkIcon class="h-3 w-3 text-blue-500" />
                                        <span class="text-blue-700">{{ linkLabel(link) }}</span>
                                        <span v-if="link.metric_field" class="text-blue-400">· {{ link.metric_field }}</span>
                                        <span v-if="link.auto_sync" class="text-green-500 text-[10px]">auto</span>
                                        <button
                                            @click="syncLink(obj.id, link.id)"
                                            class="p-0.5 text-blue-400 hover:text-blue-600"
                                            title="Sync value"
                                        >
                                            <ArrowPathIcon class="h-3 w-3" />
                                        </button>
                                        <button
                                            @click="confirmUnlink(obj.id, link.id)"
                                            class="p-0.5 text-red-400 hover:text-red-600"
                                            title="Remove link"
                                        >
                                            <TrashIcon class="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button
                                    @click="openLinkForm(obj)"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition"
                                    title="Link to entity"
                                >
                                    <LinkIcon class="h-4 w-4" />
                                </button>
                                <button @click="openEdit(obj)" class="p-1.5 text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition">
                                    <PencilSquareIcon class="h-4 w-4" />
                                </button>
                                <button @click="confirmDelete(obj)" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sub-plans -->
            <div v-if="plan.children && plan.children.length > 0" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Sub-plans ({{ plan.children.length }})</h2>
                <div class="space-y-2">
                    <div v-for="child in plan.children" :key="child.id" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                        <Link :href="route('cms.plans.show', child.id)" class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ child.title }}</p>
                        </Link>
                        <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${typeColors[child.type]}`">{{ child.type }}</span>
                        <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${statusColorsPlan[child.status]}`">{{ child.status }}</span>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Objective Modal -->
            <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showForm = false">
                <div class="bg-white rounded-xl p-6 max-w-lg w-full mx-4 shadow-xl max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ editing ? 'Edit' : 'Add' }} Objective</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select v-model="form.type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="kpi">KPI (numeric target)</option>
                                <option value="key_result">Key Result (completion-based)</option>
                                <option value="milestone">Milestone (date-based)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input v-model="form.title" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. Achieve K12M revenue" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="form.description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Value</label>
                                <input v-model.number="form.target_value" type="number" min="0" step="0.01" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Value</label>
                                <input v-model.number="form.current_value" type="number" min="0" step="0.01" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                <input v-model="form.unit" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="%, K, count" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
                                <input v-model="form.owner" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. John Doe" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Date</label>
                                <input v-model="form.target_date" type="date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select v-model="form.status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="not_started">Not Started</option>
                                    <option value="on_track">On Track</option>
                                    <option value="at_risk">At Risk</option>
                                    <option value="behind">Behind</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-3 justify-end pt-4 border-t border-gray-100">
                            <button type="button" @click="showForm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">{{ editing ? 'Update' : 'Add' }} Objective</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Link Entity Modal -->
            <div v-if="showLinkForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showLinkForm = false">
                <div class="bg-white rounded-xl p-6 max-w-lg w-full mx-4 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Link Entity to Objective</h3>
                    <form @submit.prevent="submitLink" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Entity Type</label>
                            <select v-model="linkForm.entityType" @change="onTypeChange" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option v-for="et in entityTypes" :key="et.value" :value="et.value">{{ et.label }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search {{ entityTypes.find(e => e.value === linkForm.entityType)?.label }}</label>
                            <input v-model="searchQ" @input="doSearch" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Type to search..." />
                            <div v-if="searching" class="text-xs text-gray-500 mt-1">Searching...</div>
                            <div v-if="searchResults.length" class="mt-2 border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-40 overflow-y-auto">
                                <button
                                    v-for="item in searchResults"
                                    :key="item.id"
                                    type="button"
                                    @click="selectEntity(item)"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-blue-50 transition"
                                >
                                    {{ item.label }}
                                </button>
                            </div>
                            <div v-if="linkForm.entityId" class="mt-2 px-3 py-2 bg-blue-50 border border-blue-100 rounded-lg text-sm text-blue-700 flex items-center gap-2">
                                <CheckCircleIcon class="h-4 w-4 text-blue-500" />
                                {{ linkForm.entityLabel }}
                                <button type="button" @click="linkForm.entityId = null; linkForm.entityLabel = ''" class="ml-auto text-red-400 hover:text-red-600 text-xs">Change</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metric Field <span class="text-gray-400 font-normal">(optional)</span></label>
                            <select v-model="linkForm.metricField" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">— None —</option>
                                <option v-for="f in availableFields" :key="f" :value="f">{{ f }}</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Syncs the linked entity's field value into the objective's current value.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Label <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input v-model="linkForm.label" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. Q1 Revenue Target" />
                        </div>

                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input v-model="linkForm.autoSync" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-gray-700">Auto-sync value on link</span>
                        </label>

                        <div class="flex gap-3 justify-end pt-4 border-t border-gray-100">
                            <button type="button" @click="showLinkForm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Link Entity</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>
